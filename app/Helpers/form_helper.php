<?php

function form_label_input($label_text, $field_name, $value_mixed=false) {
	$html = '<div class="form-group">';
	$html .= form_label($label_text, $field_name);
	$input_data = [];
	$input_data['name'] = $field_name;
	if(is_object($value_mixed)):
		$input_data['value'] = $value_mixed->$field_name;
	else:
		$input_data['value'] = $value_mixed;
	endif;
	$html .= form_input($input_data);
	$html .= '</div>';
	return $html;
}

function form_label_dropdown($label_text, $field_name, $options=false, $value_mixed=false) {
	$html = '<div class="form-group">';
	$html .= form_label($label_text, $field_name);
	if(is_object($value_mixed)):
		$selected = $value_mixed->$field_name;
	else:
		$selected = $value_mixed;
	endif;
	$html .= form_dropdown(['name' => $field_name, 'options' => $options, 'selected' => $selected]);
	$html .= '</div>';
	return $html;
}

function form_magicsuggest($attributes) {
    $options = $attributes['options'];
    $magic_suggest_js = '';
    foreach($options as $key_option=>$option):
        $magic_suggest_js .= "{
            name: '".$option."'
        },";
    endforeach;
    $magic_suggest_js = rtrim($magic_suggest_js, ",");
    $form_input_attributes = $attributes;
    unset($form_input_attributes['options']);

    $html = form_input($form_input_attributes);
    $html .= "
    <script>
    $( document ).ready(function() {
        $('input[name=\"".$attributes['name']."\"]').magicSuggest({
            placeholder: 'Neue Einheit',
            allowFreeEntries: true,
            data: [".$magic_suggest_js."],
            selectionPosition: 'bottom',
            selectionStacked: true,
            selectionRenderer: function(data){
                return data.name;
            }
        });
    });
    </script>
    ";

    return $html;
}

