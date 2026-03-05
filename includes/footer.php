    </div> <!-- End content-body -->
</main>
</div> <!-- End app-container -->

<!-- Notification System JS -->
<script src="/Gestion_agence_transport/assets/js/notifications.js"></script>

<script>
    // Sidebar Toggle
    document.getElementById('sidebar-toggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('open');
    });

    // ── Auto-display PHP session flash messages as Toasts ──────────
    document.addEventListener('DOMContentLoaded', function () {
        <?php if (!empty($flash_success)): ?>
        Notify.toast('success', <?php echo json_encode($flash_success); ?>);
        <?php endif; ?>

        <?php if (!empty($flash_error)): ?>
        Notify.toast('error', <?php echo json_encode($flash_error); ?>, 5000);
        <?php endif; ?>

        <?php if (!empty($flash_warning)): ?>
        Notify.toast('warning', <?php echo json_encode($flash_warning); ?>);
        <?php endif; ?>
    });
</script>
</body>
</html>
