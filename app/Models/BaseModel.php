<?php
namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class BaseModel extends Model {
    private array $form_configuration = [];

    protected $title = '';
    protected $table = '';
    protected $returnType = '';
    protected $primaryKey = ''; // Adjust as per your table
    protected bool $use_filter = false;

    protected bool $indexable = false;


    protected $afterInsert = ['replicateQuery'];
    protected $afterUpdate = ['replicateQuery'];
    protected $afterDelete = ['replicateQuery'];

    public function getTable() {
        return $this->table;
    }

    public function getPrimaryKeyField() {
        return $this->primaryKey;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getTableHeader()
    {
        //$fields = $this->db->getFieldNames($this->table);
        $fields = $this->allowedFields;

        $headers = [];
        foreach ($fields as $field) {
            $headers[] = $field;
        }

        return $headers;
    }

    public function getTableFields($entity) {
        //$fields = $this->db->getFieldNames($this->table);
        $fields = $this->allowedFields;

        $headers = [];
        foreach ($fields as $field) {
            $headers[] = $entity->{$field};
        }

        return $headers;
    }

    public function getSortableFields() {
        /*$fields = $this->allowedFields;

        $headers = [];
        foreach ($fields as $field) {
            $headers[$field] = $field;
        }

        return $headers;*/
        return null;
    }

    public function getEntryActions($entity) {
        return '';
    }

    public function getDefaultOrderField() {
        return $this->default_order_field;
    }

    public function getDefaultOrderDirection() {
        return $this->default_order_direction;
    }

    public function getDefaultOrderColumn() {
        return $this->default_order_column;
    }

    public function useFilter(): bool
    {
        return $this->use_filter;
    }

    public function getEntity() {
        $entity = $this->returnType;
        if($entity == 'array' || $entity == 'object') {
            return false;
        }
        return new $entity();
    }

    public function getEntry($uid) {
        return $this->asObject($this->returnType)
            ->where([$this->primaryKey => $uid])
            ->first();
    }


    public function getTotalEntries() {
        $userModel = new \App\Models\UserModel();
        $query_elements = $this;
        if(!auth()->user()->can('my.'.$this->table.'_admin')) {
            $query_elements = $query_elements->where('user_id', auth()->user()->id);
        }
        $count_all_results = $query_elements->countAllResults(false);

        return $count_all_results;
    }

    public function getEntries($limit=100, $offset=0)
    {
        $userModel = new \App\Models\UserModel();
        $query_elements = $this;
        if(!auth()->user()->can('my.'.$this->table.'_admin')) {
            $query_elements = $query_elements->where('user_id', auth()->user()->id);
        }
        $query_elements->orderBy($this->getPrimaryKeyField(), 'DESC');
        $query_elements = $query_elements->findAll($limit, $offset);

        return $query_elements;
    }

    public function entitiesWithParent($parent_id) {
        if(!in_array('pid', $this->allowedFields)) {
            return [];
        }
        return $this->where('pid', $parent_id)->findAll();
    }

    public function get_by_field($field_name, $field_value): array
    {
        return $this->asObject($this->returnType)
            ->where([$field_name => $field_value])
            ->findAll();
    }

    public function get_one_by_field($field_name, $field_value)
    {
        return $this->asObject($this->returnType)
            ->where([$field_name => $field_value])
            ->first();
    }

    public function get_by_fields($where_array): array
    {
        return $this->asObject($this->returnType)
            ->where($where_array)
            ->findAll();
    }

    public function get_id_by_field($field_name, $field_value) {
        return $this->asObject($this->returnType)
            ->where([$field_name => $field_value])
            ->first()->{$this->primaryKey};
    }

    public function get_table_fields() {
        return $this->getFieldNames($this->table);
    }

    public function unique_values($field_name = '') {
        if(in_array($field_name, $this->schema)) {
            $builder = $this->db->table($this->table);
            $builder->select($field_name);
            $builder->distinct();
            return $builder->get()->getResultArray();
        } else {
            return [];
        }
    }

    public function getFormConfiguration($entity=null, $request=null)
    {
        if(!$entity) {
            $entity = $this->getEntity();
        }
        if(!$request) {
            $request = service('request');
        }

        $this->form_configuration = [
            'tabs' => [
                'general' => 'Allgemein',
            ],
            'fields' => [
                'general' => [],
            ],
            'config' => [
                'first_tab' => 'general',
                'translation' => true,
            ]
        ];

        $fields = $entity->getFields();
        foreach ($fields as $field=>$value) {
            $this->form_configuration['fields']['general'][$field] = [
                'label' => $field,
                'name' => 'data['.$field.']',
                'id' => 'data_' . $field,
                'value' => $value,
                'type' => 'text',
            ];
        }

        return $this->form_configuration;

    }

    public function copy($id_to_copy) {
        $entity = $this->find($id_to_copy);
        $attributes = $entity->getAttributes();
        unset($attributes[$this->getPrimaryKeyField()]);
        if(isset($attributes['created_at'])) {
            unset($attributes['created_at']);
        }
        if(isset($attributes['updated_at'])) {
            unset($attributes['updated_at']);
        }
        if(isset($attributes['deleted_at'])) {
            unset($attributes['deleted_at']);
        }

        foreach($attributes as $attribute_field_name=>$attribute_field_value) {
            if(str_ends_with($attribute_field_name, '_title') || str_ends_with($attribute_field_name, '_name')) {
                $attributes[$attribute_field_name] = '[Kopie] ' . $attribute_field_value;
            }
        }

        return $this->insert($attributes);
    }

    public function getModelShortname(): string
    {
        return strtolower(preg_replace('#App\\\\Models\\\\([A-Za-z]*)Model#', '$1', get_class($this)));
    }

    public function getFilterConfiguration($entity = null, $request = null)
    {
        if (!$entity) {
            $entity = $this->getEntity();
        }

        // NEU: Prüfen, ob Entity-Objekt korrekt ist
        if (!is_object($entity) || !method_exists($entity, 'getFields')) {
            return [
                'fields' => [], // Leere Filterkonfiguration zurückgeben
            ];
        }

        if (!$request) {
            $request = service('request');
        }

        $filter_configuration = [];

        $fields = $entity->getFields();
        foreach ($fields as $field => $value) {
            $filter_configuration['fields'][$field] = [
                'name' => $field,
                'id' => 'data_' . $field,
                'value' => $value,
                'type' => 'text',
            ];
        }

        return $filter_configuration;
    }

    public function searchEntries($query) {
        $fields = $this->get_table_fields();
        foreach ($fields as $field=>$field_name) {
            $this->like($field_name, $query);
        }
        return $this;
    }

    protected function replicateQuery(array $data)
    {
        return $data;
    }

    public function generateLink($id)
    {
        return site_url("account/{$this->table}/form/{$id}");
    }

    public function generateContent($data)
    {
        $contentParts = [];

        foreach ($data as $key => $value) {
            // Überprüfe, ob der Wert ein Array ist
            if (is_array($value)) {
                // Füge die Array-Elemente als durch Leerzeichen getrennten String hinzu
                $contentParts[] = implode(' ', $value);
            } else {
                // Füge den Wert direkt hinzu, wenn es kein Array ist
                $contentParts[] = $value;
            }
        }

        // Kombiniere alle Teile zu einem einzigen String
        return implode(' ', $contentParts);
    }

}

