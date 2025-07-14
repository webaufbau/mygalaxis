<div class="text-start container-fluid">
<?php /* if(isset($form_configuration['config']['translation']) && $form_configuration['config']['translation']==true && $page_data) { ?>
<form method="get" action="<?=BASEURL?>">
    <?php echo form_dropdown('languageid', $page_data->getLanguagesArr(), 'large', 'class="form-control" onchange="this.form.submit()"'); ?>
</form>
<?php } */ ?>


<?php
$attributes = ['class' => 'form crud', 'id' => random_string(), 'method' => 'post', 'enctype' => 'multipart/form-data'];
echo form_open($queryroute, $attributes);
?>




<?php if(isset($form_configuration) && is_array($form_configuration['tabs']) && count($form_configuration['tabs'])>1) { ?>
<ul class="nav nav-tabs">
    <?php foreach($form_configuration['tabs'] as $tab_key=>$tab) { ?>
    <li class="nav-item">
        <a class="nav-link <?=$tab_key==$form_configuration['config']['first_tab'] ? 'active' : '';?>" data-bs-toggle="tab" href="#<?='tab_'.$tab_key;?>"><?=$tab;?></a>
    </li>
    <?php } ?>
</ul>
<?php } ?>


<!-- Tabs content -->
<div class="tab-content p-1 py-3">

    <?php foreach($form_configuration['tabs'] as $tab_key=>$tab) { ?>

    <div role="tabpanel" class="tab-pane fade <?=$tab_key==$form_configuration['config']['first_tab'] ? 'active show' : '';?>" id="<?='tab_'.$tab_key;?>">
        <fieldset class="form-section">

            <?php if(isset($form_configuration['fields'][$tab_key])) {

                if(isset($form_configuration['config']['row_fields'][$tab_key])) {

                    foreach($form_configuration['config']['row_fields'][$tab_key] as $row_fields) {
                        echo '<div class="row">';

                        $col_width = 12 / count($row_fields);
                        foreach($row_fields as $field) {
                            if(isset($form_configuration['fields'][$tab_key][$field])) {
                                echo '<div class="form-group my-1 col-12 col-md-'.$col_width.'">';
                                form_build_one_field($field, $form_configuration['fields'][$tab_key][$field], $form_data);
                                echo '</div>';
                            }
                        }
                        echo '</div>';
                    }
                } else {
                    form_build_fields($form_configuration['fields'][$tab_key], $form_data);
                }

            } ?>

        </fieldset>
    </div>

    <?php } ?>


</div>
<!-- Tabs content -->



    <div class="row my-2">
        <div class="col-md-12">
            <div class="btn-group float-end">
                <!--<a href="<?=site_url('crud?model='.$entity->getModelShortname());?>" class="btn btn-default btn-sm"><i class="bi bi-asterisk"></i> Alle anzeigen</a>-->
                <button type="submit" class="btn btn-primary btn-sm" name="submitaction" id="submitactionSave" value="save" title="[CTRL] + [s]">Speichern</button>
                <?php if($id>0) { ?>
                <a href="<?=site_url($url_prefix.$app_controller.'/form', 'https');?>" class="btn btn-info btn-sm">Neuer Eintrag</a>
                <?php } ?>
                <?php if(isset($form_configuration['config']['seo_slug_field']) && isset($form_configuration['fields']['seo'][$form_configuration['config']['seo_slug_field']])) { ?>
                    <a href="<?=site_url($form_configuration['fields']['seo'][$form_configuration['config']['seo_slug_field']]['prefix'].'/'.$entity->getValue($form_configuration['config']['seo_slug_field']), 'https');?>" target="preview" class="btn btn-eap btn-primary btn-sm"><i class="bi bi-eye"></i> Ansehen</a>
                <?php } ?>
                <?php if(isset($id) && $id > 0) { ?>
                    <button type="submit" class="btn btn-danger btn-sm del" name="submitaction" id="submitactionSave" value="delete">Löschen</button>
                <?php } ?>
            </div>
        </div>
    </div>


<?php echo form_close(); ?>

</div>

