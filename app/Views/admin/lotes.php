<?= view('templates/header', ['pageTitle' => 'Supervisión de Lotes | MateriaX Admin']) ?>

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 2.2rem; font-weight: 700; color: var(--text-primary); margin-top: 0.25rem;">
      Supervisión Global de Lotes
    </h1>
    <p style="color: var(--text-secondary); font-size: 0.95rem;">
      Auditoría integral de publicaciones, excedentes y materiales industriales publicados por las empresas.
    </p>
  </div>

  <div style="display: flex; gap: 0.75rem; align-items: center;">
    <a href="<?= site_url('admin') ?>" class="btn btn-secondary">
      Volver al Panel
    </a>
  </div>
</div>

<div class="card">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      <h2 class="card-title" style="margin: 0;">Todos los Lotes Publicados en la Red</h2>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0.25rem 0 0 0;">
        Listado consolidado con trazabilidad de la empresa oferente.
      </p>
    </div>
    <span class="badge" style="background: rgba(15, 118, 110, 0.1); color: var(--brand-teal); border: 1px solid rgba(15, 118, 110, 0.25);">
      <?= count($lotes) ?> publicaciones
    </span>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Lote / Denominación</th>
          <th>Empresa Oferente</th>
          <th>Polímero</th>
          <th>Volumen</th>
          <th>Precio Unit.</th>
          <th>Ubicación</th>
          <th>Estado</th>
          <th style="text-align: right;">Moderación</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($lotes)): ?>
          <?php foreach ($lotes as $lote): ?>
            <tr>
              <td style="color: var(--text-muted); font-family: monospace; font-size: 0.85rem;">
                #<?= esc($lote['id']) ?>
              </td>
              <td>
                <strong style="color: var(--text-primary); font-size: 0.95rem;">
                  <?= esc($lote['nombre']) ?>
                </strong>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                  Publicado: <?= date('d/m/Y', strtotime($lote['created_at'])) ?>
                </div>
              </td>
              <td>
                <a href="<?= site_url('admin/empresa/' . $lote['user_id']) ?>" style="color: var(--color-accent); text-decoration: none; font-weight: 600;">
                  <?= esc($lote['empresa_nombre'] ?? 'Empresa #' . $lote['user_id']) ?>
                </a>
                <div style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">
                  CUIT: <?= esc($lote['empresa_cuit'] ?? '-') ?>
                </div>
              </td>
              <td>
                <span class="badge badge-polimero <?= badge_polimero_class($lote['tipo_polimero']) ?>">
                  <?= esc($lote['tipo_polimero']) ?>
                </span>
              </td>
              <td>
                <strong><?= number_format($lote['cantidad_kg'], 2, ',', '.') ?></strong> kg
              </td>
              <td>
                $<?= number_format($lote['precio_unitario'], 2, ',', '.') ?> /kg
              </td>
              <td style="font-size: 0.85rem; color: var(--text-secondary);">
                <?= esc($lote['ubicacion']) ?>
              </td>
              <td>
                <?php if ($lote['estado'] === 'Disponible'): ?>
                  <span class="badge badge-success">● Disponible</span>
                <?php elseif ($lote['estado'] === 'Reservado'): ?>
                  <span class="badge badge-warning">● Reservado</span>
                <?php else: ?>
                  <span class="badge badge-vendido">● Vendido</span>
                <?php endif; ?>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                  <a href="<?= site_url('productos/ver/' . $lote['id']) ?>" class="btn btn-secondary btn-sm" target="_blank">
                    Ver
                  </a>

                  <!-- Formulario POST puro de baja administrativa (Cero JavaScript) -->
                  <form action="<?= site_url('admin/lotes/eliminar/' . $lote['id']) ?>" method="POST" style="margin: 0; display: inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-secondary btn-sm" style="color: #dc2626;" title="Dar de baja lote por moderación administrativa">
                      Dar de baja
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-secondary);">
              <p style="font-size: 1.05rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.35rem;">
                No hay lotes de polímeros publicados en la plataforma actualmente.
              </p>
              <div style="font-size: 0.85rem; color: var(--text-muted);">
                A medida que las industrias homologadas carguen sus excedentes, se listarán aquí para supervisión y trazabilidad.
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= view('templates/footer') ?>
