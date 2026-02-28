<?php
$menuItems = menu();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/dashboard">🔧 Carlumbre</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($menuItems as $item): ?>
                <li class="nav-item">
                    <a class="nav-link <?= isActive($item['url']) ? 'active' : '' ?>" href="<?= $item['url'] ?>">
                        <?= $item['label'] ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="nav-link text-light">
                        <?= $_SESSION['user']['name'] ?? 'Invitado' ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/logout">Cerrar sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>