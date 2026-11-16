<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Editar Lote | MateriaX']) ?>

<div class="container-md stack-md">
    <nav aria-label="Migas de pan" class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('productos') ?>">Mercado</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('productos/ver/' . $producto['id']) ?>"><?= esc($producto['nombre']) ?></a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page">Editar</span>
    </nav>

    <div class="page-header">
        <h1 class="page-title">Editar Publicación de Lote</h1>
        <p class="page-sub">Actualiza los datos técnicos, volumen disponible o condiciones de retiro del material.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <?= view('productos/_form', [
                'action'   => site_url('productos/actualizar/' . $producto['id']),
                'btnText'  => 'Guardar Cambios',
                'producto' => $producto,
                'plantas'  => $plantas ?? [],
            ]) ?>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
