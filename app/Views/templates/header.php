<?php
$session  = session();
$isLogged = (bool) ($session->get('isLoggedIn') ?? $session->get('logged_in'));
$rol      = (string) ($session->get('rol') ?? 'usuario');
$noLeidos = $isLogged ? contar_mensajes_no_leidos() : 0;
$success  = $session->getFlashdata('success');
$error    = $session->getFlashdata('error');
$errors   = $session->getFlashdata('errors');
$docTitle = $pageTitle ?? $title ?? 'MateriaX — Mercado de Polímeros Industriales';
$path     = trim((string) uri_string(), '/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($docTitle) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>?v=2" sizes="48x48 32x32 16x16">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/logos/favicon-32x32.png') ?>?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/logos/favicon-16x16.png') ?>?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/logos/apple-touch-icon.png') ?>?v=2">
    <link rel="stylesheet" href="<?= base_url('css/tokens.css') ?>?v=2">
    <link rel="stylesheet" href="<?= base_url('css/materiax.css') ?>?v=2">
</head>
<body data-base-url="<?= site_url('/') ?>"<?= $isLogged ? ' data-logged="1"' : '' ?>>
<a href="#main-content" class="skip-link">Saltar al contenido</a>
<header class="site-header">
    <div class="nav-container">
        <a href="<?= site_url('/') ?>" class="brand-link">Materia<span class="brand-accent">X</span></a>
        <nav aria-label="Navegación principal">
            <ul class="nav-menu">
                <?php if ($isLogged): ?>
                    <?php if ($rol === 'admin'): ?>
                        <li class="nav-item"><a href="<?= site_url('admin') ?>" <?= nav_attr($path === 'admin') ?>>Admin</a></li>
                        <li class="nav-item"><a href="<?= site_url('admin/lotes') ?>" <?= nav_attr(str_starts_with($path, 'admin/lotes')) ?>>Lotes</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a href="<?= site_url('productos') ?>" <?= nav_attr($path === 'productos') ?>>Mercado</a></li>
                    <li class="nav-item"><a href="<?= site_url('panel') ?>" <?= nav_attr(str_starts_with($path, 'panel')) ?>>Mi Panel</a></li>
                    <li class="nav-item"><a href="<?= site_url('productos/crear') ?>" <?= nav_attr(str_starts_with($path, 'productos/crear')) ?>>Publicar</a></li>
                    <li class="nav-item"><a href="<?= site_url('plantas') ?>" <?= nav_attr(str_starts_with($path, 'plantas')) ?>>Plantas</a></li>
                    <li class="nav-item"><a href="<?= site_url('solicitudes') ?>" <?= nav_attr(str_starts_with($path, 'solicitudes')) ?>>Solicitudes</a></li>
                    <li class="nav-item"><a href="<?= site_url('certificados') ?>" <?= nav_attr(str_starts_with($path, 'certificados')) ?>>Certificados</a></li>
                    <li class="nav-item"><a href="<?= site_url('mensajes') ?>" <?= nav_attr(str_starts_with($path, 'mensajes')) ?>>Mensajes <span class="badge-mensajes-live"<?= $noLeidos > 0 ? '' : ' style="display:none"' ?>><?= $noLeidos ?></span></a></li>
                    <li class="nav-item"><a href="<?= site_url('perfil') ?>" <?= nav_attr(str_starts_with($path, 'perfil')) ?>>Perfil</a></li>
                <?php else: ?>
                    <li class="nav-item"><a href="<?= site_url('productos') ?>" <?= nav_attr(str_starts_with($path, 'productos')) ?>>Mercado</a></li>
                    <li class="nav-item"><a href="<?= site_url('/#feed') ?>">Lotes</a></li>
                    <li class="nav-item"><a href="<?= site_url('/#contacto') ?>">Contacto</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="nav-auth">
            <?php if ($isLogged): ?>
                <a href="<?= site_url('logout') ?>" class="btn btn-secondary btn-sm">Salir</a>
            <?php else: ?>
                <a href="<?= site_url('login') ?>" class="btn btn-secondary btn-sm">Ingresar</a>
                <a href="<?= site_url('register') ?>" class="btn btn-primary btn-sm">Registrar Empresa</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main id="main-content" class="main-wrapper">
    <?php if ($success): ?>
        <div class="container-lg"><div class="alert alert-success"><?= esc($success) ?></div></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="container-lg"><div class="alert alert-error"><?= esc($error) ?></div></div>
    <?php endif; ?>
    <?php if (!empty($errors) && is_array($errors)): ?>
        <div class="container-lg">
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?></ul>
            </div>
        </div>
    <?php endif; ?>
