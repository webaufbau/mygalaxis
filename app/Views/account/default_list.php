
<div class="col ">

    <!--<h2><?=$page_title;?></h2>-->

    <nav class="navbar navbar-expand-lg navbar-filters rounded-3 my-3">
        <div class="container-fluid">

            <?php if(!isset($controller_name)) {
                $controller_name = $model_name;
            } ?>

            <?php if((isset($has_filter_configuration) && $has_filter_configuration>0) || isset($text_add_new) && $text_add_new!==false) { ?>
            <form class="d-flex form-filters" role="search" method="get">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?=$form_filters;?>
                    <?php if(isset($has_filter_configuration) && $has_filter_configuration>0) { ?>
                    <li class="nav-item px-3">
                        <input type="submit" value="filtern" class="btn btn-primary btn-sm py-1 my-2">
                    </li>
                    <?php } ?>
                    <?php if(isset($url_prefix) && isset($controller_name) && service('request')->getGet('filtered')) { ?>
                    <li class="nav-item px-3">
                        <a href="<?=site_url(strtolower($url_prefix . $controller_name));?>" class="btn btn-outline-primary btn-sm py-1 my-2">Filter zurücksetzen</a>
                    </li>
                    <?php } ?>
                </ul>
                <input type="hidden" name="filtered" value="1">
            </form>
            <?php } ?>


            <div class="d-flex gap-2">

            <?php if(isset($text_add_new) && $text_add_new && isset($url_prefix)) { ?>
                <a href="<?=base_url(strtolower($url_prefix . $controller_name)."/form");?>" class="btn btn-primary btn-sm float-end"><?=$text_add_new;?></a>
            <?php } ?>

            <?php if(isset($navbar_html)) { echo $navbar_html; } ?>

            </div>

        </div>
    </nav>




    <div class="table-responsive">
        <?=$pager_table;?>
    </div>
    <?= $pager_links; ?>

</div>


<?php /*
<link href="<?=base_url('assets/DataTables/datatables.min.css');?>" rel="stylesheet">
<script src="<?=base_url('assets/DataTables/datatables.min.js');?>"></script>
<script>
    new DataTable('.datatable', {
        language: {
            url: '<?=base_url('assets/DataTables/de-DE.json');?>'
        },
        ajax: 'scripts/server_processing.php',
        processing: true,
        serverSide: true
    });
</script>

<style>
    table.dataTable thead>tr>th.dt-orderable-asc:hover, table.dataTable thead>tr>th.dt-orderable-desc:hover, table.dataTable thead>tr>td.dt-orderable-asc:hover, table.dataTable thead>tr>td.dt-orderable-desc:hover {
        outline: 0;
    }
</style>
*/ ?>

