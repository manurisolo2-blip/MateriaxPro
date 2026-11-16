<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Registrar Nueva Planta Industrial | MateriaX']) ?>

<div class="container-md stack-md">
    <nav aria-label="Migas de pan" class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('plantas') ?>">Plantas Industriales</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page">Nueva Planta</span>
    </nav>

    <div class="page-header">
        <h1 class="page-title">Registrar Nueva Planta Industrial</h1>
        <p class="page-sub">Ingresa los datos del predio fabril o depósito para habilitar retiros logísticos.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <?= view('plantas/_form', ['action' => site_url('plantas/guardar'), 'btnText' => 'Registrar Planta']) ?>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
