<?php

namespace App\Controllers\Admin;

use App\Controllers\Crud;
use CodeIgniter\Files\File;

class Settings extends AdminBase {

    protected string $url_prefix = 'admin/';

    /**
     * @throws \Exception
     */
    public function index($model_name = null) {
        if (!auth()->user()->can('my.'.$this->app_controller.'_view')) {
            return redirect()->to('/');
        }

        $subscription_library = new \App\Libraries\Subscription();
        $settings_data = [];

        $model = new \App\Models\SettingsModel();
        $this->template->set('model_name', $model_name);
        $this->template->set('page_title', $model->getTitle());
        $this->template->set('settings', $model);

        $email_template_library = new \App\Libraries\Email();
        $email_template_options = $email_template_library->getEmailTemplateOptions();
        $this->template->set('email_template_options', $email_template_options);

        $this->template->set('subscription_type_options', $subscription_library->getSubscriptionTypeOptions(0));

        if (! $this->request->is('post')) {
            return $this->template->return('account/admin/settings_list');
        }

        $settings_data = $this->request->getPost();
        unset($settings_data['submitbutton']);

        if(isset($_FILES)) {
            foreach($_FILES as $field_name=>$data) {
                if($data['full_path'] !== '') {
                    $uploadedFile = $this->uploadFile($field_name);
                    if (isset($uploadedFile['uploaded_fileinfo'])) {
                        $settings_data[$field_name] = $uploadedFile['uploaded_fileinfo']->getFilename();
                    } else {
                        $this->template->set($uploadedFile);
                        return $this->template->return('account/admin/settings_list');
                    }
                }
            }
        }
        
        foreach($settings_data as $key=>$value) {
            $settings_entity = $model->where('key', $key)->first();
            if(!is_object($settings_entity) || $settings_entity->id <= 0) {
                $model->insert(['key' => $key, 'value' => trim($value)]);
            } elseif($settings_entity->value !== $value) {
                $model->update($settings_entity->id, ['value' => trim($value)]);
            }
        }

        log_action('Einstellungen gespeichert', json_encode($settings_data));

        $this->setFlash('Einstellungen gespeichert', 'success');

        return $this->template->return('account/admin/settings_list');
    }


    /**
     * @throws \ReflectionException
     */
    protected function uploadFile($field_name): array
    {
        $validationRule = [
            $field_name => [
                'label' => 'Image File',
                'rules' => [
                    'uploaded['.$field_name.']',
                    'is_image['.$field_name.']',
                    'mime_in['.$field_name.',image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                    'max_size['.$field_name.',1000]',
                    'max_dims['.$field_name.',2000,2000]',
                ],
            ],
        ];

        if (! $this->validate($validationRule)) {
            log_action('Fehler durch Einstellungen Bildupload', json_encode($this->validator->getErrors()));

            return ['errors' => $this->validator->getErrors()];
        }

        $img = $this->request->getFile($field_name);

        if (! $img->hasMoved()) {
            $filepath = WRITEPATH . 'uploads/' . $img->store('');
            log_action('Einstellungen Bildupload konnte nicht verschoben werden', json_encode($img->getPathname()));

            return ['uploaded_fileinfo' => new File($filepath)];
        }

        log_action('Einstellungen Bildupload erfolgreich', json_encode($img->getFilename()));

        return ['errors' => 'The file has already been moved.'];
    }

}