function form_build_one_field($field_key, $field_configuration, $form_data=null, $return=false) {
    
    $return_html = '';

    if(isset($field_configuration['header'])) {
        $return_html .= '<h4 class="form-section-headline">'.$field_configuration['header'].'</h4>';
    }

    $data = [
        'name'      => $field_configuration['name'] ?? 'data['.$field_key.']',
        'id'        => $field_configuration['id'] ?? $field_key,
        'class' => 'form-control',
        'value' => '',
        'placeholder' => $field_configuration['placeholder'] ?? '',
        'onchange' => $field_configuration['onchange'] ?? '',
    ];

    $return_html .= '<div class="row form-row-'.$data['id'].'" id="form-row-'.$data['id'].'"><div class="form-group my-1" id="form-group-'.$data['id'].'">';

    if(isset($field_configuration['label']) && $field_configuration['label']!=="") {
        $return_html .= '<label class="form-label">';
        $return_html .= $field_configuration['label'];
        /*$return_html .= '<code>[';
        $return_html .= $field_key;
        $return_html .= ']</code>';*/

        if(isset($field_configuration['info'])) {
            $return_html .= '<span class="bi bi-info-circle mx-1 text-primary" data-bs-toggle="tooltip" title="'.$field_configuration['info'].'"></span>';
        }

        $return_html .= '</label>';
    }

    if(isset($field_configuration['value'])) {
        $data['value'] = $field_configuration['value'];
    }
    if(isset($field_configuration['required'])) {
        $data['required'] = $field_configuration['required'];
    }
    if(isset($field_configuration['onchange'])) {
        $data['onchange'] = $field_configuration['onchange'];
    }
    if(isset($field_configuration['onblur'])) {
        $data['onblur'] = $field_configuration['onblur'];
    }
    if(isset($field_configuration['maxlength'])) {
        $data['maxlength'] = $field_configuration['maxlength'];
    }
    if(isset($form_data[$field_key])) {
        $data['value'] = $form_data[$field_key];
    }
    if(isset($field_configuration['prefill'])) {
        $data['value'] = $field_configuration['prefill'];
    }
    if(isset($field_configuration['disabled'])) {
        $data['disabled'] = $field_configuration['disabled'];
    }

    if(isset($field_configuration['type'])) {
        switch($field_configuration['type']) {
            case 'label':
                $return_html .= form_label($data['value']);
                break;
            case 'message':
                $return_html .= '<div class="alert alert-info">';
                $return_html .= $field_configuration['message'];
                $return_html .= '</div>';
                break;
            case 'text':
                $return_html .= form_input($data);
                break;
            case 'color':
                $data['type'] = 'color';
                $return_html .= form_input($data);
                break;
            case 'search':
                $return_html .= '<div class="input-group">';
                $return_html .= '<span class="input-group-text"><i class="bi bi-search"></i></span>';
                $return_html .= form_input($data);
                $return_html .= '</div>';
                break;
            case 'slug':
                $return_html .= '
<div class="input-group mb-3">
  <span class="input-group-text">'.site_url($field_configuration['prefix']).'</span>
  '.form_input($data).'
</div>';
                break;
            case 'password':
                $return_html .= form_password($data);
                break;
            case 'number':
                $data['type'] = 'number';
                if(isset($field_configuration['step'])) {
                    $data['step'] = $field_configuration['step'];
                }
                if(isset($field_configuration['min'])) {
                    $data['min'] = $field_configuration['min'];
                }
                if(isset($field_configuration['max'])) {
                    $data['max'] = $field_configuration['max'];
                }
                $return_html .= form_input($data);
                break;
            case 'multinumber':
                $field_configuration['type'] = 'number';
                $selected_values = json_decode($data['value'],true);
                if(!is_array($selected_values)) {
                    $selected_values = json_decode((string)$selected_values,true);
                }
                $options = $field_configuration['options'];

                if(isset($options)) {
                    foreach ($options as $key => $label) {
                        $field_configuration['id'] = uniqid();
                        $field_configuration['name'] = $data['name'] . '['.$key.']';
                        $return_html .= '<div>';
                        if(!isset($selected_values[$key]) || $selected_values[$key]<0) {
                            $selected_values[$key] = 0;
                        }
                        $field_configuration['value'] = $selected_values[$key];
                        $field_configuration['class'] = 'form-control';
                        unset($field_configuration['options']);
                        $return_html .= form_label($label, $field_configuration['id'], ['class' => "form-label"]);
                        $return_html .= form_input($field_configuration);
                        $return_html .= '</div>';
                    }
                }
                break;
            case 'email':
                $data['type'] = 'email';
                $return_html .= form_input($data);
                break;
            case 'url':
                $data['type'] = 'url';
                if(isset($field_configuration['value'])) {
                    $data['value'] = $field_configuration['value'];
                }
                $return_html .= form_input($data);
                break;
            case 'tel':
                $data['type'] = 'tel';
                $return_html .= form_input($data);
                break;
            case 'date':
                $data['type'] = 'date';
                if(isset($field_configuration['min'])) {
                    $data['min'] = $field_configuration['min'];
                }
                if(isset($field_configuration['max'])) {
                    $data['max'] = $field_configuration['max'];
                }
                if(strtotime($data['value'])) {
                    $data['value'] = date("Y-m-d", strtotime($data['value']));
                }
                $return_html .= form_input($data);
                break;
            case 'datetime':
                $data['type'] = 'datetime-local';
                if(isset($field_configuration['min'])) {
                    $data['min'] = $field_configuration['min'];
                }
                if(isset($field_configuration['max'])) {
                    $data['max'] = $field_configuration['max'];
                }
                $return_html .= form_input($data);
                break;
            case 'date_time_range':

                $current_time_start = strtotime(date("H:i"));
                if(isset($field_configuration['value_time_start'])) {
                    //$return_html .= $field_configuration['value_time_start'];
                    $current_time_start = strtotime($field_configuration['value_time_start']);
                }

                $current_time_stop = '';
                if(isset($field_configuration['value_time_stop'])) {
                    //$return_html .= "-" . $field_configuration['value_time_stop'];
                    $current_time_stop = strtotime($field_configuration['value_time_stop']);

                    $originalTime = new \DateTime('31.08.2023 ' . $field_configuration['value_time_stop']);

                    // Aufrunden auf den nächsten 15-Minuten-Schritt
                    $minutes = $originalTime->format('i');
                    $remainder = $minutes % 15;
                    $minutesToNextStep = $remainder === 0 ? 0 : 15 - $remainder;
                    $nextTime = clone $originalTime;
                    $nextTime->modify("+$minutesToNextStep minutes");

                    $current_time_stop = $nextTime->getTimestamp();

                    // Ausgabe der nächsten Uhrzeit
                    //$return_html .= "Ursprüngliche Uhrzeit: " . $originalTime->format('d.m.Y H:i') . "\n";
                    //$return_html .= "Nächste Uhrzeit: " . $nextTime->format('d.m.Y H:i') . "\n";

                }






                $start_time = strtotime('07:00');
                if(isset($field_configuration['time_range_start'])) {
                    $start_time = strtotime($field_configuration['time_range_start']);
                }
                $end_time = strtotime('22:00');
                if(isset($field_configuration['time_range_stop'])) {
                    $end_time = strtotime($field_configuration['time_range_stop']);
                }

                $return_html .= '<div class="row">';
                $return_html .= '<div class="col-12 col-md-6">';
                $date_field = $data;
                $date_field['type'] = 'date';
                $return_html .= form_input($date_field);
                $return_html .= '</div>';

                //var_dump(date("H:i", $current_time_start));
                //var_dump(date("H:i", $current_time_stop));

                $selected_time = '';
                $return_html .= '<div class="col-3 ps-0">';
                $return_html .= '<select id="timeSelectStart" name="date_time_start" class="form-control form-select">';
                for ($time = $start_time; $time <= $end_time; $time += 15 * 60) {
                    $option_time = date('H:i', $time);
                    $return_html .= '<option value="' . $option_time . '"';

                    // Check if the current time is within the 15-minute range of this option
                    if ($current_time_start >= $time && $current_time_start < ($time + 15 * 60)) {
                        $selected_time = strtotime(date('H:i', ($time + 15 * 60)));
                        $return_html .= ' selected';
                    }

                    $return_html .= '>' . $option_time . '</option>';
                }
                $return_html .= '</select>';
                $return_html .= '</div>';

                $return_html .= '<div class="col-3 ps-0">';
                //$return_html .= date("d.m.Y H:i", $current_time_stop);

                $return_html .= '<select id="timeSelectStop" name="date_time_stop" class="form-control form-select">';
                for ($time = $start_time; $time <= $end_time; $time += 15 * 60) {
                    $option_time = date('H:i', $time);

                    $return_html .= '<option value="' . $option_time . '"';

                    // Check if the current time is within the 15-minute range of this option
                    if ($current_time_stop!=='' && date("H:i", $current_time_stop) == $option_time) {
                        $return_html .= ' selected';
                    } elseif ($current_time_stop=='' && $selected_time == $time) {
                        $return_html .= ' selected';
                    }

                    $return_html .= '>' . $option_time . '</option>';
                }
                $return_html .= '</select>';
                $return_html .= '</div>';

                $return_html .= '</div>';



                break;
            case 'summernote':
                $data['type'] = 'textarea';
                $data['id'] = 'summernote' . md5($data['name'] ?? uniqid());

                $toolbar = $field_configuration['toolbar'] ?? [
                    ['style', ['style']],
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['custom', ['variable']],
                    ['view', ['codeview']],
                    ['cleaner',['cleaner']],
                ];

                ob_start();
                ?>
                <textarea id="<?=$data['id'];?>" name="<?=$data['name'];?>"><?=$data['value'];?></textarea>




                <link href="<?=base_url('assets/summernote/css/summernote-lite.min.css?v='.mt_rand(0,999999));?>" rel="stylesheet">
                <script src="<?=base_url('assets/summernote/js/summernote-lite.min.js');?>"></script>
                <script src="<?=base_url('assets/summernote/js/summernote-cleaner.js');?>"></script>
                <script>
                $(document).ready(function() {
                    function cleanHTML(input) {
                        const allowedTags = ['B', 'STRONG', 'I', 'U', 'EM', 'UL', 'OL', 'LI', 'A', 'P'];
                        const div = document.createElement('div');
                        div.innerHTML = input;

                        function sanitize(node) {
                            const children = [...node.childNodes];
                            for (const child of children) {
                                if (child.nodeType === 1) { // ELEMENT_NODE
                                    if (!allowedTags.includes(child.tagName)) {
                                        // <span> z.B. ersetzen durch Inhalte
                                        child.replaceWith(...child.childNodes);
                                    } else {
                                        child.removeAttribute('style');
                                        child.removeAttribute('class');
                                        child.removeAttribute('lang');
                                        child.removeAttribute('width'); // für z.B. Word-Tabellen
                                        sanitize(child);
                                    }
                                }
                            }
                        }

                        sanitize(div);
                        return div.innerHTML;
                    }

                    var VariableButton = function (context) {
                        var text_placeholders = [
                            ['Link', '%link%'],
                            ['Aktivierungslink', '%activation-link%'],
                            ['Abo-Bestell-Nr', '%subscription-id%'],
                            ['Abo-Name', '%subscription-type-name%'],
                            ['Abo-Slug', '%subscription-type-slug%'],
                            ['Abo-Gruppe', '%subscription-type-group%'],
                            ['Abo-Sortierung', '%subscription-type-sort%'],
                            ['Abo-Dauer in Wochen', '%subscription-type-duration-weeks%'],
                            ['Abo-Beschreibung', '%subscription-type-description%'],
                            ['Abo-Preis', '%subscription-type-price%'],
                            ['Abo-Dauer in Tagen', '%subscription-type-duration-days%'],
                            ['Abo-Zahlungsart', '%subscription-payment-type%'],
                            ['Abo-Bezahldatum', '%subscription-payment-date%'],
                            ['Abo bezahlt?', '%subscription-payment-paid%'],
                            ['Abo-Start', '%subscription-start%'],
                            ['Abo-Ende', '%subscription-stop%'],
                            ['Abo-Kosten', '%subscription-price%'],
                            ['Print-Plus-ID', '%subscription-newspaper-number%'],
                            ['Benutzer ID', '%user-id%'],
                            ['Benutzer Vorname', '%user-firstname%'],
                            ['Benutzer Nachname', '%user-lastname%'],
                            ['Benutzer E-Mail', '%user-email%'],
                            ['Benutzer Strasse', '%user-street%'],
                            ['Benutzer Hausnummer', '%user-street-nr%'],
                            ['Benutzer Postleitzahl', '%user-postcode%'],
                            ['Benutzer Stadt', '%user-city%'],
                            ['Benutzer Land', '%user-country%'],
                            ['Benutzer Foto', '%user-photo%'],
                            ['Benutzer Telefon', '%user-phone%'],
                            ['Benutzer Geburtstag', '%user-birthday%'],
                            ['Kommentar', '%comment%'],
                            ['Organisation-Name', '%organization-name%'],
                        ];
                        var text_placeholders_contents = '<ul class="dropdown-menu show" style="background: #FFF;padding: 10px;width:250px;">';
                        for(var text_row in text_placeholders) {
                            text_placeholders_contents = text_placeholders_contents + '<li class="nav-item"><a style="cursor:pointer" href="javascript:;" data-value="'+text_placeholders[text_row][1]+'">'+text_placeholders[text_row][0]+'</a></li>';
                        }
                        text_placeholders_contents = text_placeholders_contents + '</ul>';

                        var ui = $.summernote.ui;
                        var button = ui.buttonGroup([
                            ui.button({
                                className: 'dropdown-toggle',
                                contents: 'Variablen <span class="note-icon-caret"></span>',
                                tooltip: 'Variable auswählen',
                                data: {
                                    toggle: 'dropdown'
                                }
                            }),
                            ui.dropdown({
                                className: 'dropdown-variable',
                                contents: text_placeholders_contents,
                                callback: function (items) {
                                    $(items).find('li a').each(function () {
                                        $(this).click(function(e) {
                                            context.invoke("editor.insertText", this.dataset.value);
                                            e.preventDefault();
                                        });
                                    });
                                }
                            })
                        ]);

                        return button.render();
                    };



                    var context = $('#<?=$data['id'];?>').summernote({
                        height: 200,
                        toolbar: <?=json_encode($toolbar);?>,
                        callbacks: {
                            onImageUpload: function(files) {
                                uploadImage(files[0]);
                            },
                            onPaste: function (e) {
                                e.preventDefault();
                                const clipboardData = (e.originalEvent || e).clipboardData;
                                const html = clipboardData.getData('text/html') || clipboardData.getData('text/plain');
                                const cleaned = cleanHTML(html);
                                document.execCommand('insertHTML', false, cleaned);
                            }
                        },
                        buttons: {
                            variable: VariableButton
                        },
                        cleaner: {
                            action: 'both', // both|button|paste 'button' only cleans via toolbar button, 'paste' only clean when pasting content, both does both options.
                            icon: '<i class="note-icon"><svg xmlns="http://www.w3.org/2000/svg" id="libre-paintbrush" viewBox="0 0 14 14" width="14" height="14"><path d="m 11.821425,1 q 0.46875,0 0.82031,0.311384 0.35157,0.311384 0.35157,0.780134 0,0.421875 -0.30134,1.01116 -2.22322,4.212054 -3.11384,5.035715 -0.64956,0.609375 -1.45982,0.609375 -0.84375,0 -1.44978,-0.61942 -0.60603,-0.61942 -0.60603,-1.469866 0,-0.857143 0.61608,-1.419643 l 4.27232,-3.877232 Q 11.345985,1 11.821425,1 z m -6.08705,6.924107 q 0.26116,0.508928 0.71317,0.870536 0.45201,0.361607 1.00781,0.508928 l 0.007,0.475447 q 0.0268,1.426339 -0.86719,2.32366 Q 5.700895,13 4.261155,13 q -0.82366,0 -1.45982,-0.311384 -0.63616,-0.311384 -1.0212,-0.853795 -0.38505,-0.54241 -0.57924,-1.225446 -0.1942,-0.683036 -0.1942,-1.473214 0.0469,0.03348 0.27455,0.200893 0.22768,0.16741 0.41518,0.29799 0.1875,0.130581 0.39509,0.24442 0.20759,0.113839 0.30804,0.113839 0.27455,0 0.3683,-0.247767 0.16741,-0.441965 0.38505,-0.753349 0.21763,-0.311383 0.4654,-0.508928 0.24776,-0.197545 0.58928,-0.31808 0.34152,-0.120536 0.68974,-0.170759 0.34821,-0.05022 0.83705,-0.07031 z"/></svg></i>',
                            keepHtml: true,
                            keepTagContents: ['span'], //Remove tags and keep the contents
                            badTags: ['applet', 'col', 'colgroup', 'embed', 'noframes', 'noscript', 'script', 'style', 'title', 'meta', 'link', 'head'], //Remove full tags with contents
                            badAttributes: ['bgcolor', 'border', 'height', 'cellpadding', 'cellspacing', 'lang', 'start', 'style', 'valign', 'width', 'data-(.*?)'], //Remove attributes from remaining tags
                            limitChars: 0, // 0|# 0 disables option
                            limitDisplay: 'both', // none|text|html|both
                            limitStop: false, // true/false
                            limitType: 'text', // text|html
                            notTimeOut: 850, //time before status message is hidden in miliseconds
                            keepImages: true, // if false replace with imagePlaceholder
                            imagePlaceholder: 'https://via.placeholder.com/200'
                        }
                    });

                        // Apply corrected styles
                    $("<style id=\"fixed\">.note-editor .dropdown-toggle::after { all: unset; } .note-editor .note-dropdown-menu { box-sizing: content-box; } .note-editor .note-modal-footer { box-sizing: content-box; }</style>")
                            .prependTo("body");

                    });
                </script>

                <?php
                $return_html .= ob_get_contents();
                ob_end_clean();

                break;

            case 'submit':
                $data['type'] = 'submit';
                $data['class'] = 'btn btn-eap btn-primary btn-submit';
                if(isset($field_configuration['class'])) {
                    $data['class'] = $field_configuration['class'];
                }
                if(isset($field_configuration['value'])) {
                    $data['value'] = $field_configuration['value'];
                }
                $return_html .= form_submit($data);
                break;

            case 'button':
                $data['type'] = 'button';
                $data['class'] = 'btn btn-eap btn-primary btn-submit';
                if(isset($field_configuration['content'])) {
                    $data['content'] = $field_configuration['content'];
                }
                if(isset($field_configuration['class'])) {
                    $data['class'] = $field_configuration['class'];
                }
                if(isset($field_configuration['buttontype'])) {
                    $data['type'] = $field_configuration['buttontype'];
                }
                $return_html .= form_button($data);
                break;

            case 'hidden':
                if(isset($field_configuration['value'])) {
                    $data['value'] = (string)$field_configuration['value'];
                }
                $return_html .= form_hidden($data['name'], $data['value']);
                break;
            case 'file':
                if(!isset($field_configuration['name'])) {
                    $data['name'] = 'file[' . $field_key . ']';
                }
                if(isset($field_configuration['preview']) && $field_configuration['preview']==true && isset($field_configuration['public_path']) && file_exists(WRITEPATH . "/" . $data['value']) && is_file(WRITEPATH . "/" . $data['value'])) {
                    $file_info = new CodeIgniter\Files\File(WRITEPATH . "/" . $data['value']);
                    if($file_info->getExtension() == 'pdf') {
                            $return_html .= '<br>'.basename($data['value']);
                    } else {
                        $return_html .= '<br>'.img($field_configuration['public_path'] . basename($data['value']), false, ['width' => $field_configuration['preview_width'] ?? 50, 'height' => 'auto', 'class' => 'mb-3']);
                    }
                }
                $return_html .= form_upload($data, '', $field_configuration);
                break;
            case 'imgmodal':
                if(!isset($field_configuration['name'])) {
                    $data['name'] = 'file[' . $field_key . ']';
                }
                if(isset($field_configuration['preview']) && isset($field_configuration['public_path']) && file_exists(WRITEPATH . "/" . $data['value'])) {
                    $return_html .= '<br>'.img($field_configuration['public_path'] . basename($data['value']), false, ['width' => '50', 'height' => 'auto', 'class' => 'mb-3']);
                }
                $return_html .= form_button(['class' => 'btn btn-secondary', 'content' => 'Foto auswählen', 'data-toggle' => 'modal', 'data-target' => '#eap-modal', 'data-modal-size' => 'lg', 'data-href' => '']);
                break;
            case 'upload_images':
                if(!isset($field_configuration['name'])) {
                    $data['name'] = 'files[' . $field_key . '][]';
                }

                $maximum = 999;
                if(isset($field_configuration['max'])) {
                    $maximum = $field_configuration['max'];
                }

                $data['value'] = str_replace('["https:\/\/ea-plus.ddev.site\/assets\/images\/placeholder.png"]', '', $data['value']);

                $uploaded_images_count = 0;
                if(!is_null($data['value'])) {
                    $return_html .= '<div class="d-flex">';
                    $images = json_decode($data['value'], true);
                    $uploaded_images_count = count($images ?? []);
                    if (is_array($images)) {
                        $return_html .= '<div class="preview-images d-flex"" id="preview-images-'.md5($data['name']).'">';

                        foreach ($images as $filepath) {
                            if($filepath == '' || !is_string($filepath)) continue;
                            $filepath_md5 = md5($filepath);
                            if (isset($field_configuration['preview']) && isset($field_configuration['public_path']) && file_exists(WRITEPATH . "/" . $filepath)) {
                                $return_html .= '<div class="me-3">';
                                $return_html .= '<input type="checkbox" name="delete_images[]" id="delete_images_' . $filepath_md5 . '" value="' . $filepath . '" data-bs-original-title="löschen"> <label for="delete_images_' . $filepath_md5 . '">löschen</label> <br><br> ';
                                $return_html .= img($field_configuration['public_path'] . basename($filepath), false, ['width' => '50', 'height' => 'auto', 'class' => 'mb-3']);
                                $return_html .= '</div>';
                            }

                        }
                        $return_html .= '</div>';
                    }
                    $return_html .= '</div>';
                }

                $field_configuration['multiple'] = 'multiple';
                $field_configuration['accept'] = 'image/png, image/gif, image/jpeg';

                if($uploaded_images_count < $maximum) {
                    $return_html .= form_upload($data, '', $field_configuration);
                }

                ob_start();
                ?>
                <div id="file-limit-warning-<?=md5($data['name']);?>" class="text-danger mt-2" style="display: none;">Maximal <?=$maximum;?> Bilder sind erlaubt.</div>
                <script>
                    $(document).ready(function() {
                        const maxImages = <?=$maximum;?>;
                        const uploadedImagesCount = $('#preview-images-<?=md5($data['name']);?> img').length;

                        $('#<?=$data['id'];?>').on('change', function() {
                            const selectedFiles = this.files.length;
                            const totalImages = uploadedImagesCount + selectedFiles;

                            if (totalImages > maxImages) {
                                $('#file-limit-warning-<?=md5($data['name']);?>').show();
                                this.value = ''; // Clear the selected files
                            } else {
                                $('#file-limit-warning-<?=md5($data['name']);?>').hide();
                            }
                        });
                    });
                </script>

                <?php
                $return_html .= ob_get_contents();
                ob_end_clean();


                break;
            case 'upload_files':
                if(!isset($field_configuration['name'])) {
                    $data['name'] = 'files[' . $field_key . '][]';
                }

                $maximum = 999;
                if(isset($field_configuration['max'])) {
                    $maximum = $field_configuration['max'];
                }

                $uploaded_images_count = 0;
                if(!is_null($data['value'])) {
                    $return_html .= '<div class="d-flex">';
                    $images = json_decode($data['value'], true);
                    $uploaded_images_count = count($images ?? []);
                    if (is_array($images)) {
                        $return_html .= '<div class="preview-images d-flex"" id="preview-images-'.md5($data['name']).'">';

                        foreach ($images as $filepath) {
                            $filepath_md5 = md5($filepath);
                            if (isset($field_configuration['preview']) && isset($field_configuration['public_path']) && file_exists(WRITEPATH . "/" . $filepath)) {
                                $file_extension = pathinfo($filepath, PATHINFO_EXTENSION);

                                $return_html .= '<div class="me-3">';
                                $return_html .= '<input type="checkbox" name="delete_files[]" id="delete_files_'.$filepath_md5.'" value="'.$filepath.'" data-bs-original-title="löschen"> <label for="delete_files_'.$filepath_md5.'">löschen</label> <br><br> ';
                                $return_html .= '<i class="bi bi-filetype-'.$file_extension.' fs-1" title="'.basename($filepath).'"></i>';
                                $return_html .= '</div>';
                            }
                        }
                        $return_html .= '</div>';
                    }
                    $return_html .= '</div>';
                }

                $field_configuration['multiple'] = 'multiple';

                if(!isset($field_configuration['accept'])) {
                    $field_configuration['accept'] = '.pdf,.jpg';
                }

                if($uploaded_images_count < $maximum) {
                    $return_html .= form_upload($data, '', $field_configuration);
                }

                ob_start();
                ?>
                <div id="file-limit-warning-<?=md5($data['name']);?>" class="text-danger mt-2" style="display: none;">Maximal <?=$maximum;?> Bilder sind erlaubt.</div>
                <script>
                    $(document).ready(function() {
                        const maxImages = <?=$maximum;?>;
                        const uploadedImagesCount = $('#preview-images-<?=md5($data['name']);?> img').length;

                        $('#<?=$data['id'];?>').on('change', function() {
                            const selectedFiles = this.files.length;
                            const totalImages = uploadedImagesCount + selectedFiles;

                            if (totalImages > maxImages) {
                                $('#file-limit-warning-<?=md5($data['name']);?>').show();
                                this.value = ''; // Clear the selected files
                            } else {
                                $('#file-limit-warning-<?=md5($data['name']);?>').hide();
                            }
                        });
                    });
                </script>

                <?php
                $return_html .= ob_get_contents();
                ob_end_clean();


                break;


            case 'upload_media':
                if(!isset($field_configuration['name'])) {
                    $data['name'] = 'files[' . $field_key . '][]';
                }

                $maximum = 999;
                if(isset($field_configuration['max'])) {
                    $maximum = $field_configuration['max'];
                }

                $uploaded_videos_count = 0;
                if(!is_null($data['value'])) {
                    $return_html .= '<div class="d-flex">';
                    $videos = json_decode($data['value'], true);
                    $uploaded_videos_count = count($videos ?? []);
                    if (is_array($videos)) {
                        $return_html .= '<div class="preview-media d-flex"" id="preview-media-'.md5($data['name']).'">';
                        foreach ($videos as $filepath) {
                            if($filepath == '' || !is_string($filepath)) continue;
                            $filepath_md5 = md5($filepath);
                            if (isset($field_configuration['preview']) && isset($field_configuration['public_path']) && file_exists(WRITEPATH . "/" . $filepath)) {
                                $return_html .= '<div class="me-3">';
                                $return_html .= '<input type="checkbox" name="delete_media[]" id="delete_media_'.$filepath_md5.'" value="'.$filepath.'" data-bs-original-title="löschen"> <label for="delete_media_'.$filepath_md5.'">löschen</label> <br><br> ';
                                $return_html .= video(
                                    $field_configuration['public_path'] . basename($filepath),
                                    'Keine Videounterstützung in diesem Browser',
                                    'width="100" height="auto" class="mb-3"'
                                );
                                $return_html .= '</div>';
                            }
                        }
                        $return_html .= '</div>';
                    }
                    $return_html .= '</div>';
                }

                $field_configuration['multiple'] = 'multiple';
                $field_configuration['accept'] = 'video/mp4';

                if($uploaded_videos_count < $maximum) {
                    $return_html .= form_upload($data, '', $field_configuration);
                }

                ob_start();
                ?>
                <div id="file-limit-warning-<?=md5($data['name']);?>" class="text-danger mt-2" style="display: none;">Maximal <?=$maximum;?> Videos sind erlaubt.</div>
                <script>
                    $(document).ready(function() {
                        const maxVideos = <?=$maximum;?>;
                        const uploadedVideosCount = $('#preview-images-<?=md5($data['name']);?> img').length;

                        $('#custom_videos').on('change', function() {
                            const selectedFiles = this.files.length;
                            const totalVideos = uploadedVideosCount + selectedFiles;

                            if (totalVideos > maxVideos) {
                                $('#file-limit-warning-<?=md5($data['name']);?>').show();
                                this.value = ''; // Clear the selected files
                            } else {
                                $('#file-limit-warning-<?=md5($data['name']);?>').hide();
                            }
                        });
                    });
                </script>

                <?php
                $return_html .= ob_get_contents();
                ob_end_clean();


                break;



            case 'upload':
                $data['name'] = 'file['.$field_key.']';
                $image_path = base_url('file/image/logo/'.$data['value']);
                $image_path_root = WRITEPATH . 'uploads/logo/'.$data['value'];

                ob_start();
                ?>
                <div class="form-group"><label for="<?=$field_key;?>" class="d-block">Foto <button type="button" class="btn btn-danger btn-quadrat" onclick="deleteImage('<?=$field_key;?>');" data-bs-original-title="löschen">×</button></label>
                    <div class="upload-box image image__upload <?=$field_key;?>" id="upload-box-<?=$field_key;?>" style="background-image: url(&quot;<?=$image_path;?>&quot;);">
                        <input type="file" multiple accept="image/*" name="<?=$field_key;?>" id="file-<?=$field_key;?>"></div>
                    <input type="hidden" name="change-upload-<?=$field_key;?>" value="0">
                </div>

                <script>
                    let imagesArray_<?=$field_key;?> = {<?=$field_key;?>: ''}

                    function deleteImage(field) {
                        imagesArray_<?=$field_key;?>[field] = '';
                        $('#file-' + field).val('');
                        $('input[name="change-upload-' + field+'"]').val('1');
                        displayImages();
                    }

                    function displayImages() {
                        let images = ""
                        //console.log('imagesArray', imagesArray);

                        for (var imageKey in imagesArray_<?=$field_key;?>) {
                            let image = imagesArray_<?=$field_key;?>[imageKey];
                            $('#upload-box-' + imageKey).css("background-image", "url(" + image + ")");
                        }
                    }

                    $('.image__upload input').change(function(e) {
                        const field = $(this).closest('.image__upload').attr('class').split(' ').pop();
                        const files = e.target.files;
                        if(files && files.length > 0) {
                            imagesArray_<?=$field_key;?>[field] = URL.createObjectURL(files[0]);
                        }
                        $('input[name="change-upload-' + field+'"]').val('1');
                        displayImages(field);
                    });

                    <?php if(isset($image_path)) { ?>
                    imagesArray_<?=$field_key;?>['<?=$field_key;?>'] = '<?php $return_html .= $image_path; ?>';
                    <?php } ?>

                    displayImages();
                </script>


                <?php
                $return_html .= ob_get_contents();
                ob_end_clean();

                break;
            case 'checkbox':
                $return_html .= '<div class="form-check form-switch">';
                if(!isset($field_configuration['name'])) {
                    $field_configuration['name'] = $data['name'];
                }
                if(!isset($field_configuration['checked'])) {
                    $field_configuration['checked'] = $data['value'] == 1;
                }
                $return_html .= form_checkbox($field_configuration);
                $return_html .= '</div>';
                break;
            case 'radio':
                $return_html .= '<div class="form-check form-switch">';
                if(!isset($field_configuration['name'])) {
                    $field_configuration['name'] = $data['name'];
                }
                if(!isset($field_configuration['checked'])) {
                    $field_configuration['checked'] = $data['value'] == 1;
                }
                $return_html .= form_radio($field_configuration);
                $return_html .= '</div>';
                break;
            case 'checkboxes':
                $field_configuration['type'] = 'checkbox';
                $selected_values = json_decode($data['value']);
                $options = $field_configuration['options'];

                $return_html .= '<div class="d-grid" style="grid-template-columns: auto auto auto auto;">';
                if(isset($options)) {
                    foreach ($options as $value => $label) {
                        $field_configuration['id'] = uniqid();
                        $field_configuration['name'] = $data['name'] . '['.$value.']';
                        unset($field_configuration['checked']);
                        $return_html .= '<div class="form-check">';
                        $field_configuration['role'] = 'switch';

                        if (!isset($field_configuration['checked']) && is_array($selected_values)) {
                            $field_configuration['checked'] = in_array($value, $selected_values);
                        }
                        unset($field_configuration['options']);
                        $field_configuration['value'] = 1;
                        $return_html .= form_checkbox($field_configuration);
                        $return_html .= form_label($label, $field_configuration['id'], ['class' => "form-check-label"]);
                        $return_html .= '</div>';
                    }
                }
                $return_html .= '</div>';

                break;
            case 'checkboxes_grouped':
                $field_configuration['type'] = 'checkbox';
                $selected_values = json_decode($data['value']);
                $options = $field_configuration['options'];

                $return_html .= '<div class="d-grid" style="grid-template-columns: auto auto auto auto;">';
                if(isset($options)) {
                    $groups = $options;
                    foreach($groups as $group=>$options) {
                        $return_html .= '<h6 class="d-block fw-bold mt-3" style="grid-column: 1 / -1;">'.$group.'</h6>';

                        foreach ($options as $value => $label) {
                            $field_configuration['id'] = uniqid();
                            $field_configuration['name'] = $data['name'] . '[' . $value . ']';
                            unset($field_configuration['checked']);
                            $return_html .= '<div class="form-check">';
                            $field_configuration['role'] = 'switch';

                            if (!isset($field_configuration['checked']) && is_array($selected_values)) {
                                $field_configuration['checked'] = in_array($value, $selected_values);
                            }
                            unset($field_configuration['options']);
                            $field_configuration['value'] = 1;
                            $return_html .= form_checkbox($field_configuration);
                            $return_html .= form_label($label, $field_configuration['id'], ['class' => "form-check-label"]);
                            $return_html .= '</div>';
                        }
                    }
                }
                $return_html .= '</div>';

                break;
            case 'magicsuggest':
                $return_html .= form_input($data);
                $magicsuggest_init_data = $form_data[$field_key];
                //data: $magicsuggest_init_data;

                ob_start();
                ?>
                <script>
                    $(function () {
                        $('#<?=$field_configuration['id'];?>').magicSuggest({
                            data: <?=json_encode($field_configuration['options']);?>,
                            allowFreeEntries: false,
                            maxSelection: <?=$field_configuration['maxSelection'];?>
                        });
                    });
                </script>
            <?php
                $return_html .= ob_get_contents();
                ob_end_clean();

                break;
            case 'editorjs':
                $return_html .= form_hidden($data['name'], $data['value']);
                if(isset($form_data[$field_key])) {
                    $blocks_init_data = $form_data[$field_key];
                }
                if(!isset($blocks_init_data) || $blocks_init_data == '' || $blocks_init_data[0] !== '[') {
                    $blocks_init_data = '[]';
                }

                ob_start();
                ?>
                <div class="ce-example__content _ce-example__content--small">
                    <div id="editorjs"></div>
                </div>

                <script src="<?=base_url('assets/editorjs/_header.js');?>"></script><!-- Header -->
                <script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@latest"></script><!-- Delimiter -->
                <script src="<?=base_url('assets/editorjs/_columns.js');?>"></script><!-- Columns -->
                <script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script><!-- Image -->
                <script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@latest"></script><!-- Delimiter -->
                <script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script><!-- List -->
                <script src="https://cdn.jsdelivr.net/npm/@editorjs/checklist@latest"></script><!-- Checklist -->
                <script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script><!-- Quote -->
                <script src="https://cdn.jsdelivr.net/npm/@editorjs/code@latest"></script><!-- Code -->
                <script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@latest"></script><!-- Embed -->
                <script src="https://cdn.jsdelivr.net/npm/@editorjs/table@latest"></script><!-- Table -->
                <script src="https://cdn.jsdelivr.net/npm/@editorjs/link@latest"></script><!-- Link -->
                <script src="https://cdn.jsdelivr.net/npm/@editorjs/warning@latest"></script><!-- Warning -->
                <script src="https://cdn.jsdelivr.net/npm/@editorjs/marker@latest"></script><!-- Marker -->
                <script src="https://cdn.jsdelivr.net/npm/@editorjs/inline-code@latest"></script><!-- Inline Code -->
                <script src="https://cdn.jsdelivr.net/npm/editorjs-alert@latest"></script>
                <script src="https://cdn.jsdelivr.net/npm/@calumk/editorjs-codeflask@latest"></script>
                <script src="https://cdn.jsdelivr.net/npm/@calumk/editorjs-nested-checklist@latest"></script>
                <script src="https://cdn.jsdelivr.net/npm/@calumk/editorjs-paragraph-linebreakable"></script>
                <script src="https://cdn.jsdelivr.net/npm/editorjs-html@3.4.0/build/edjsHTML.browser.js"></script>

                <!-- Load Editor.js's Core -->
                <script src="<?=base_url('assets/editorjs/editor.js');?>"></script>

                <!-- Initialization -->
                <script>
                    const cPreview = (function (module) {
                        /**
                         * Shows JSON in pretty preview
                         * @param {object} output - what to show
                         * @param {Element} holder - where to show
                         */
                        module.show = function(output, holder) {
                            /** Make JSON pretty */
                            output = JSON.stringify( output, null, 4 );
                            /** Encode HTML entities */
                            output = encodeHTMLEntities( output );
                            /** Stylize! */
                            output = stylize( output );
                            holder.innerHTML = output;
                        };

                        module.html = function(editorjs_data) {
                            console.log('convert');

                            const edjsParser = edjsHTML();
                            const HTML = edjsParser.parse(editorjs_data);
                            var content_html = '';
                            HTML.forEach(function(item) {
                                content_html += item;
                            });
                            return content_html;
                        };

                        /**
                         * Converts '>', '<', '&' symbols to entities
                         */
                        function encodeHTMLEntities(string) {
                            return string.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        }

                        /**
                         * Some styling magic
                         */
                        function stylize(string) {
                            /** Stylize JSON keys */
                            string = string.replace( /"(\w+)"\s?:/g, '"<span class=sc_key>$1</span>" :');
                            /** Stylize tool names */
                            string = string.replace( /"(paragraph|quote|list|header|link|code|image|delimiter|raw|checklist|table|embed|warning)"/g, '"<span class=sc_toolname>$1</span>"');
                            /** Stylize HTML tags */
                            string = string.replace( /(&lt;[\/a-z]+(&gt;)?)/gi, '<span class=sc_tag>$1</span>' );
                            /** Stylize strings */
                            string = string.replace( /"([^"]+)"/gi, '"<span class=sc_attr>$1</span>"' );
                            /** Boolean/Null */
                            string = string.replace( /\b(true|false|null)\b/gi, '<span class=sc_bool>$1</span>' );
                            return string;
                        }

                        return module;
                    })({});

                    let column_tools = {
                        header: Header,
                        alert : Alert,
                        paragraph : editorjsParagraphLinebreakable,
                        delimiter : Delimiter
                    }

                    /**
                     * To initialize the Editor, create a new instance with configuration object
                     * @see docs/installation.md for mode details
                     */
                    var editor = new EditorJS({
                        /**
                         * Enable/Disable the read only mode
                         */
                        readOnly: false,

                        /**
                         * Wrapper of Editor
                         */
                        holder: 'editorjs',

                        /**
                         * Common Inline Toolbar settings
                         * - if true (or not specified), the order from 'tool' property will be used
                         * - if an array of tool names, this order will be used
                         */
                        inlineToolbar: ['link', 'marker', 'bold', 'italic'],
                        inlineToolbar: true,

                        /**
                         * Tools list
                         */
                        tools: {
                            /**
                             * Each Tool is a Plugin. Pass them via 'class' option with necessary settings {@link docs/tools.md}
                             */
                            header: {
                                class: Header,
                                inlineToolbar: ['marker', 'link'],
                                config: {
                                    placeholder: 'Header'
                                },
                                shortcut: 'CMD+SHIFT+H'
                            },

                            /**
                             * Or pass class directly without any configuration
                             */
                            image: {
                                class: ImageTool,
                                config: {
                                    endpoints: {
                                        byFile: '<?=site_url('admin/upload/file');?>', // Your backend file uploader endpoint
                                        byUrl: '<?=site_url('admin/upload/url');?>', // Your endpoint that provides uploading by Url
                                    }
                                }
                            },

                            columns : {
                                class : editorjsColumns,
                                config : {
                                    tools : column_tools, // IMPORTANT! ref the column_tools
                                }
                            },

                            list: {
                                class: List,
                                inlineToolbar: true,
                                shortcut: 'CMD+SHIFT+L'
                            },

                            checklist: {
                                class: Checklist,
                                inlineToolbar: true,
                            },

                            quote: {
                                class: Quote,
                                inlineToolbar: true,
                                config: {
                                    quotePlaceholder: 'Enter a quote',
                                    captionPlaceholder: 'Quote\'s author',
                                },
                                shortcut: 'CMD+SHIFT+O'
                            },

                            warning: Warning,

                            marker: {
                                class:  Marker,
                                shortcut: 'CMD+SHIFT+M'
                            },

                            code: {
                                class:  CodeTool,
                                shortcut: 'CMD+SHIFT+C'
                            },

                            delimiter: Delimiter,

                            inlineCode: {
                                class: InlineCode,
                                shortcut: 'CMD+SHIFT+C'
                            },

                            linkTool: LinkTool,

                            embed: Embed,

                            table: {
                                class: Table,
                                inlineToolbar: true,
                                shortcut: 'CMD+ALT+T'
                            },

                        },

                        /**
                         * This Tool will be used as default
                         */
                        defaultBlock: 'paragraph',

                        /**
                         * Initial Editor data
                         */

                        data: {
                            blocks: <?=$blocks_init_data;?>
                        },

                        onReady: function(){
                            //saveButton.click();
                        },
                        onChange: function(api, event) {
                            editor.save()
                                .then((savedData) => {
                                    $('input[name="data[content_json]"]').val(JSON.stringify(savedData.blocks, null, 4));
                                    $('input[name="data[content]"]').val(cPreview.html(savedData));
                                })
                                .catch((error) => {
                                    console.error('Saving error', error);
                                });
                            console.log('something changed', event);
                        }
                    });

                </script>


                <?php
                $return_html .= ob_get_contents();
                ob_end_clean();

                break;
            case 'variants':

                $model_filename = $field_configuration['model_filename'];
                $model_name = $field_configuration['model_name'];
                $model_class = model($model_name.'Model');
                $table = new \App\Libraries\Table();

                $template = array(
                    'table_open' => '<table class="table table-hover table-bordered table-striped dataTable" id="table-'.$model_name.'">'
                );
                $table->set_template($template);
                $table->set_heading(array_merge($model_class->getTableHeader(), ['']));

                foreach ($model_class->where($field_configuration['model_primary_key'], $field_configuration[$field_configuration['model_primary_key']])->findAll() as $entity_entity) {
                    $primary_key_field = $model_class->getPrimaryKeyField();
                    $actions = '<button data-href="'.site_url('admin/'.$model_filename.'/form/' . $entity_entity->{$primary_key_field}) . '?plain=1&model=' . $model_filename . $field_configuration['params'] . $entity_entity->getIdParams() .'" class="btn btn-default action edit_variant py-0 px-1" title="Bearbeiten" data-toggle="modal" data-target="#eap-modal" data-modal-size="l"><i class="bi bi-pencil"></i></button>';
                    $actions .= '<a href="'.site_url('admin/'.$model_filename.'/delete/' . $entity_entity->{$primary_key_field}) . '?plain=1&model=' . $model_filename . $field_configuration['params'] . $entity_entity->getIdParams() .'" class="btn btn-default action del py-0 px-1" title="Löschen"><i class="bi bi-trash"></i></>';

                    $table->add_row(
                        array_merge($model_class->getTableFields($entity_entity),
                            [['class'=>'no-trim', 'data'=>$actions]])
                    );
                }

                $variants_table = $table->generate();

                ob_start();
?>
<span id="success_message"></span>
<div class="card">
<div class="card-header">
    <div class="row">
        <div class="col col-md-6">Einträge</div>
        <div class="col col-md-6" align="right">
            <button data-href="<?=site_url('admin/'.$model_filename.'/form');?>?plain=1&model=<?=$model_filename;?>" class="btn btn-default action edit_variant" title="Neue Organisation hinzufügen" data-toggle="modal" data-target="#eap-modal" data-modal-size="l"><i class="bi bi-plus"></i></>
        </div>
    </div>
</div>
<div class="card-body">

    <div class="table-responsive">
        <?=$variants_table;?>
    </div>
</div>
</div>


<!-- Modal -->
<div class="modal fade" id="variants_edit_modal" tabindex="-1" aria-labelledby="variants_edit_modalLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="variants_edit_modalLabel">Bearbeiten</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            ...
        </div>
    </div>
</div>
</div>


<?php
                $return_html .= ob_get_contents();
                ob_end_clean();

                break;
            case 'textarea':
                if(isset($field_configuration['rows'])) {
                    $data['rows'] = $field_configuration['rows'];
                }
                if(isset($field_configuration['cols'])) {
                    $data['cols'] = $field_configuration['cols'];
                }

                $return_html .= form_textarea($data);
                break;
            case 'dropdown':
                if(!isset($field_configuration['options'])) {
                    return false;
                }
                $data = [
                    'name'      => $field_configuration['name'] ?? 'data['.$field_key.']',
                    'id'        => $field_configuration['id'] ?? $field_key,
                    'class' => 'form-control form-select',
                    'options' => $field_configuration['options'],
                ];
                if(isset($form_data[$field_key])) {
                    $data['selected'] = $form_data[$field_key];
                }
                if(isset($field_configuration['value'])) {
                    $data['selected'] = $field_configuration['value'];
                }
                if(isset($field_configuration['disabled'])) {
                    $data['disabled'] = $field_configuration['disabled'];
                }
                if(isset($field_configuration['onchange'])) {
                    $data['onchange'] = $field_configuration['onchange'];
                }
                if(isset($field_configuration['onblur'])) {
                    $data['onblur'] = $field_configuration['onblur'];
                }
                if(isset($field_configuration['required'])) {
                    $data['required'] = $field_configuration['required'];
                }

                $return_html .= form_dropdown($data);
                break;
            case 'multiple':
                $data = [
                    'name'      => $field_configuration['name'] ?? 'data['.$field_key.'][]',
                    'id'        => $field_configuration['id'] ?? $field_key,
                    'class' => 'form-control form-select',
                    'options' => $field_configuration['options'],
                    'multiple' => 'multiple',
                    'style' => $field_configuration['style'] ?? '',
                ];
                if(isset($form_data[$field_key])) {
                    $data['selected'] = $form_data[$field_key];
                }
                if(isset($field_configuration['value'])) {
                    $data['selected'] = $field_configuration['value'];
                }
                if(isset($field_configuration['disabled'])) {
                    $data['disabled'] = $field_configuration['disabled'];
                }
                if(isset($data['selected']) && is_string($data['selected'])) {
                    $data['selected'] = json_decode($data['selected'], true);
                }

                $return_html .= form_dropdown($data);

                break;
            case 'dropdown_model':
                if(!isset($field_configuration['model'])) {
                    return false;
                }

                $model = model($field_configuration['model']);
                if(!$model) {
                    return false;
                }

                $options = [];

                if(isset($field_configuration['option_all'])) {
                    $options[''] = $field_configuration['option_all'];
                }

                $model_key = $model->getPrimaryKeyField();
                if(isset($field_configuration['model_key'])) {
                    $model_key = $field_configuration['model_key'];
                }

                foreach ($model->findAll() as $entity) {
                    $options[ $entity->getValue($model_key) ] = $entity->getTitle();
                }

                $data = [
                    'name'      => $field_configuration['name'] ?? 'data['.$field_key.']',
                    'id'        => $field_configuration['id'] ?? $field_key,
                    'class' => 'form-control form-select',
                    'options' => $options,
                ];

                if(isset($form_data[$field_key])) {
                    $data['selected'] = $form_data[$field_key];
                }
                if(isset($field_configuration['value'])) {
                    $data['selected'] = $field_configuration['value'];
                }
                if(isset($field_configuration['disabled'])) {
                    $data['disabled'] = $field_configuration['disabled'];
                }
                if(isset($field_configuration['onchange'])) {
                    $data['onchange'] = $field_configuration['onchange'];
                }
                if(isset($field_configuration['onblur'])) {
                    $data['onblur'] = $field_configuration['onblur'];
                }

                if(isset($field_configuration['style'])) {
                    $data['style'] = $field_configuration['style'];
                }

                $return_html .= form_dropdown($data);
                break;
            case 'dropdown_db':
                $data = [
                    'name'      => $field_configuration['name'] ?? 'data['.$field_key.']',
                    'id'        => $field_configuration['id'] ?? $field_key,
                    'class' => 'form-control',
                ];
                if(isset($form_data[$field_key])) {
                    $data['selected'] = $form_data[$field_key];
                }
                if(isset($field_configuration['value'])) {
                    $data['selected'] = $field_configuration['value'];
                }
                if(isset($field_configuration['disabled'])) {
                    $data['disabled'] = $field_configuration['disabled'];
                }

                if(!isset($field_configuration['value']) && isset($form_data[$field_key])) {
                    $field_configuration['value'] = $form_data[$field_key];
                }

                if(!isset($field_configuration['results_on_query'])) {
                    $field_configuration['results_on_query'] = false;
                }

                if(!isset($field_configuration['dropdown_style'])) {
                    $field_configuration['dropdown_style'] = '';
                }

                if(!isset($field_configuration['default_results'])) {
                    $field_configuration['default_results'] = 0;
                }

                if(!isset($field_configuration['class_btn'])) {
                    $field_configuration['class_btn'] = '';
                }

                if(!isset($field_configuration['display_field'])) {
                    $field_configuration['display_field'] = null;
                }

                ob_start();

?>
                <div class="dropdown">
                    <button class="btn btn-default border form-select w-100 <?=$field_configuration['class_btn'];?>" type="button" id="dropdownMenuButton<?=$data['id'];?>" data-bs-toggle="dropdown" aria-expanded="false"><?= (isset($field_configuration['selected_text']) && $field_configuration['selected_text'] !=='') ? $field_configuration['selected_text'] : 'Auswahl';?></button>
                    <ul class="dropdown-menu w-100 p-0" style="<?=$field_configuration['dropdown_style'];?>" aria-labelledby="dropdownMenuButton<?=$data['id'];?>">
                        <input class="form-control " id="dropdownSearch<?=$data['id'];?>" type="text" placeholder="Suche.." autocomplete="off" >
                        <div id="dropdownItems<?=$data['id'];?>" style="min-height: 25px; max-height: 250px; overflow-y: auto;"></div>
                    </ul>
                    <input type="hidden" id="selectedItemId<?=$data['id'];?>" name="<?=$data['name'];?>" value="<?=$data['selected'] ?? 0;?>">
                    <input type="hidden" id="selectedItemText<?=$data['id'];?>" name="<?php if(str_contains($data['name'], "]")) { echo str_replace("]", "_text]", $data['name']); } else { echo $data['name'] . "_text"; } ;?>" value="<?= $field_configuration['selected_text'] ?? '';?>">
                </div>

