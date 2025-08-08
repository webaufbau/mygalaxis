</main>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script type="text/javascript">

    $(".del").click(function(){
        if (!confirm("Wirklich löschen?")){
            return false;
        }
    });

    $(".cancel").click(function(){
        if (!confirm("Wirklich abbrechen?")){
            return false;
        }
    });

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el, {
            placement: 'bottom'
        });
    });

</script>

<!-- Footer -->
<footer class="bg-light text-center text-muted py-4 mt-auto border-top">
    <div class="container">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
            <?php if(auth()->user()->inGroup('admin')) { ?>
                <small>&copy; <?= date('Y') ?> Offerten Manager – Alle Rechte vorbehalten</small>
            <?php } ?>

        </div>
    </div>
</footer>



</body>
</html>
