<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="MateriaX Pro - Plataforma industrial para la recirculación, publicación y auditoría de excedentes poliméricos y materiales técnicos.">
  <title><?= esc($pageTitle ?? 'MateriaX | Red Industrial Circular') ?></title>
  
  <!-- Fuentes -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Estilos -->
  <link rel="stylesheet" href="<?= base_url('css/materiax.css') ?>">
  
  <!-- Icono -->
  <link rel="icon" type="image/png" href="<?= base_url('assets/logos/isotipo-black.png') ?>">
</head>
<body>

  <!-- Salto al contenido -->
  <a href="#main-content" class="skip-link">Saltar al contenido principal</a>

  <!-- Menú de navegación -->
  <header class="site-header" role="banner">
    <nav class="nav-container" aria-label="Navegación principal">
      <a href="<?= site_url('/') ?>" class="brand-link">
        <img src="<?= base_url('assets/logos/isotipo-black.png') ?>" alt="Logo MateriaX" class="brand-logo-img" width="32" height="32">
        <span>Materia<span class="brand-accent">X</span></span>
      </a>

      <ul class="nav-menu">
        <?php if (session()->get('isLoggedIn')): ?>
          <?php if (session()->get('rol') === 'admin'): ?>
            <li class="nav-item"><a href="<?= site_url('admin') ?>" <?= nav_attr(url_is('admin') && !url_is('admin/lotes*')) ?>>Panel Admin</a></li>
            <li class="nav-item"><a href="<?= site_url('admin/lotes') ?>" <?= nav_attr(url_is('admin/lotes*')) ?>>Moderar Lotes</a></li>
            <li class="nav-item"><a href="<?= site_url('productos') ?>" <?= nav_attr(url_is('productos*')) ?>>Mercado</a></li>
          <?php else: ?>
            <li class="nav-item"><a href="<?= site_url('panel') ?>" <?= nav_attr(url_is('panel*')) ?>>Mi Panel</a></li>
            <li class="nav-item"><a href="<?= site_url('productos') ?>" <?= nav_attr(url_is('productos*') && !url_is('productos/crear*')) ?>>Mercado</a></li>
            <li class="nav-item"><a href="<?= site_url('productos/crear') ?>" <?= nav_attr(url_is('productos/crear*')) ?>>Publicar Lote</a></li>
            <li class="nav-item"><a href="<?= site_url('plantas') ?>" <?= nav_attr(url_is('plantas*')) ?>>Mis Plantas</a></li>
            <li class="nav-item"><a href="<?= site_url('solicitudes') ?>" <?= nav_attr(url_is('solicitudes*')) ?>>Solicitudes</a></li>
            <li class="nav-item"><a href="<?= site_url('certificados') ?>" <?= nav_attr(url_is('certificados*')) ?>>Certificados</a></li>
            <?php $noLeidos = contar_mensajes_no_leidos(); ?>
            <li class="nav-item">
              <a href="<?= site_url('mensajes') ?>" <?= nav_attr(url_is('mensajes*')) ?>>
                Mensajes <span class="badge badge-success badge-mensajes-live" style="font-size: 0.72rem; padding: 0.15rem 0.45rem; vertical-align: middle; border-radius: 999px; <?= ($noLeidos > 0) ? '' : 'display: none;' ?>"><?= $noLeidos ?></span>
              </a>
            </li>
          <?php endif; ?>
        <?php else: ?>
          <li class="nav-item"><a href="<?= site_url('/') ?>" <?= nav_attr(url_is('/') || url_is('')) ?>>Inicio</a></li>
          <?php foreach (['feed' => 'Mercado', 'contacto' => 'Contacto'] as $anchor => $label): ?>
            <li class="nav-item"><a href="<?= site_url('/#' . $anchor) ?>"><?= $label ?></a></li>
          <?php endforeach; ?>
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
          <a href="<?= site_url('register') ?>" class="btn btn-primary btn-sm">Registrar Empresa (Sign Up)</a>
        <?php endif; ?>
      </div>
    </nav>
  </header>

  <!-- Contenido principal -->
  <main id="main-content" class="main-wrapper" role="main">

    <!-- Mensaje de éxito -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success" role="status" aria-live="polite">
        <div><?= esc(session()->getFlashdata('success')) ?></div>
      </div>
    <?php endif; ?>

    <!-- Mensaje de error -->
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-error" role="alert" aria-live="assertive">
        <div><?= esc(session()->getFlashdata('error')) ?></div>
      </div>
    <?php endif; ?>

    <!-- Errores de validación -->
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