<script type="text/javascript">
    $(document).ready(function() {
        function fetchDropdownItems(query) {
            $.ajax({
                url: '<?=site_url($field_configuration['json_url']);?>?results_on_query=<?=$field_configuration['results_on_query'];?>&default_results=<?=$field_configuration['default_results'];?>&display_field=<?=$field_configuration['display_field'];?>',
                method: 'GET',
                dataType: 'json',
                data: { q: query },
                success: function(data) {
                    var dropdownItems = $('#dropdownItems<?=$data['id'];?>');
                    dropdownItems.empty(); // Clear previous items
                    $.each(data, function(item_id, item_name) {
                        dropdownItems.append('<li><button type="button" class="dropdown-item dropdown-item<?=$data['id'];?>" href="#" data-id="' + item_id + '" data-name="' + item_name + '">' + item_name + '</button></li>');
                    });

                    <?php if(isset($field_configuration['value']) && $field_configuration['value']>0) {
                    echo "
                    $('#dropdownItems".$data['id']." li').filter(function() {
                        var value = ".$field_configuration['value'].";
                        if($(this).find('.dropdown-item').data('id') == value) {
                            var selectedName = $(this).text();
                            var selectedId = $(this).find('.dropdown-item').data('id');
                            $('#dropdownMenuButton".$data['id']."').text(selectedName);
                            $('#selectedItemId".$data['id']."').val(selectedId);
                            $('#selectedItemText".$data['id']."').val(selectedName);
                        }
                    });
                    ";
                }
                    ?>
                }
            });
        }

        <?php if(!isset($field_configuration['results_on_query']) || $field_configuration['results_on_query'] == false) { ?>
        $('#dropdownSearch<?=$data['id'];?>').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#dropdownItems<?=$data['id'];?> li').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
        <?php } else { ?>
        $('#dropdownSearch<?=$data['id'];?>').on('keyup', function() {
            var query = $(this).val().toLowerCase();
            fetchDropdownItems(query);
        });
        <?php } ?>
        fetchDropdownItems('');

        $(document).on('click', '.dropdown-item<?=$data['id'];?>', function() {
            var selectedName = $(this).text();
            var selectedId = $(this).data('id');
            $('#dropdownMenuButton<?=$data['id'];?>').text(selectedName);
            $('#selectedItemId<?=$data['id'];?>').val(selectedId);
            $('#selectedItemText<?=$data['id'];?>').val(selectedName);
        });

    });
