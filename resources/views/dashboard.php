<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard | Carlumbre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">🔧 Carlumbre</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php foreach ($menu as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $item['url'] ?>"><?= $item['label'] ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <span class="navbar-text">
                    <?= $_SESSION['user']['name'] ?>
                    <a href="/logout" class="btn btn-sm btn-warning ms-2">Salir</a>
                </span>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Bienvenido, <?= $_SESSION['user']['name'] ?>!</h1>
        <p>Estas son las opciones disponibles según tu rol:</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>