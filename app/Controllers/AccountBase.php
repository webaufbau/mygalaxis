<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class AccountBase extends \App\Controllers\BaseController {
    protected $account_id = 0;

    protected $user_model = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->template->setHeader('templates/header_account');
        $this->template->setFooter('templates/footer_account');

        if(auth()->user()) {
            $this->account_id = auth()->user()->id;
        } else {
            echo '<meta http-equiv="refresh" content="0;url=/auth?url='.current_url().'" />';
            exit();
        }

        $this->user_model = new \App\Models\UserModel();

        if(auth()->user() && auth()->user()->active <= 0) {
            // User ausloggen
            auth()->logout();
            return redirect()->to('/');
        }

    }

    protected function upload_file($uploaded_name, $uploaded_folder, $filetypes, $random_name=true) {
        $file = $this->request->getFile($uploaded_name);
        if ($file && $file->getSize() <= 0) {
            return false;
        }

        $file_array = [];
        if ($file) {
            $file_array = objectToArray($file);
        }

        $rules = [
            $uploaded_name => [
                'label' => 'Datei',
                'rules' => [
                    'uploaded['.$uploaded_name.']',
                    'mime_in['.$uploaded_name.','.$filetypes.']',
                    //'max_size['.$uploaded_name.',10000]', // größere maximale Dateigröße
                ],
            ],
        ];

        if (! $this->validateData([], $rules)) {
            $this->template->set(['errors' => $this->validator->getErrors()]);
            return false;
        }

        $imageLib = new \App\Libraries\Image();

        if ($file && ! $file->hasMoved()) {
            $file_extension = $file->getClientExtension();
            $filename = url_title($file->getClientName(), '_', true) . '.' . $file_extension;
            if($random_name) {
                $filename = md5($file->getBasename()) . '.' . $file_extension;
            }

            $filepath = 'uploads/'.$uploaded_folder.'/' . $filename;

            if(!is_dir('uploads/'.$uploaded_folder)) {
                mkdir('uploads/' . $uploaded_folder, 0777, TRUE);
            }

            if (file_exists($filepath)) {
                $filepath = 'uploads/'.$uploaded_folder.'/' . url_title($file->getClientName(), '_', true) . '_' . md5(uniqid()) . '.' . $file_extension;
            }

            $file->move(WRITEPATH . 'uploads/'.$uploaded_folder.'/', $filename, true);

            // Prüfe, ob es sich um eine PDF-Datei handelt
            if ($file_extension === 'pdf') {

                if ($filepath) {
                    // Bildgröße ermitteln
                    list($width, $height) = getimagesize(WRITEPATH . $filepath);
                    $file_array['width'] = $width;
                    $file_array['height'] = $height;

                    $file_array['originalName'] = $filename;
                    $file_array['name'] = $filename;

                    return [
                        'filepath' => $filepath,
                        'fileinfo' => $file_array,
                    ];
                }
            } else {
                // Bildgröße ermitteln
                list($width, $height) = getimagesize(WRITEPATH . $filepath);
                $file_array['width'] = $width;
                $file_array['height'] = $height;

                // Maximale Breiten für die Spalten
                $max_widths = [380, 760, 1140];

                // Bestimme die Spaltenanzahl basierend auf der Bildbreite
                $columns = 1; // Standardmäßig 1 Spalte
                $max_width = $max_widths[0]; // Standardmäßig Spalte 1

                if ($height <= $width) {
                    // Überprüfen, ob die Bildbreite größer ist als die maximale Breite für die aktuelle Spaltenanzahl
                    for ($i = count($max_widths) - 1; $i >= 0; $i--) {
                        if ($width > $max_widths[$i]) {
                            $columns = $i + 1;
                            $max_width = $max_widths[$i];
                            break;
                        }
                    }
                }

                // Falls keine passende Breite gefunden wurde, wird die maximale Spaltenanzahl verwendet
                if ($columns > count($max_widths)) {
                    $columns = count($max_widths);
                    $max_width = $max_widths[$columns - 1];
                }

                // Berechne die neue Breite und Höhe des Bildes
                $new_width = min($width, $max_width);
                $new_height = ($height / $width) * $new_width;

                // Bildgröße anpassen und speichern
                $resized_output_path = 'uploads/'.$uploaded_folder.'/' . pathinfo($filepath, PATHINFO_FILENAME) . "_{$max_width}." . pathinfo($filepath, PATHINFO_EXTENSION);
                $imageLib->resize_image_and_save(WRITEPATH . $filepath, $max_width, WRITEPATH . $resized_output_path);

                $file_array["resized_path"] = $resized_output_path;
                $file_array['columns'] = $columns;
                $file_array['originalName'] = $filename;
                $file_array['name'] = $filename;
                $file_array['width'] = round($new_width);
                $file_array['height'] = round($new_height);

                return [
                    'filepath' => $filepath,
                    'fileinfo' => $file_array,
                ];


            }
        }

        $this->template->set(['errors' => 'The file has already been moved.']);
        return false;
    }

}