</script>
<?php
                $return_html .= ob_get_contents();
                ob_end_clean();

                break;
            case 'json_array':
                if(isset($form_data[$field_key])) {
                    $return_html .= form_hidden($field_key, $form_data[$field_key]);
                }

                if($form_data[$field_key] == '' || json_decode($form_data[$field_key]) === JSON_ERROR_NONE) {
                    $form_data[$field_key] = '[]';
                }

                $return_html .= "
<script>
<!--
var items_json_array_".$field_key." = [];
var current_id_counter_".$field_key." = ".count(json_decode($form_data[$field_key], true))."; // Zähler für die ID

function list_items(field_key) {
$('#list_json_array_" . $field_key . "').html('');
items_json_array_".$field_key.".forEach(function(item) {
    $('#list_json_array_" . $field_key . "').append($(item));
});
}

function button_add_item(field_key) {
    var new_field_number = current_id_counter_".$field_key."++;
    var item = $('#new_json_array_" . $field_key . "').html().replace(/FIELD_NR/g, new_field_number);
    
    // ID setzen
    var itemId = 'card-" . $field_key . "_' + new_field_number;
    item = $(item);
    item.attr('id', itemId);
   
    items_json_array_" . $field_key . ".push(item.prop('outerHTML')); // Store HTML string in array
    
    // Element hinzufügen
    $('#list_json_array_" . $field_key . "').append(item);
}

