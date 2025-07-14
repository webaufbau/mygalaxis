<?php

namespace App\Controllers\Admin;

use App\Controllers\Crud;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Review extends Crud {

    protected string $url_prefix = 'admin/';

    public function __construct() {
        parent::__construct('Review');
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->template->set('text_add_new', '');
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
