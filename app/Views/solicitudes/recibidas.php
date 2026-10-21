<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Solicitudes Recibidas | MateriaX']) ?>

<!-- Migas de pan -->
<nav aria-label="Migas de pan" class="breadcrumb" style="max-width: 1120px; margin: 0 auto 1.25rem;">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('panel') ?>">Mi Panel</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page">Solicitudes de Compra</span>
</nav>

<div style="max-width: 1120px; margin: 0 auto 2.5rem;">

  <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <div>
      <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--color-ink); margin: 0 0 0.35rem 0;">
        Solicitudes de Compra y Acuerdos Comerciales
      </h1>
      <p style="color: var(--color-ink-2); font-size: 0.95rem; margin: 0;">
        Gestiona las propuestas comerciales y pedidos de retiro sobre tus lotes publicados.
      </p>
    </div>

    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
      <a href="<?= site_url('solicitudes/recibidas') ?>" class="btn btn-primary btn-sm">
        Recibidas (Ventas)
      </a>
      <a href="<?= site_url('solicitudes/enviadas') ?>" class="btn btn-secondary btn-sm">
        Mis Compras Enviadas
      </a>
    </div>
  </div>

  <!-- Estadísticas de solicitudes -->
  <div class="grid-3" style="margin-bottom: 1.75rem;">
    <div class="metric-card">
      <span class="metric-card-label">Total Recibidas</span>
      <div class="metric-card-value">
        <?= count($solicitudes) ?>
      </div>
      <span class="metric-card-sub">Propuestas comerciales recibidas</span>
    </div>

    <div class="metric-card">
      <span class="metric-card-label">Pendientes de Evaluación</span>
      <div class="metric-card-value" style="color: var(--color-accent);">
        <?= $pendientes ?>
      </div>
      <span class="metric-card-sub">Requieren tu aprobación o rechazo</span>
    </div>

    <div class="metric-card">
      <span class="metric-card-label">Acuerdos Aprobados</span>
      <div class="metric-card-value" style="color: var(--color-success-text);">
        <?= $aprobadas ?>
      </div>
      <span class="metric-card-sub">Lotes reservados en coordinación</span>
    </div>
  </div>

  <!-- Tabla de solicitudes recibidas -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Bandeja de Solicitudes Recibidas</h3>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th scope="col">Código Operación</th>
            <th scope="col">Lote Solicitado</th>
            <th scope="col">Empresa Compradora</th>
            <th scope="col">Cantidad</th>
            <th scope="col">Total Propuesto</th>
            <th scope="col">Modalidad</th>
            <th scope="col">Estado</th>
            <th scope="col">Fecha</th>
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
                  <strong><?= esc($s['comprador_nombre']) ?></strong>
                  <div style="font-size: 0.78rem; color: var(--color-ink-2);">
                    CUIT: <?= esc($s['comprador_cuit'] ?? 'N/D') ?>
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
                    <a href="<?= site_url('solicitudes/ver/' . $s['id']) ?>" class="btn btn-secondary btn-sm" title="Ver ficha y condiciones">
                      Ver
                    </a>

                    <?php if ($s['estado'] === 'solicitada'): ?>
                      <form action="<?= site_url('solicitudes/responder/' . $s['id']) ?>" method="POST" style="margin: 0; display: inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion" value="aceptar">
                        <button type="submit" class="btn btn-primary btn-sm" title="Aceptar oferta y reservar lote">
                          Aceptar
                        </button>
                      </form>

                      <form action="<?= site_url('solicitudes/responder/' . $s['id']) ?>" method="POST" style="margin: 0; display: inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion" value="rechazar">
                        <button type="submit" class="btn btn-danger btn-sm" title="Rechazar propuesta">
                          Rechazar
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
                  No has recibido solicitudes de compra todavía
                </p>
                <p style="font-size: 0.92rem; margin-bottom: 1.25rem;">
                  Cuando otra industria de la red se interese en alguno de tus lotes publicados, su propuesta aparecerá aquí para que puedas evaluarla y acordar el retiro.
                </p>
                <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary">
                  Publicar Nuevo Lote
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
