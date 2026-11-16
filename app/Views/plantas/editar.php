<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Editar Planta Industrial | MateriaX']) ?>

<div class="container-md stack-md">
    <nav aria-label="Migas de pan" class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('plantas') ?>">Plantas Industriales</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page">Editar Planta</span>
    </nav>

    <div class="page-header">
        <h1 class="page-title">Editar Sede Fabril: <?= esc($planta['nombre_planta']) ?></h1>
        <p class="page-sub">Actualiza los datos técnicos, de báscula o de contacto para esta ubicación.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <?= view('plantas/_form', ['action' => site_url('plantas/actualizar/' . $planta['id']), 'btnText' => 'Actualizar Datos', 'planta' => $planta]) ?>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
