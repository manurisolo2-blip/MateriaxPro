<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Mis Compras y Solicitudes Enviadas | MateriaX']) ?>

<!-- Migas de pan -->
<nav aria-label="Migas de pan" class="breadcrumb" style="max-width: 1120px; margin: 0 auto 1.25rem;">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('panel') ?>">Mi Panel</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page">Mis Compras Enviadas</span>
</nav>

<div style="max-width: 1120px; margin: 0 auto 2.5rem;">

  <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <div>
      <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--color-ink); margin: 0 0 0.35rem 0;">
        Mis Compras y Solicitudes Enviadas
      </h1>
      <p style="color: var(--color-ink-2); font-size: 0.95rem; margin: 0;">
        Seguimiento en tiempo real de tus propuestas comerciales sobre materiales de otros oferentes.
      </p>
    </div>

    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
      <a href="<?= site_url('solicitudes/recibidas') ?>" class="btn btn-secondary btn-sm">
        Recibidas (Ventas)
      </a>
      <a href="<?= site_url('solicitudes/enviadas') ?>" class="btn btn-primary btn-sm">
        Mis Compras Enviadas
      </a>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Historial de Solicitudes de Adquisición</h3>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th scope="col">Código Operación</th>
            <th scope="col">Lote Solicitado</th>
            <th scope="col">Empresa Oferente</th>
            <th scope="col">Cantidad</th>
            <th scope="col">Total Propuesto</th>
            <th scope="col">Modalidad</th>
            <th scope="col">Estado</th>
            <th scope="col">Fecha Envío</th>
            <th scope="col" style="text-align: right;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($solicitudes) && count($solicitudes) > 0): ?>
            <?php foreach ($solicitudes as $s): ?>
              <tr>
                <td>
                  <a href="<?= site_url('solicitudes/ver/' . $s['id']) ?>" style="font-weight: 700; color: var(--color-ink);">
                    <?= esc($s['codigo_operacion']) ?>
                  </a>
                </td>
                <td>
                  <a href="<?= site_url('productos/ver/' . $s['producto_id']) ?>" style="color: inherit; text-decoration: underline;">
                    <?= esc($s['producto_nombre']) ?>
                  </a>
                  <div style="font-size: 0.78rem; color: var(--color-ink-2);">
                    <?= esc($s['tipo_polimero']) ?>
                  </div>
                </td>
                <td>
                  <strong><?= esc($s['vendedor_nombre']) ?></strong>
                  <div style="font-size: 0.78rem; color: var(--color-ink-2);">
                    CUIT: <?= esc($s['vendedor_cuit'] ?? 'N/D') ?>
                  </div>
                </td>
                <td><strong><?= format_kg($s['cantidad_kg']) ?></strong></td>
                <td>
                  <strong><?= format_precio($s['monto_total']) ?></strong>
                  <div style="font-size: 0.75rem; color: var(--color-ink-2);">
                    <?= format_precio($s['precio_unitario_pactado']) ?>/kg
                  </div>
                </td>
                <td style="font-size: 0.82rem; color: var(--color-ink-2);">
                  <?= label_modalidad_retiro($s['modalidad_retiro']) ?>
                </td>
                <td>
                  <span class="badge <?= badge_transaccion_class($s['estado']) ?>">
                    <?= label_transaccion_estado($s['estado']) ?>
                  </span>
                </td>
                <td style="font-size: 0.82rem; color: var(--color-ink-2);">
                  <?= date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) ?>
                </td>
                <td style="text-align: right; white-space: nowrap;">
                  <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                    <a href="<?= site_url('solicitudes/ver/' . $s['id']) ?>" class="btn btn-secondary btn-sm" title="Ver detalles y logística">
                      Ver
                    </a>

                    <?php if ($s['estado'] === 'solicitada'): ?>
                      <form action="<?= site_url('solicitudes/cancelar/' . $s['id']) ?>" method="POST" style="margin: 0; display: inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm" title="Cancelar esta solicitud">
                          Cancelar
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-ink-2);">
                <p style="font-size: 1.1rem; color: var(--color-ink); font-weight: 600; margin-bottom: 0.5rem;">
                  Aún no has enviado ninguna solicitud de compra
                </p>
                <p style="font-size: 0.92rem; margin-bottom: 1.25rem;">
                  Explora el catálogo de polímeros industriales disponibles y solicita lotes para tus procesos productivos.
                </p>
                <a href="<?= site_url('productos') ?>" class="btn btn-primary">
                  Explorar Mercado de Materiales
                </a>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?= view('templates/footer') ?>
