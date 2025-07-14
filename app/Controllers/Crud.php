<?php namespace App\Controllers;

use AllowDynamicProperties;
use App\Controllers\AccountBase;
use App\Libraries\Table;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;

#[AllowDynamicProperties] class Crud extends AccountBase
{
    protected $get_model = '';
    protected $model_name = '';
    protected $model_class = null;
    protected $model_pk_field = null;

    protected $table = null;

    protected string $url_prefix = 'account/';

    protected $template_list = 'account/crud_list';
    protected $template_form = 'account/crud_form';

    public function __construct($init_model_name='') {
        $this->get_model = service('request')->getGet('model');
        if($init_model_name!=='') {
            $this->get_model = $init_model_name;
        }
        $reflect = new ReflectionClass($this);
        $this->app_controller = strtolower($reflect->getShortName());

        $this->permission_prefix = $this->app_controller;
    }

    /**
     * @throws RedirectException
     * @throws \ErrorException
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->template->set('model_name', $this->get_model);
        if($this->get_model!== null) {
            $this->model_name = $this->get_model;
            if ((!isset($this->model_class) || !isset($this->{$this->model_name . '_model'}))) {
                $model_path = '\App\Models\\' . ucfirst($this->model_name) . 'Model';
                if(class_exists($model_path)) {
                    $this->{$this->model_name . '_model'} = new $model_path();

                    $this->model_class = $this->{$this->model_name . '_model'};
                    $this->model_pk_field = $this->model_class->getPrimaryKeyField();
                } else {
                    throw new \ErrorException("Model not found");
                }
            }

            if(isset($_SESSION['year'])) {
                $this->model_class->setYear($_SESSION['year']);
                $this->template->set('year', $_SESSION['year']);
            }

            $this->template->set('url_prefix', $this->url_prefix);

            $this->template->set('meta_title', $this->model_name);
            $this->template->set('page_title', $this->model_class->getTitle());
            $this->template->set('css_class_scroll_main', 'overflow-scroll py-3');
        } else {
            exit('missing get param model');
        }

        if($this->request->getGet('plain') == '1') {
            $this->template->setHeader('templates/plain');
            $this->template->setFooter('templates/plain');
        }

        if($this->request->getGet('modal') == '1') {
            $this->template->setHeader('templates/empty');
            $this->template->setFooter('templates/empty');
        }

        $this->table = new Table();

        $this->template->set('filters', false);


        $router = service('router');
        $controllerName = $router->controllerName();
        $className = (new \ReflectionClass($controllerName))->getShortName();
        $this->template->set('controller_name', $className);


        $this->template->set('request', $this->request->getGet());
    }

    public function index()
    {
        if (!auth()->user()->can('my.'.$this->app_controller.'_view')) {
            dd(('my.'.$this->app_controller.'_view'));
            session()->setFlashdata('error', 'Keine Berechtigung');
            return redirect()->to('/');
        }

        $pager = service('pager');

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 100;
        $offset = $page * $perPage - $perPage;

        $filter_configuration = $this->getFilterConfiguration();

        $request_get_data = $this->request->getGet();
        if($request_get_data) {
            foreach($request_get_data as $key => $value) {
                if(isset($filter_configuration['fields'][$key])) {
                    $field_config = $filter_configuration['fields'][$key];
                    if (isset($field_config['where']) && $value !== "") {
                        $where_replaced = $field_config['where'];
                        $where_replaced = str_replace('%value%', $value, $where_replaced);
                        $where_replaced = str_replace('greatherequal', ">=", $where_replaced);
                        $where_replaced = str_replace('lowerthan', "<", $where_replaced);
                        $this->model_class->where($where_replaced);
                    } elseif (isset($field_config['like']) && $value) {
                        $this->model_class->groupStart();
                        foreach ($field_config['like'] as $field_config_like => $field_config_value) {
                            $value_like = $field_config['like'][$field_config_like];
                            $where_replaced = $value_like;
                            $where_replaced = str_replace('%value%', $value, $where_replaced);
                            $where_replaced = str_replace('greatherequal', ">=", $where_replaced);
                            $where_replaced = str_replace('lowerthan', "<", $where_replaced);
                            $this->model_class->orLike($field_config_like, $where_replaced);
                        }
                        $this->model_class->groupEnd();
                    } elseif (isset($field_config['in']) && $value) {
                        if($key == 'author_id') {
                            $this->model_class->groupStart();
                            if(in_array("privat", $value)) {
                                $this->model_class->where('author_id', auth()->user()->id);
                            }
                            $this->model_class->orWhereIn('organization_id', $value);
                            $this->model_class->groupEnd();
                            continue;
                        }
                        $this->model_class->groupStart();
                        foreach ($field_config['in'] as $field_config_in => $field_config_value) {
                            $this->model_class->whereIn($field_config_in, $value);
                        }
                        $this->model_class->groupEnd();
                    }
                }
            }
        }

        $total = $this->model_class->getTotalEntries();
        $entries = $this->model_class->getEntries($perPage, $offset);

        // echo $this->model_class->db->getLastQuery();

        $filter_html = '';
        if(isset($filter_configuration['fields'])) {
            foreach ($filter_configuration['fields'] as $key => $value) {
                if (isset($filter_configuration['fields'][$key])) {
                    $field_config = $filter_configuration['fields'][$key];
                    $filter_html .= '<li class="nav-item px-3">
                    ' . form_build_one_field($key, $field_config, $request_get_data, true) . '
                </li>';
                }
            }
        }

        $this->template->set('has_filter_configuration', count($filter_configuration));

        $this->template->set('form_filters', $filter_html);

        $this->template->set('page_title', $this->model_class->getTitle());

        $table = $this->getCrudTable($this->app_controller, $this->model_name, (array)$entries);
        $this->template->set('pager_table', $table);
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'bs5_prev_next');

        $this->template->set('pager_links', $pager_links);

        $this->template->load('account/default_list');
    }

    protected function getCrudTable($app_controller_name=null, $model_name=null, $entries=null, $template=null) {
        if(!is_null($model_name)) {
            $this->model_name = $model_name;
            $model_path = '\App\Models\\' . ucfirst($this->model_name) . 'Model';
            if(class_exists($model_path)) {
                $this->model_class = new $model_path();
            }
        }

        if(is_null($template)) {
            $template = array(
                'table_open' => '<table class="table datatable table-hover text-start dataTable" id="table-' . $this->model_name . '">',
                'heading_cell_start' => '<th class="text-nowrap">',
                'cell_start' => '<td class="text-nowrap1">',
            );
        }

        if(!is_null($app_controller_name)) {
            $this->app_controller = strtolower($app_controller_name);
            $this->template->set('app_controller', $this->app_controller);
        }

        $this->table->set_template($template);

        $table_heading = $this->getTableHeader();

        if(is_null($entries)) {
            $entries = $this->model_class->getEntries();
        }

        if(is_array($this->getSortableFields()) && count($this->getSortableFields())) {
            $current_sort_field = $this->request->getVar('sortfield') ?? $this->model_class->getPrimaryKeyField();
            $current_sort_order = $this->request->getVar('sortby') ?? 'desc';

            // Toggle Sortierreihenfolge
            $next_sort_order = ($current_sort_order == 'asc') ? 'desc' : 'asc';

            $headers = [];
            foreach ($this->getSortableFields() as $field => $label) {
                // Erstellen eines klickbaren Headers
                $sort_link = site_url($this->url_prefix . '/' . $this->app_controller . '?sortfield=' . $field . '&sortby=' . $next_sort_order);
                $headers[] = '<a href="' . $sort_link . '">' . $label . '</a>';
            }

            $this->table->set_heading(array_merge($headers, ['']));
        } else {
            $this->table->set_heading(array_merge($table_heading, ['']));
        }


        if(is_array($entries)) {
            foreach ($entries as $entity_entity) {
                $primary_key_field = $this->model_class->getPrimaryKeyField();
                $table_fields = $this->getTableFields($entity_entity);

                $actions = $this->getRowActions($entity_entity);

                $this->table->add_row_attr(['class' => $entity_entity->getRowClass()]);
                $this->table->add_row(
                    array_merge($table_fields,
                        [['class' => 'no-trim', 'data' => '<div class="btn-group float-end">' . $actions . '</div>']])
                );
            }
        }

        $this->table->set_footer('');

        return $this->table->generate();
    }

    protected function getTableHeader() {
        return $this->model_class->getTableHeader();
    }

    protected function getSortableFields() {
        return $this->model_class->getSortableFields();
    }

    protected function getTableFields($entity_entity) {
        return $this->model_class->getTableFields($entity_entity);
    }

    protected function getFilterConfiguration($entity=null, $request=null) {
        return $this->model_class->getFilterConfiguration($entity, $request);
    }

    public function form($id=0) {
        if (!auth()->user()->can('my.'.$this->permission_prefix.'_edit')) {
            return redirect()->to('/');
        }

        $posted_values = $this->request->getPost('data');

        if(is_array($posted_values) && $this->request->getPost('submitaction') == 'save') {

            return $this->formSubmitAction($id, $posted_values);

        } elseif(is_array($posted_values) && $this->request->getPost('submitaction') == 'close') {

            return redirect()->to($this->url_prefix.$this->app_controller . '/?model='.$this->model_name);

        } elseif(is_array($posted_values) && $this->request->getPost('submitaction') == 'delete') {

            return $this->delete($id);

        }

        $entity_entity = $this->model_class->getEntity();
        if($id>0) {
            $entity_entity = $this->model_class->getEntry($id);
        }

        if($this->request->getPost()) {
            foreach($this->request->getPost() as $key=>$val) {
                $entity_entity->$key = $val;
            }
        }

        $this->template->set('id', $id);

        $form_data = [];

        if($id>0) {
            $this->template->set('entity', $this->model_class->getEntry($id));
            $entity_entity = $this->model_class->getEntry($id);
            $dorm_data = [];
            if(!$entity_entity) {
                $this->setFlash('Eintrag nicht gefunden', 'danger');
                return redirect()->to($this->url_prefix.$this->app_controller);
            }

            $form_data = $entity_entity->getFields();



            /*if($this->seo_model->where(['module' => $this->model_name, 'module_uid' => $id, 'sid' => 1])->first() !== null) {
                $form_data['seo'] = (array)$this->seo_model->where(['module' => $this->model_name, 'module_uid' => $id, 'sid' => 1])->first()->getFields();
            }*/
        } else {
            $this->template->set('entity', $entity_entity);
            $form_data = $entity_entity->getFields();
        }

        $this->template->set('form_configuration', $this->model_class->getFormConfiguration($entity_entity, $this->request));
        $this->template->set('form_data', $form_data);

        if($this->request->getGet('view')) {
            return $this->template->return($this->request->getGet('view'));
        } else {
            return $this->template->return($this->template_form);
        }
    }

    protected function formSubmitAction($id=0, $posted_values=[], $return_id=false) {
        if (!auth()->user()->can('my.'.$this->permission_prefix.'_edit')) {
            return redirect()->to('/');
        }

        if($id<=0 && isset($posted_values[$this->model_pk_field]) && $posted_values[$this->model_pk_field]>0):
            $id = $posted_values[$this->model_pk_field];
        endif;

        $entity_entity = $this->model_class->find($id);
        if(is_null($entity_entity)) {
            $entity_entity = $this->model_class->getEntity();
        }

        $entity_before_changes = $this->model_class->find($id);


        /* handle bool fields */
        /*$db = db_connect();
        $fields = $db->getFieldData($this->model_class->table);
        foreach($entity_entity->getSchema() as $db_field_name=>$db_field_type) {
            if($db_field_type == \PDO::PARAM_BOOL) {
                $entity_entity->$db_field_name = 0;
            }
        }*/

        foreach($posted_values as $posted_field=>$posted_value) {
            $entity_entity->setAttribute($posted_field, $posted_value);
        }

        if($id>0) {
            if($entity_before_changes->getFields() !== $entity_entity->getFields()) {
                $this->model_class->save($entity_entity);
                $this->setFlash('Eintrag gespeichert.', 'success');
            } else {
                $this->setFlash('Keine Änderungen vorgenommen', 'info');
            }
        } else {
            $this->model_class->save($entity_entity);
            $id = $this->model_class->getInsertID();
            $this->setFlash('Neuer Eintrag erstellt.','success');
        }

        if($return_id) {
            return $id;
        }

        return redirect()->to($this->url_prefix.$this->app_controller . '/form/'.$id.'?model='.$this->model_name);
    }

    protected function getRowActions($entity_entity) {
        $primary_key_field = $this->model_class->getPrimaryKeyField();
        $actions = $this->model_class->getEntryActions($entity_entity)
            . anchor($this->url_prefix.$this->app_controller . '/form/' . $entity_entity->{$primary_key_field} . '?model=' . $this->model_name, '<i class="bi bi-pencil"></i>', 'class="btn btn-default action" title="Bearbeiten"')
            . anchor($this->url_prefix.$this->app_controller . '/copy/' . $entity_entity->{$primary_key_field} . '?model=' . $this->model_name, '<i class="bi bi-files"></i>', 'class="btn btn-default action" title="Kopieren"')
            . anchor($this->url_prefix.$this->app_controller . '/delete/' . $entity_entity->{$primary_key_field} . '?model=' . $this->model_name, '<i class="bi bi-trash"></i>', 'class="btn btn-default action del" title="Löschen"');

        return $actions;
    }

    public function copy($entity_id=0) {
        if (!auth()->user()->can('my.'.$this->permission_prefix.'_edit')) {
            return redirect()->to('/');
        }

        $entity_entity = $this->model_class->getEntry($entity_id);
        if(!is_object($entity_entity)):
            return redirect()->to($this->url_prefix.$this->app_controller . '?model=' . $this->model_name);
        endif;

        $new_id = $this->model_class->copy($entity_id);
        $this->setFlash('Eintrag kopiert.','success');

        return redirect()->to($this->url_prefix.$this->app_controller . '/form/' . $new_id . '?model=' . $this->model_name);
    }

    public function save($entity_id=0) {
        if (!auth()->user()->can('my.'.$this->permission_prefix.'_edit')) {
            return redirect()->to('/');
        }

        $posted_values = $this->request->getPost();
        if(is_array($posted_values) && $this->getMethod()==='post') {
            if(isset($posted_values['entity_id']) && $posted_values['entity_id']>0):
                $entity_id = $posted_values['entity_id'];
            endif;
        }

        $entity_entity = $this->model_class->getEntity();
        if($entity_id>0) {
            $entity_entity = $this->model_class->getEntry($entity_id);
        }
        if(!is_object($entity_entity)) {
            return false;
        }

        if(is_array($posted_values) && $this->getMethod()==='post') {
            foreach($posted_values as $posted_key=>$posted_value) {
                $entity_entity->$posted_key = $posted_value;
            }
            $entity_entity->user_id = session()->get('id');

            if($entity_entity->entity_price<=0):
                $entity_entity->entity_price = 0;
            endif;
            if($entity_entity->entity_price_shop<=0 && $entity_entity->entity_price>0):
                $entity_entity->entity_price_shop = $entity_entity->entity_price;
            endif;

            if(!isset($posted_values['is_shop'])):
                $entity_entity->is_shop = 0;
            endif;

            if(isset($posted_values['delete_image'])):
                if(file_exists(WRITEPATH.'uploads/shopimages/'.$entity_entity->entity_image_shop)):
                    unlink(WRITEPATH.'uploads/shopimages/'.$entity_entity->entity_image_shop);
                endif;
                $entity_entity->entity_image_shop = '';
            endif;

            if($imagefile = $this->getFile('entity_image_shop'))
            {
                if ($imagefile->isValid() && ! $imagefile->hasMoved()) {
                    $newName = $imagefile->getRandomName();
                    $imagefile->move(WRITEPATH.'uploads/shopimages/', $newName);
                    $entity_entity->entity_image_shop = $newName;
                }
            }

            if($entity_id>0) {
                $entity_entity->updated_at = date("Y-m-d H:i:s");
                $this->model_class->save($entity_entity);
                $this->setFlash('Eintrag gespeichert','success');
            } else {
                $entity_id = $this->model_class->save($entity_entity);
                $this->setFlash('Eintrag hinzugefügt','success');
            }

            return redirect()->to($this->url_prefix.$this->app_controller . '/form/'.$entity_id);
        }
    }

    public function delete($entity_id=0) {
        if (!auth()->user()->can('my.'.$this->permission_prefix.'_delete')) {
            return redirect()->to('/');
        }

        $entity_entity = $this->model_class->getEntry($entity_id);
        if(!is_object($entity_entity)):
            return redirect()->to($this->url_prefix.$this->app_controller . '?model=' . $this->model_name);
        endif;

        $entity_deletable = false;

        if(count($this->model_class->entitiesWithParent($entity_id))<=0):
            $entity_deletable = true;
        else:
            $this->setFlash('Eintrag kann nicht gelöscht werden. Bitte erste die Kinder Einträge löschen.','success');
        endif;

        if($entity_deletable):
            $this->model_class->where($this->model_class->getPrimaryKeyField(), $entity_entity->getPrimaryKeyValue($this->model_class->getPrimaryKeyField()))->delete();
            $this->setFlash('Eintrag gelöscht.','success');
        endif;

        return redirect()->to($this->url_prefix.$this->app_controller . '?model=' . $this->model_name);
    }

    public function json() {
        if (!auth()->user()->can('my.'.$this->permission_prefix.'_view')) {
            return redirect()->to('/');
        }

        $results_on_query = $this->request->getVar('results_on_query');
        $query = $this->request->getVar('q');

        $response = [];
        if(!$results_on_query) {
            $entity_entity = $this->model_class->getEntity();
            $results = $this->model_class->select('*')->orderBy($entity_entity->getTitleField(), 'ASC')->findAll();

        } elseif($query) {
            $entity_entity = $this->model_class->getEntity();
            $results = $this->model_class->select('*')->like($entity_entity->getTitleField(), $query)->orderBy($entity_entity->getTitleField(), 'ASC')->findAll(50);

        } else {
            $entity_entity = $this->model_class->getEntity();
            $result = new \stdClass();
            $result->{$this->model_class->getPrimaryKeyField()} = '';
            $result->{$entity_entity->title_field} = 'Keine Resultate';
            $results[] = $result;
        }

        foreach ($results as $result) {
            $response[$result->{$this->model_class->getPrimaryKeyField()}] = $result->{$entity_entity->titleField};
        }

        return $this->response->setJSON($response);
    }

}
