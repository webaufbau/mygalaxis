
    <h2><?=$page_title;?></h2>

    <div class="d-flex justify-content-start">
    <a href="<?=site_url($app_controller.'/form/?model='.$model_name);?>" class="btn btn-default"><i class="bi bi-plus"></i></a>

    <form action="<?=site_url($app_controller.'/?model='.$model_name);?>" method="get">
    <div class="input-group">
        <input type="hidden" name="model" value="<?=$model_name;?>">
        <?=form_dropdown(['name' => 'filter', 'options' => ([0=>'Kein Filter ausgewählt'] + (is_array($filters) ? $filters : [])), 'selected' => isset($request['filter'])?$request['filter']:'', 'class' => 'form-control', 'onchange' => 'this.form.submit();']);?>
        <?php if(isset($request['filter'])) { ?>
        <div class="input-group-append">
            <a class="btn btn-default action edit_variant" href="<?=site_url($app_controller.'/form/'.$request['filter'].'?plain=1&model=filter&filtermodel='.$model_name.'&filter_module='.$model_name);?>"><i class="bi bi-pencil"></i></a>
        </div>
        <?php } ?>
        <div class="input-group-append">
            <a class="btn btn-default action edit_variant" href="<?=site_url($app_controller.'/form/?plain=1&model=filter&filtermodel='.$model_name.'&filter_module='.$model_name);?>"><i class="bi bi-plus"></i><i class="bi bi-funnel"></i></a>
        </div>
    </div>
    </form>

    </div>


    <link href="<?=base_url('assets/DataTables/datatables.min.css');?>" rel="stylesheet">
    <script src="<?=base_url('assets/DataTables/datatables.min.js');?>"></script>
    <script>
        new DataTable('.dataTable');
    </script>

    <?=$table;?>

<script>
    $(document).ready(function () {
    <?php if(!is_null($default_order_column) && !is_null($default_order_direction)) { ?>
        $('#table-<?=$model_name;?>').DataTable().order([<?=$default_order_column;?>, '<?=$default_order_direction;?>']).draw();
        <?php } ?>
    });
</script>



    <!-- Modal -->
    <div class="modal fade" id="variants_edit_modal" tabindex="-1" aria-labelledby="variants_edit_modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="variants_edit_modalLabel">Filter</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ...
                </div>
            </div>
        </div>
    </div>