function button_remove_item(field_key, index) {
    items_json_array_".$field_key.".splice(index, 1);
    $('#list_json_array_" . $field_key . "').find('#card-" . $field_key . "_' + index).remove();
}


//-->
</script>
                ";


                $return_html .= '<div id="new_json_array_'.$field_key.'" class="form_json_array_html_pattern" style="display: none;"><div class="card mb-2"><div class="card-body">';
                foreach($field_configuration['fields'] as $inner_field_key=>$inner_field_configuration) {
                    $inner_field_configuration['name'] = 'data['.$field_key.'][FIELD_NR]['.$inner_field_key.']';
                    $return_html .= '<div class="row"><div class="form-group my-1">';
                    $return_html .= form_build_one_field($inner_field_key, $inner_field_configuration, $form_data, true);
                    $return_html .= '</div></div>';
                }
                $return_html .= '<button type="button" class="btn btn-sm btn-danger mt-2" onclick="javascript:button_remove_item(\''.$field_key.'\', FIELD_NR)">Löschen</button>';
                $return_html .= '</div></div></div>';

                $return_html .= '<div id="list_json_array_'.$field_key.'">';

                foreach(json_decode($form_data[$field_key], true) as $field_nr=>$fields) {
                    $return_html .= '<div class="card mb-2" id="card-' . $field_key . '_'.$field_nr.'"><div class="card-body">';
                    if(is_array($fields) && count($fields)) {
                        foreach ($fields as $fields_field_key => $fields_field_value) {
                            if (isset($field_configuration['fields'][$fields_field_key])) {
                                $fields_field_configuration = $field_configuration['fields'][$fields_field_key];
                                $fields_field_configuration['name'] = 'data[' . $field_key . '][' . $field_nr . '][' . $fields_field_key . ']';
                                $fields_field_form_data[$fields_field_key] = $fields_field_value;
                                $return_html .= '<div class="row"><div class="form-group my-1">';
                                $return_html .= form_build_one_field($fields_field_key, $fields_field_configuration, $fields_field_form_data, true);
                                $return_html .= '</div></div>';
                            }
                        }
                    }
                    $return_html .= '<button type="button" class="btn btn-sm btn-danger mt-2" onclick="javascript:button_remove_item(\''.$field_key.'\', '.$field_nr.')">Löschen</button>';
                    $return_html .= '</div></div>';
                }

                $return_html .= '</div>';

                $return_html .= '<button class="btn btn-sm btn-outline-secondary" type="button" onclick="javascript:button_add_item(\''.$field_key.'\')">+</button>';

                break;

        }
    } else {
        dd($field_configuration);
    }

    $return_html .= '</div></div>';

    if($return) {
        return $return_html;
    } else {
        echo $return_html;
    }
}

function form_build_fields($form_configuration_fields, $form_data=null) {
    foreach($form_configuration_fields as $field_key=>$field_configuration) {

        echo '<div class="row"><div class="form-group my-1">';

        form_build_one_field($field_key, $field_configuration, $form_data);

        echo '</div></div>';

    }
}

function form_builder($form_configuration, $form_data=null, $with_form_tags=true) {
    $template_lib = new \App\Libraries\Template();
    $template_lib->setHeaderFooter('templates/plain','templates/plain');
    $template_lib->set('form_configuration', $form_configuration);
    $template_lib->set('with_form_tags', $with_form_tags);
    $template_lib->set('form_data', $form_data);
    $request_uri = ltrim($_SERVER['REQUEST_URI'], '/');
    $template_lib->set('queryroute', $request_uri);
    $template_lib->set('queryparts', explode("/", $request_uri));
    return $template_lib->return('account/form_builder');
}
