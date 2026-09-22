<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($pageTitle ?? 'MateriaX | Red Industrial Circular') ?></title>
  
  <!-- CSS Oficial Puro de MateriaX -->
  <link rel="stylesheet" href="<?= base_url('css/materiax.css') ?>">
  
  <!-- Favicon / Isotipo -->
  <link rel="icon" type="image/png" href="<?= base_url('assets/logos/isotipo-black.png') ?>">
</head>
<body>

  <!-- Cabecera de Navegación Principal -->
  <header class="site-header">
    <div class="nav-container">
      <a href="<?= site_url('/') ?>" class="brand-link">
        <img src="<?= base_url('assets/logos/isotipo-black.png') ?>" alt="Logo MateriaX" class="brand-logo-img">
        <span>Materia<span class="brand-accent">X</span></span>
      </a>

      <ul class="nav-menu">
        <li class="nav-item">
          <a href="<?= site_url('/') ?>">Inicio</a>
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
        <?php if (session()->get('isLoggedIn')): ?>
          <?php if (session()->get('rol') === 'admin'): ?>
            <li class="nav-item">
              <a href="<?= site_url('admin') ?>" style="color: #14b8a6; font-weight: 700;">🛡️ Panel Admin</a>
            </li>
            <li class="nav-item">
              <a href="<?= site_url('admin/lotes') ?>">📋 Moderar Lotes</a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a href="<?= site_url('panel') ?>" style="color: #14b8a6; font-weight: 700;">📊 Mi Panel</a>
            </li>
          <?php endif; ?>
          <li class="nav-item">
            <a href="<?= site_url('productos') ?>">📦 Mercado</a>
          </li>
          <li class="nav-item">
            <a href="<?= site_url('productos/crear') ?>">➕ Publicar Lote</a>
          </li>
          <li class="nav-item">
            <a href="<?= site_url('perfil') ?>">👤 Mi Cuenta</a>
          </li>
        <?php endif; ?>
      </ul>

      <div class="nav-auth">
        <?php if (session()->get('isLoggedIn')): ?>
          <?php if (session()->get('rol') === 'admin'): ?>
            <a href="<?= site_url('admin') ?>" class="btn btn-primary btn-sm" style="background: #0f766e; border-color: #14b8a6;" title="Ir al Panel de Administración">
              🛡️ Panel Admin
            </a>
          <?php else: ?>
            <a href="<?= site_url('panel') ?>" class="btn btn-primary btn-sm" style="background: #0f766e; border-color: #14b8a6;" title="Ir a Mi Panel de Empresa">
              📊 Mi Panel
            </a>
          <?php endif; ?>
          <a href="<?= site_url('perfil') ?>" class="user-badge" title="Ver mi cuenta" style="text-decoration: none;">
            <span class="status-dot"></span>
            <span><strong><?= esc(session()->get('nombre')) ?></strong></span>
          </a>
          <a href="<?= site_url('perfil') ?>" class="btn btn-secondary btn-sm" title="Panel de cuenta empresarial">👤 Perfil</a>
          <a href="<?= site_url('logout') ?>" class="btn btn-secondary btn-sm" title="Cerrar sesión de forma segura">Cerrar Sesión</a>
        <?php else: ?>
          <a href="<?= site_url('login') ?>" class="btn btn-secondary btn-sm">Iniciar Sesión</a>
          <a href="<?= site_url('register') ?>" class="btn btn-primary btn-sm">Registrar Empresa</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Contenedor Principal -->
  <main class="main-wrapper">

    <!-- Mensaje Flash: Éxito -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success">
        <span class="alert-icon">✓</span>
        <div><?= esc(session()->getFlashdata('success')) ?></div>
      </div>
    <?php endif; ?>

    <!-- Mensaje Flash: Error General -->
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-error">
        <span class="alert-icon">⚠</span>
        <div><?= esc(session()->getFlashdata('error')) ?></div>
      </div>
    <?php endif; ?>

    <!-- Mensaje Flash: Errores de Validación -->
    <?php if (session()->getFlashdata('errors')): ?>
      <div class="alert alert-error">
        <span class="alert-icon">⚠</span>
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
