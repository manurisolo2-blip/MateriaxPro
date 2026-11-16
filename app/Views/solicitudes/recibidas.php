<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Solicitudes Recibidas | MateriaX']) ?>

<div class="container-lg stack-md">
    <nav aria-label="Migas de pan" class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('panel') ?>">Mi Panel</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page">Solicitudes de Compra</span>
    </nav>

    <div class="page-header flex-between">
        <div>
            <h1 class="page-title">Solicitudes de Compra y Acuerdos Comerciales</h1>
            <p class="page-sub">Gestiona las propuestas comerciales y pedidos de retiro sobre tus lotes publicados.</p>
        </div>
        <div class="flex-row">
            <a href="<?= site_url('solicitudes/recibidas') ?>" class="btn btn-primary btn-sm">Recibidas (Ventas)</a>
            <a href="<?= site_url('solicitudes/enviadas') ?>" class="btn btn-secondary btn-sm">Mis Compras Enviadas</a>
        </div>
    </div>

    <div class="grid-3">
        <div class="metric-card">
            <span class="metric-card-label">Total Recibidas</span>
            <div class="metric-card-value"><?= count($solicitudes) ?></div>
            <span class="metric-card-sub">Propuestas comerciales recibidas</span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Pendientes de Evaluación</span>
            <div class="metric-card-value"><?= $pendientes ?></div>
            <span class="metric-card-sub">Requieren tu aprobación o rechazo</span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Acuerdos Aprobados</span>
            <div class="metric-card-value"><?= $aprobadas ?></div>
            <span class="metric-card-sub">Lotes reservados en coordinación</span>
        </div>
    </div>

    <?= view('solicitudes/_tabla', ['solicitudes' => $solicitudes, 'modo' => 'recibidas']) ?>
</div>

<?= view('templates/footer') ?>
