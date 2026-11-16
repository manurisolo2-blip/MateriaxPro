<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Confirmar Eliminación']) ?>

<div class="container-sm stack-md">
    <a href="<?= site_url('productos') ?>" class="text-sec">&larr; Conservar lote y volver al inventario</a>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Confirmar Eliminación de Lote</h2>
        </div>
        <div class="card-body stack-md">
            <p>¿Está seguro de que desea eliminar el siguiente lote de material de la plataforma?</p>

            <div class="card stack-sm">
                <div class="flex-between">
                    <div>
                        <span class="text-muted">LOTE #<?= esc($producto['id']) ?></span>
                        <h3><?= esc($producto['nombre']) ?></h3>
                    </div>
                    <span class="badge badge-polimero <?= badge_polimero_class($producto['tipo_polimero']) ?>"><?= esc($producto['tipo_polimero']) ?></span>
                </div>
                <div class="grid-3">
                    <div><span class="text-muted">Volumen:</span><br><strong><?= format_kg($producto['cantidad_kg']) ?></strong></div>
                    <div><span class="text-muted">Precio Unitario:</span><br><strong><?= format_precio($producto['precio_unitario']) ?> / kg</strong></div>
                    <div><span class="text-muted">Origen:</span><br><strong><?= esc($producto['ubicacion']) ?></strong></div>
                </div>
            </div>

            <div class="alert alert-error">
                <strong>Advertencia:</strong> Esta operación es irreversible. El registro será removido de forma permanente.
            </div>

            <form action="<?= site_url('productos/eliminar/' . $producto['id']) ?>" method="POST" class="flex-row">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger">Eliminar Lote Definitivamente</button>
                <a href="<?= site_url('productos/ver/' . $producto['id']) ?>" class="btn btn-secondary">Cancelar y Conservar Lote</a>
            </form>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
