<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="MateriaX Pro - Plataforma industrial para la recirculación, publicación y auditoría de excedentes poliméricos y materiales técnicos.">
  <title><?= esc($pageTitle ?? 'MateriaX | Red Industrial Circular') ?></title>
  
  <!-- Optimización de Carga Tipográfica (Preconnect - Core Web Vitals) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- CSS Oficial Puro de MateriaX -->
  <link rel="stylesheet" href="<?= base_url('css/materiax.css') ?>">
  
  <!-- Favicon / Isotipo -->
  <link rel="icon" type="image/png" href="<?= base_url('assets/logos/isotipo-black.png') ?>">
</head>
<body>

  <!-- Enlace Accesible para Navegación por Teclado (WCAG 2.2 AA) -->
  <a href="#main-content" class="skip-link">Saltar al contenido principal</a>

  <!-- Cabecera de Navegación Principal -->
  <header class="site-header" role="banner">
    <nav class="nav-container" aria-label="Navegación principal">
      <a href="<?= site_url('/') ?>" class="brand-link">
        <img src="<?= base_url('assets/logos/isotipo-black.png') ?>" alt="Logo MateriaX" class="brand-logo-img" width="32" height="32">
        <span>Materia<span class="brand-accent">X</span></span>
      </a>

      <ul class="nav-menu">
        <?php if (session()->get('isLoggedIn')): ?>
          <?php if (session()->get('rol') === 'admin'): ?>
            <li class="nav-item">
              <a href="<?= site_url('admin') ?>" class="<?= (url_is('admin') && !url_is('admin/lotes*')) ? 'active' : '' ?>" <?= (url_is('admin') && !url_is('admin/lotes*')) ? 'aria-current="page"' : '' ?>>Panel Admin</a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('admin/lotes') ?>" class="<?= url_is('admin/lotes*') ? 'active' : '' ?>" <?= url_is('admin/lotes*') ? 'aria-current="page"' : '' ?>>Moderar Lotes</a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('productos') ?>" class="<?= url_is('productos*') ? 'active' : '' ?>" <?= url_is('productos*') ? 'aria-current="page"' : '' ?>>Mercado</a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a href="<?= site_url('panel') ?>" class="<?= url_is('panel*') ? 'active' : '' ?>" <?= url_is('panel*') ? 'aria-current="page"' : '' ?>>Mi Panel</a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('productos') ?>" class="<?= ((url_is('productos') || url_is('productos/*')) && !url_is('productos/crear*')) ? 'active' : '' ?>" <?= ((url_is('productos') || url_is('productos/*')) && !url_is('productos/crear*')) ? 'aria-current="page"' : '' ?>>Mercado</a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('productos/crear') ?>" class="<?= url_is('productos/crear*') ? 'active' : '' ?>" <?= url_is('productos/crear*') ? 'aria-current="page"' : '' ?>>Publicar Lote</a>
            </li>
          <?php endif; ?>
        <?php else: ?>
          <li class="nav-item">
            <a href="<?= site_url('/') ?>" class="<?= (url_is('/') || url_is('')) ? 'active' : '' ?>" <?= (url_is('/') || url_is('')) ? 'aria-current="page"' : '' ?>>Inicio</a>
          </li>
          <li class="nav-item">
            <a href="<?= site_url('/#ecosistema') ?>">Ecosistema</a>
          </li>
          <li class="nav-item">
            <a href="<?= site_url('/#polimeros') ?>">Polímeros</a>
          </li>
          <li class="nav-item">
            <a href="<?= site_url('/#seguridad') ?>">Seguridad</a>
          </li>
          <li class="nav-item">
            <a href="<?= site_url('/#metricas') ?>">Métricas</a>
          </li>
          <li class="nav-item">
            <a href="<?= site_url('/#contacto') ?>">Contacto</a>
          </li>
        <?php endif; ?>
      </ul>

      <div class="nav-auth">
        <?php if (session()->get('isLoggedIn')): ?>
          <a href="<?= site_url('perfil') ?>" class="user-badge" title="Ver perfil y datos corporativos" style="text-decoration: none;">
            <span class="status-dot" aria-hidden="true"></span>
            <span><?= esc(session()->get('nombre')) ?></span>
          </a>
          <a href="<?= site_url('perfil') ?>" class="btn btn-secondary btn-sm" title="Editar datos y contraseña">Perfil</a>
          <a href="<?= site_url('logout') ?>" class="btn btn-secondary btn-sm" title="Cerrar sesión de forma segura">Cerrar Sesión</a>
        <?php else: ?>
          <a href="<?= site_url('login') ?>" class="btn btn-secondary btn-sm">Iniciar Sesión</a>
          <a href="<?= site_url('register') ?>" class="btn btn-primary btn-sm">Registrar Empresa</a>
        <?php endif; ?>
      </div>
    </nav>
  </header>

  <!-- Contenedor Principal Accesible -->
  <main id="main-content" class="main-wrapper" role="main">

    <!-- Mensaje Flash: Éxito -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success" role="status" aria-live="polite">
        <div><?= esc(session()->getFlashdata('success')) ?></div>
      </div>
    <?php endif; ?>

    <!-- Mensaje Flash: Error General -->
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-error" role="alert" aria-live="assertive">
        <div><?= esc(session()->getFlashdata('error')) ?></div>
      </div>
    <?php endif; ?>

    <!-- Mensaje Flash: Errores de Validación -->
    <?php if (session()->getFlashdata('errors')): ?>
      <div class="alert alert-error" role="alert" aria-live="assertive">
        <div>
          <strong>Por favor corrige los siguientes errores:</strong>
          <ul>
            <?php foreach (session()->getFlashdata('errors') as $fieldError): ?>
              <li><?= esc($fieldError) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endif; ?>
