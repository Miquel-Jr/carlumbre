<?php if (isset($_SESSION['success'])): ?>
    const successMessage = <?= json_encode($_SESSION['success'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: successMessage
    });
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    const errorMessage = <?= json_encode($_SESSION['error'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: errorMessage
    });
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
