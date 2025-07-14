<?php $pager->setSurroundCount(10) ?>

<nav aria-label="Page navigation">
    <ul class="pagination">

        <?php
        if(isset($pager_details) && is_array($pager_details)) {
            // Extract values from $pager_details array
            $current_page = $pager_details['currentPage'];
            $per_page = $pager_details['perPage'];
            $total_entries =$pager_details['total'];

            // Calculate the range of entries being displayed
            $from_entry = ($current_page - 1) * $per_page + 1;
            if($from_entry<=0) {$from_entry=0;}
            $to_entry = min($current_page * $per_page, $total_entries);

            $total_entries = number_format($pager_details['total'], 0, ".", "'");

            if($total_entries > 0) {
                echo "<li class='text'>Angezeigt werden $from_entry-$to_entry von $total_entries Einträgen</li>";
            }
        }
        ?>

        <li class="page-item btn-group">
        <?php if ($pager->hasPreviousPage()) : ?>

                <a class="page-link prev" href="<?= $pager->getPreviousPage() ?>" aria-label="<?= lang('Pager.previous') ?>">
                    <span aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                </a>

        <?php endif ?>

        <?php if ($pager->hasNextPage()) : ?>

                <a class="page-link next" href="<?= $pager->getNextPage() ?>" aria-label="<?= lang('Pager.next') ?>">
                    <span aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                </a>

        <?php endif ?>
        </li>

    </ul>
</nav>
