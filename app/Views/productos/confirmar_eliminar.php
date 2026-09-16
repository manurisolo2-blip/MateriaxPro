<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Confirmar Eliminación']) ?>

<div style="max-width: 650px; margin: 2rem auto;">
  <div style="margin-bottom: 1.25rem;">
    <a href="<?= site_url('productos') ?>" style="color: var(--text-secondary); font-size: 0.9rem;">
      &larr; Cancelar y volver al inventario
    </a>
  </div>

  <div class="card" style="border-color: rgba(239, 68, 68, 0.4);">
    <div class="card-header" style="background-color: rgba(239, 68, 68, 0.12); border-bottom-color: rgba(239, 68, 68, 0.3);">
      <h2 class="card-title" style="color: #f87171; display: flex; align-items: center; gap: 0.6rem;">
        <span>⚠️</span> Confirmar Eliminación de Lote
      </h2>
    </div>

    <div class="card-body">
      <p style="font-size: 1.05rem; color: #ffffff; margin-bottom: 1.25rem;">
        ¿Está completamente seguro de que desea eliminar el siguiente lote de material de la plataforma?
      </p>

      <div style="background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
          <div>
            <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">LOTE #<?= esc($producto['id']) ?></span>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #ffffff; margin-top: 0.15rem;">
              <?= esc($producto['nombre']) ?>
            </h3>
          </div>
          <span class="badge badge-polimero"><?= esc($producto['tipo_polimero']) ?></span>
        </div>

        <div class="form-row" style="margin-top: 1rem; border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
          <div>
            <span style="font-size: 0.82rem; color: var(--text-muted);">Volumen:</span>
            <p style="font-weight: 700; color: #ffffff;"><?= number_format((float)$producto['cantidad_kg'], 0, ',', '.') ?> kg</p>
          </div>
          <div>
            <span style="font-size: 0.82rem; color: var(--text-muted);">Precio Unitario:</span>
            <p style="font-weight: 700; color: var(--color-success);">$<?= number_format((float)$producto['precio_unitario'], 2, ',', '.') ?> / kg</p>
          </div>
          <div>
            <span style="font-size: 0.82rem; color: var(--text-muted);">Planta de Origen:</span>
            <p style="font-weight: 600; color: #ffffff;"><?= esc($producto['ubicacion']) ?></p>
          </div>
        </div>
      </div>

      <div class="alert alert-error" style="margin-bottom: 1.75rem;">
        <span class="alert-icon">🛑</span>
        <div>
          <strong>Advertencia de Seguridad:</strong> Esta operación es irreversible. El registro será removido permanentemente de la base de datos y ya no estará disponible para cotizaciones ni trazabilidad en la red.
        </div>
      </div>

      <!-- Formulario nativo HTTP POST (100% PHP, 0% JavaScript) -->
      <form action="<?= site_url('productos/eliminar/' . $producto['id']) ?>" method="POST" style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <?= csrf_field() ?>

        <button type="submit" class="btn btn-danger" style="flex: 1; min-width: 200px;">
          🗑 Sí, Eliminar Permanentemente
        </button>

        <a href="<?= site_url('productos/ver/' . $producto['id']) ?>" class="btn btn-secondary" style="text-align: center;">
          Cancelar
        </a>
      </form>
    </div>
  </div>
</div>

<?= view('templates/footer') ?>
