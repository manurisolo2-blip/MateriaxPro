<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Mis Compras y Solicitudes Enviadas | MateriaX']) ?>

<div class="container-lg stack-md">
    <nav aria-label="Migas de pan" class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('panel') ?>">Mi Panel</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page">Mis Compras Enviadas</span>
    </nav>

    <div class="page-header flex-between">
        <div>
            <h1 class="page-title">Mis Compras y Solicitudes Enviadas</h1>
            <p class="page-sub">Seguimiento de tus propuestas comerciales sobre materiales de otros oferentes.</p>
        </div>
        <div class="flex-row">
            <a href="<?= site_url('solicitudes/recibidas') ?>" class="btn btn-secondary btn-sm">Recibidas (Ventas)</a>
            <a href="<?= site_url('solicitudes/enviadas') ?>" class="btn btn-primary btn-sm">Mis Compras Enviadas</a>
        </div>
    </div>

    <?= view('solicitudes/_tabla', ['solicitudes' => $solicitudes, 'modo' => 'enviadas']) ?>
</div>

<?= view('templates/footer') ?>
