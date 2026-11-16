<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Iniciar Solicitud de Compra | MateriaX']) ?>

<div class="container-md stack-md">
    <nav aria-label="Migas de pan" class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('productos') ?>">Mercado</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('productos/ver/' . $producto['id']) ?>">Lote #<?= esc($producto['id']) ?></a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page">Solicitud de Compra</span>
    </nav>

    <div class="page-header">
        <h1 class="page-title">Iniciar Solicitud de Compra / Retiro</h1>
        <p class="page-sub">Emite una propuesta formal al titular del material para acordar volumen, precio y logística.</p>
    </div>

    <div class="card flex-between">
        <div class="stack-sm">
            <span class="badge badge-polimero <?= badge_polimero_class($producto['tipo_polimero']) ?>"><?= esc($producto['tipo_polimero']) ?></span>
            <h2><?= esc($producto['nombre']) ?></h2>
            <p class="text-sec">
                Empresa titular: <strong><?= esc($producto['empresa_nombre']) ?></strong> · Radicación: <?= esc($producto['ubicacion']) ?>
                <?php if (!empty($producto['nombre_planta'])): ?>
                    <br>Planta de Carga: <strong><?= esc($producto['nombre_planta']) ?></strong> (<?= !empty($producto['planta_posee_bascula']) ? 'Báscula Sí' : 'Sin Báscula' ?>)
                <?php endif; ?>
            </p>
        </div>
        <div class="stack-sm">
            <span class="text-muted">Disponible / Referencia</span>
            <strong class="text-mono"><?= format_kg($producto['cantidad_kg']) ?> · <?= format_precio($producto['precio_unitario']) ?>/kg</strong>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="<?= site_url('solicitudes/guardar') ?>" method="POST" class="stack-sm">
                <?= csrf_field() ?>
                <input type="hidden" name="producto_id" value="<?= esc($producto['id']) ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="cantidad_kg" class="form-label">Volumen Solicitado (kg) <span class="required-indicator" aria-hidden="true">*</span></label>
                        <input type="number" step="0.01" inputmode="decimal" name="cantidad_kg" id="cantidad_kg" class="form-control" value="<?= esc(old('cantidad_kg', $producto['cantidad_kg'])) ?>" required max="<?= esc($producto['cantidad_kg']) ?>">
                        <span class="form-hint">Máximo disponible publicado: <?= format_kg($producto['cantidad_kg']) ?>.</span>
                    </div>
                    <div class="form-group">
                        <label for="precio_unitario_pactado" class="form-label">Precio Ofertado ($ ARS/kg) <span class="required-indicator" aria-hidden="true">*</span></label>
                        <input type="number" step="0.01" inputmode="decimal" name="precio_unitario_pactado" id="precio_unitario_pactado" class="form-control" value="<?= esc(old('precio_unitario_pactado', $producto['precio_unitario'])) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="modalidad_retiro" class="form-label">Modalidad Logística de Retiro <span class="required-indicator" aria-hidden="true">*</span></label>
                        <select name="modalidad_retiro" id="modalidad_retiro" class="form-select" required>
                            <option value="retiro_comprador_planta"<?= (old('modalidad_retiro') === 'retiro_comprador_planta') ? ' selected' : '' ?>>Retiro por Comprador en Planta (Camión / Flete Propio)</option>
                            <option value="entrega_vendedor"<?= (old('modalidad_retiro') === 'entrega_vendedor') ? ' selected' : '' ?>>Entrega a Cargo del Vendedor</option>
                            <option value="flete_convenido"<?= (old('modalidad_retiro') === 'flete_convenido') ? ' selected' : '' ?>>Flete de Terceros Convenido entre Partes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="costo_flete" class="form-label">Costo de Flete Estimado ($ ARS)</label>
                        <input type="number" step="0.01" inputmode="decimal" name="costo_flete" id="costo_flete" class="form-control" value="<?= esc(old('costo_flete', '0.00')) ?>" placeholder="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones" class="form-label">Condiciones Particulares o Mensaje al Vendedor</label>
                    <textarea name="observaciones" id="observaciones" class="form-textarea" rows="4" placeholder="Ej: Retiro en chasis de 14 Ton, requerimos remito de pesaje en balanza fiscal."><?= esc(old('observaciones') ?? '') ?></textarea>
                </div>

                <div class="flex-row">
                    <button type="submit" class="btn btn-primary">Emitir Solicitud Formal</button>
                    <a href="<?= site_url('productos/ver/' . $producto['id']) ?>" class="btn btn-secondary">Cancelar y Volver al Lote</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
