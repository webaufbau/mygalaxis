<?php

namespace App\Controllers\Admin;

use App\Libraries\Table;
use App\Models\ReviewModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Review extends AdminBase {

    protected string $url_prefix = 'admin/';
    protected $model_class;
    protected string $model_name = 'review';
    protected $table;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->model_class = new ReviewModel();
        $this->table = new Table();
        $this->template->set('text_add_new', '');
        $this->template->set('model_name', $this->model_name);
    }

    public function index()
    {
        if (!auth()->user()->can('my.'.$this->app_controller.'_view')) {
            return redirect()->to('/');
        }

        $pager = service('pager');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 100;
        $offset = $page * $perPage - $perPage;

        $total = $this->model_class->countAllResults(false);
        $entries = $this->model_class->getEntries($perPage, $offset);

        // Table erstellen
        $template = [
            'table_open' => '<table class="table datatable table-hover text-start dataTable" id="table-review">',
            'heading_cell_start' => '<th class="text-nowrap">',
            'cell_start' => '<td class="text-nowrap1">',
        ];
        $this->table->set_template($template);
        $this->table->set_heading(array_merge($this->model_class->getTableHeader(), ['']));

        if (is_array($entries)) {
            foreach ($entries as $entity) {
                $primary_key_field = $this->model_class->getPrimaryKeyField();
                $table_fields = $this->model_class->getTableFields($entity);
                $actions = $this->getRowActions($entity);

                $this->table->add_row_attr(['class' => $entity->getRowClass()]);
                $this->table->add_row(
                    array_merge($table_fields, [['class' => 'no-trim', 'data' => '<div class="btn-group float-end">' . $actions . '</div>']])
                );
            }
        }

        $this->table->set_footer('');

        $this->template->set('page_title', $this->model_class->getTitle());
        $this->template->set('pager_table', $this->table->generate());
        $this->template->set('pager_links', $pager->makeLinks($page, $perPage, $total, 'bs5_prev_next'));
        $this->template->set('has_filter_configuration', 0);
        $this->template->set('form_filters', '');

        $this->template->load('account/default_list');
    }

    public function form($id = 0)
    {
        if (!auth()->user()->can('my.'.$this->app_controller.'_edit')) {
            return redirect()->to('/');
        }

        $posted_values = $this->request->getPost('data');

        if (is_array($posted_values) && $this->request->getPost('submitaction') == 'save') {
            $entity = $this->model_class->find($id);
            if (is_null($entity)) {
                $entity = $this->model_class->getEntity();
            }

            foreach ($posted_values as $field => $value) {
                $entity->setAttribute($field, $value);
            }

            $this->model_class->save($entity);
            if ($id > 0) {
                $this->setFlash('Eintrag gespeichert.', 'success');
            } else {
                $id = $this->model_class->getInsertID();
                $this->setFlash('Neuer Eintrag erstellt.', 'success');
            }

            return redirect()->to($this->url_prefix . $this->app_controller . '/form/' . $id . '?model=' . $this->model_name);
        }

        $entity = $id > 0 ? $this->model_class->getEntry($id) : $this->model_class->getEntity();

        if (!$entity && $id > 0) {
            $this->setFlash('Eintrag nicht gefunden', 'danger');
            return redirect()->to($this->url_prefix . $this->app_controller . '?model=' . $this->model_name);
        }

        $this->template->set('id', $id);
        $this->template->set('entity', $entity);
        $this->template->set('form_configuration', $this->model_class->getFormConfiguration($entity, $this->request));
        $this->template->set('form_data', $entity->getFields());
        $this->template->set('page_title', $this->model_class->getTitle());

        return $this->template->return('account/crud_form');
    }

    public function delete($id = 0)
    {
        if (!auth()->user()->can('my.'.$this->app_controller.'_delete')) {
            return redirect()->to('/');
        }

        $entity = $this->model_class->getEntry($id);
        if (!is_object($entity)) {
            return redirect()->to($this->url_prefix . $this->app_controller . '?model=' . $this->model_name);
        }

        $this->model_class->where($this->model_class->getPrimaryKeyField(), $entity->getPrimaryKeyValue($this->model_class->getPrimaryKeyField()))->delete();
        $this->setFlash('Eintrag gelöscht.', 'success');

        return redirect()->to($this->url_prefix . $this->app_controller . '?model=' . $this->model_name);
    }

    protected function getRowActions($entity_entity) {
        $primary_key_field = $this->model_class->getPrimaryKeyField();

        $actions = $this->model_class->getEntryActions($entity_entity);

        $review = $this->model_class->find($entity_entity->id);
        $currentUser = auth()->user();
        $userId = $currentUser->id;

        $canEdit = false;
        if(is_object($review)) {
            $canEdit = $currentUser->can('my.' . $this->app_controller . '_admin') ||
                $review->user_id == $userId;
        }

        if($canEdit) {
            //$actions .= anchor($this->url_prefix . $this->app_controller . '/approve/' . $entity_entity->{$primary_key_field} . '?model=' . $this->model_name, '<i class="bi bi-check-lg"></i>', 'class="btn btn-default action" title="Freischalten"');
            $actions .= anchor($this->url_prefix . $this->app_controller . '/form/' . $entity_entity->{$primary_key_field} . '?model=' . $this->model_name, '<i class="bi bi-pencil"></i>', 'class="btn btn-default action" title="Bearbeiten"');
        }
        if($currentUser->can('my.' . $this->app_controller . '_admin')) {
            $actions .= anchor($this->url_prefix . $this->app_controller . '/delete/' . $entity_entity->{$primary_key_field} . '?model=' . $this->model_name, '<i class="bi bi-trash"></i>', 'class="btn btn-default action del" title="Löschen"');
        }

        return $actions;
    }

    public function approve($id) {
        if (!auth()->user()->can('my.'.$this->app_controller.'_view')) {
            return redirect()->to('/');
        }

        $review = $this->model_class->find($id);

        if (!$review || (!auth()->user()->can('my.' . $this->app_controller . '_admin'))) {
            $this->setFlash('Keine Berechtigung', 'error');
            return redirect()->back();
        }

        $review->is_approved = 1;

        $this->model_class->update($id, $review);
        $this->setFlash('Kommentar freigegeben', 'success');
        return redirect()->back();
    }

    public function disapprove($id) {
        if (!auth()->user()->can('my.'.$this->app_controller.'_view')) {
            return redirect()->to('/');
        }

        $review = $this->model_class->find($id);

        if (!$review || (!auth()->user()->can('my.' . $this->app_controller . '_admin'))) {
            $this->setFlash('Keine Berechtigung', 'error');
            return redirect()->back();
        }

        $review->is_approved = 0;

        $this->model_class->update($id, $review);
        $this->setFlash('Kommentar deaktiviert', 'success');
        return redirect()->back();
    }

}
