<?= view('templates/header', ['pageTitle' => 'Publicar Excedente Industrial | MateriaX']) ?>

<div class="container-md stack-md">
    <nav aria-label="Migas de pan" class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('productos') ?>">Mercado</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page">Publicar Lote</span>
    </nav>

    <div class="page-header">
        <h1 class="page-title">Publicar Nuevo Lote de Material</h1>
        <p class="page-sub">Ingresa los datos técnicos del excedente polimérico para ofrecerlo a industrias de la red.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <?= view('productos/_form', [
                'action'  => site_url('productos/guardar'),
                'btnText' => 'Publicar Lote',
                'plantas' => $plantas ?? [],
            ]) ?>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
