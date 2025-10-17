<?php $pager->setSurroundCount(2) ?>

<nav aria-label="Seitennavigation">
    <ul class="pagination justify-content-center">
        <?php if ($pager->hasPrevious()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="Erste Seite">
                    <i class="bi bi-chevron-double-left"></i>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getPrevious() ?>" aria-label="Vorherige Seite">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link"><i class="bi bi-chevron-double-left"></i></span>
            </li>
            <li class="page-item disabled">
                <span class="page-link"><i class="bi bi-chevron-left"></i></span>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link): ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= $link['uri'] ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getNext() ?>" aria-label="Nächste Seite">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getLast() ?>" aria-label="Letzte Seite">
                    <i class="bi bi-chevron-double-right"></i>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link"><i class="bi bi-chevron-right"></i></span>
            </li>
            <li class="page-item disabled">
                <span class="page-link"><i class="bi bi-chevron-double-right"></i></span>
            </li>
        <?php endif ?>
    </ul>
</nav>
