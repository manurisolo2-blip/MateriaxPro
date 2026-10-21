<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Iniciar Solicitud de Compra | MateriaX']) ?>

<!-- Migas de pan -->
<nav aria-label="Migas de pan" class="breadcrumb" style="max-width: 800px; margin: 0 auto 1.25rem;">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('productos') ?>">Mercado</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('productos/ver/' . $producto['id']) ?>">Lote #<?= esc($producto['id']) ?></a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page">Solicitud de Compra</span>
</nav>

<div style="max-width: 800px; margin: 0 auto 2.5rem;">

  <div style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--color-ink); margin: 0 0 0.35rem 0;">
      Iniciar Solicitud de Compra / Retiro
    </h1>
    <p style="color: var(--color-ink-2); font-size: 0.95rem; margin: 0;">
      Emite una propuesta formal al titular del material para acordar el volumen, precio y logística de retiro.
    </p>
  </div>

  <!-- Resumen del lote solicitado -->
  <div class="card" style="margin-bottom: 1.5rem; background-color: var(--color-paper-2);">
    <div class="card-body">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
        <div>
          <span class="badge badge-polimero <?= badge_polimero_class($producto['tipo_polimero']) ?>"><?= esc($producto['tipo_polimero']) ?></span>
          <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-ink); margin: 0.35rem 0 0.25rem 0;">
            <?= esc($producto['nombre']) ?>
          </h2>
          <div style="font-size: 0.88rem; color: var(--color-ink-2);">
            Empresa titular: <strong style="color: var(--color-ink);"><?= esc($producto['empresa_nombre']) ?></strong> &nbsp;|&nbsp;
            Radicación: <?= esc($producto['ubicacion']) ?>
          </div>
          <?php if (!empty($producto['nombre_planta'])): ?>
            <div style="font-size: 0.85rem; color: var(--color-ink-2); margin-top: 0.25rem;">
              Planta de Carga: <strong><?= esc($producto['nombre_planta']) ?></strong> (<?= !empty($producto['planta_posee_bascula']) ? 'Báscula Sí' : 'Sin Báscula' ?>)
            </div>
          <?php endif; ?>
        </div>

        <div style="text-align: right;">
          <div style="font-size: 0.8rem; color: var(--color-ink-2);">Disponible / Precio Referencia</div>
          <div style="font-size: 1.15rem; font-weight: 700; color: var(--color-ink);">
            <?= format_kg($producto['cantidad_kg']) ?> &nbsp;&bull;&nbsp; <?= format_precio($producto['precio_unitario']) ?>/kg
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Formulario de solicitud -->
  <div class="card">
    <div class="card-body">
      <form action="<?= site_url('solicitudes/guardar') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="producto_id" value="<?= esc($producto['id']) ?>">

        <div class="form-row">
          <div class="form-group">
            <label for="cantidad_kg" class="form-label">
              Volumen Solicitado (en Kilogramos) <span class="required-indicator" aria-hidden="true">*</span>
            </label>
            <input type="number" step="0.01" inputmode="decimal" name="cantidad_kg" id="cantidad_kg" class="form-control" value="<?= old('cantidad_kg', $producto['cantidad_kg']) ?>" required max="<?= esc($producto['cantidad_kg']) ?>">
            <span class="form-hint">Máximo disponible publicado: <?= format_kg($producto['cantidad_kg']) ?>.</span>
          </div>

          <div class="form-group">
            <label for="precio_unitario_pactado" class="form-label">
              Precio Unitario Ofertado ($ ARS / kg) <span class="required-indicator" aria-hidden="true">*</span>
            </label>
            <input type="number" step="0.01" inputmode="decimal" name="precio_unitario_pactado" id="precio_unitario_pactado" class="form-control" value="<?= old('precio_unitario_pactado', $producto['precio_unitario']) ?>" required>
            <span class="form-hint">Precio neto por kilogramo de material propuesto.</span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="modalidad_retiro" class="form-label">
              Modalidad Logística de Retiro <span class="required-indicator" aria-hidden="true">*</span>
            </label>
            <select name="modalidad_retiro" id="modalidad_retiro" class="form-select" required>
              <option value="retiro_comprador_planta" <?= (old('modalidad_retiro') === 'retiro_comprador_planta') ? 'selected' : '' ?>>
                Retiro por Comprador en Planta (Camión / Flete Propio)
              </option>
              <option value="entrega_vendedor" <?= (old('modalidad_retiro') === 'entrega_vendedor') ? 'selected' : '' ?>>
                Entrega a Cargo del Vendedor
              </option>
              <option value="flete_convenido" <?= (old('modalidad_retiro') === 'flete_convenido') ? 'selected' : '' ?>>
                Flete de Terceros Convenido entre Partes
              </option>
            </select>
            <span class="form-hint">Condición de transporte para el retiro del lote en depósito.</span>
          </div>

          <div class="form-group">
            <label for="costo_flete" class="form-label">
              Costo de Flete Estimado ($ ARS)
            </label>
            <input type="number" step="0.01" inputmode="decimal" name="costo_flete" id="costo_flete" class="form-control" value="<?= old('costo_flete', '0.00') ?>" placeholder="0.00">
            <span class="form-hint">Indicar 0 si corre por cuenta del transportista o a liquidar luego.</span>
          </div>
        </div>

        <div class="form-group">
          <label for="observaciones" class="form-label">Condiciones Particulares o Mensaje al Vendedor</label>
          <textarea name="observaciones" id="observaciones" class="form-textarea" rows="4" placeholder="Ej: Consultamos si podemos retirar en chasis de 14 Ton el próximo jueves. Requerimos remito de pesaje en balanza fiscal."><?= old('observaciones') ?></textarea>
          <span class="form-hint">Indica turnos de retiro deseados, especificaciones de carga, tipo de vehículo o condiciones de pago.</span>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
          <button type="submit" class="btn btn-primary">
            Emitir Solicitud Formal
          </button>
          <a href="<?= site_url('productos/ver/' . $producto['id']) ?>" class="btn btn-secondary">
            Cancelar y Volver al Lote
          </a>
        </div>
      </form>
    </div>
  </div>

</div>

<?= view('templates/footer') ?>
