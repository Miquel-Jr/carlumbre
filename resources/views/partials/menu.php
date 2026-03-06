<?php
$menuItems = menu();
?>

<style>
.mobile-menu-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 1040;
  display: none;
}

.mobile-menu-backdrop.show {
  display: block;
}

.mobile-menu-drawer {
  position: fixed;
  top: 0;
  left: 0;
  width: 280px;
  max-width: 85vw;
  height: 100vh;
  background: #212529;
  color: #fff;
  transform: translateX(-100%);
  transition: transform 0.2s ease-in-out;
  z-index: 1045;
  overflow-y: auto;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.25);
}

.mobile-menu-drawer.show {
  transform: translateX(0);
}

.mobile-menu-link {
  color: #dee2e6;
  text-decoration: none;
  display: block;
  padding: 0.65rem 0.5rem;
  border-radius: 0.375rem;
}

.mobile-menu-link:hover,
.mobile-menu-link.active {
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
}

.desktop-nav-links {
  gap: 0.25rem;
}

.desktop-nav-links .nav-link {
  white-space: nowrap;
  padding-left: 0.65rem;
  padding-right: 0.65rem;
}

.desktop-user-links {
  gap: 0.25rem;
}

.desktop-user-links .dropdown {
  position: relative;
}

.desktop-user-links .dropdown-menu {
  position: absolute;
  top: calc(100% + 0.25rem);
  right: 0;
  left: auto;
}
</style>

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="/dashboard">🔧 Carlumbre</a>

    <button class="navbar-toggler d-xxl-none" type="button" id="mobileMenuToggle" aria-label="Abrir menú"
      aria-controls="mobileMenu" aria-expanded="false">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="d-none d-xxl-flex flex-grow-1 justify-content-between align-items-center ms-3">
      <ul class="navbar-nav flex-row flex-nowrap align-items-center me-auto mb-0 desktop-nav-links">
        <?php foreach ($menuItems as $item): ?>
        <li class="nav-item">
          <a class="nav-link <?= isActive($item['url']) ? 'active' : '' ?>" href="<?= $item['url'] ?>">
            <?= $item['label'] ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>

      <ul class="navbar-nav flex-row align-items-center ms-auto desktop-user-links">
        <li class="nav-item dropdown" id="desktopUserDropdown">
          <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown"
            aria-expanded="false" id="desktopUserDropdownToggle">
            <?= $_SESSION['user']['name'] ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" id="desktopUserDropdownMenu">
            <li><a class="dropdown-item" href="/profile">Ver mi perfil</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/logout">Cerrar sesión</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div id="mobileMenuBackdrop" class="mobile-menu-backdrop d-xxl-none"></div>

<aside id="mobileMenu" class="mobile-menu-drawer d-xxl-none" aria-hidden="true">
  <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center">
    <strong>Menú</strong>
    <button class="btn btn-sm btn-outline-light" type="button" id="mobileMenuClose">✕</button>
  </div>

  <div class="p-3">
    <?php foreach ($menuItems as $item): ?>
    <a class="mobile-menu-link <?= isActive($item['url']) ? 'active' : '' ?>" href="<?= $item['url'] ?>">
      <?= $item['label'] ?>
    </a>
    <?php endforeach; ?>

    <hr class="border-secondary">

    <div class="small text-light opacity-75 mb-2">
      <?= $_SESSION['user']['name'] ?>
    </div>
    <a class="mobile-menu-link" href="/profile">Ver mi perfil</a>
    <a class="mobile-menu-link" href="/logout">Cerrar sesión</a>
  </div>
</aside>

<script>
(function() {
  const toggleButton = document.getElementById('mobileMenuToggle');
  const closeButton = document.getElementById('mobileMenuClose');
  const mobileMenu = document.getElementById('mobileMenu');
  const backdrop = document.getElementById('mobileMenuBackdrop');

  if (!toggleButton || !mobileMenu || !backdrop) {
    return;
  }

  const openMenu = () => {
    mobileMenu.classList.add('show');
    backdrop.classList.add('show');
    mobileMenu.setAttribute('aria-hidden', 'false');
    toggleButton.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  };

  const closeMenu = () => {
    mobileMenu.classList.remove('show');
    backdrop.classList.remove('show');
    mobileMenu.setAttribute('aria-hidden', 'true');
    toggleButton.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  };

  toggleButton.addEventListener('click', openMenu);
  backdrop.addEventListener('click', closeMenu);
  if (closeButton) {
    closeButton.addEventListener('click', closeMenu);
  }

  mobileMenu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeMenu);
  });

  const desktopUserDropdown = document.getElementById('desktopUserDropdown');
  const desktopUserDropdownToggle = document.getElementById('desktopUserDropdownToggle');
  const desktopUserDropdownMenu = document.getElementById('desktopUserDropdownMenu');

  if (window.bootstrap && desktopUserDropdown && desktopUserDropdownToggle && desktopUserDropdownMenu) {
    const desktopDropdown = bootstrap.Dropdown.getOrCreateInstance(desktopUserDropdownToggle);

    document.addEventListener('click', (event) => {
      const isOpen = desktopUserDropdownMenu.classList.contains('show');
      if (!isOpen) {
        return;
      }

      if (!desktopUserDropdown.contains(event.target)) {
        desktopDropdown.hide();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key !== 'Escape') {
        return;
      }

      const isOpen = desktopUserDropdownMenu.classList.contains('show');
      if (isOpen) {
        desktopDropdown.hide();
      }
    });
  }
})();
</script>
