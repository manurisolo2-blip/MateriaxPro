<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Panel de Empresa | MateriaX']) ?>

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap;">
      <h1 style="font-size: 2.2rem; font-weight: 700; color: var(--color-ink); margin: 0;">
        <?= esc($user['nombre']) ?>
      </h1>
      <span class="badge badge-success">
        Cuenta Habilitada
      </span>
    </div>
    <p style="color: var(--color-ink-2); font-size: 0.95rem; margin-top: 0.25rem;">
      <strong>CUIT:</strong> <?= esc($user['cuit'] ?? 'N/D') ?> &nbsp;|&nbsp; 
      <strong>Rubro:</strong> <?= esc($user['rubro'] ?? 'Industrial') ?> &nbsp;|&nbsp; 
      <strong>Radicación:</strong> <?= esc($user['ciudad'] ?? 'Córdoba') ?>, <?= esc($user['provincia'] ?? 'Argentina') ?>
    </p>
  </div>

  <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
    <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary">
      Publicar Lote
    </a>
    <a href="<?= site_url('solicitudes/recibidas') ?>" class="btn btn-secondary">
      Solicitudes <?= !empty($solicitudesPendientes) ? "({$solicitudesPendientes} pendientes)" : '' ?>
    </a>
    <a href="<?= site_url('plantas') ?>" class="btn btn-secondary">
      Mis Plantas (<?= $totalPlantas ?? 0 ?>)
    </a>
    <a href="<?= site_url('certificados') ?>" class="btn btn-secondary">
      Certificados Ambientales
    </a>
    <a href="<?= site_url('productos') ?>" class="btn btn-secondary">
      Mercado
    </a>
    <a href="<?= site_url('perfil') ?>" class="btn btn-secondary">
      Perfil
    </a>
  </div>
</div>

<?php if (!empty($solicitudesPendientes)): ?>
  <div class="card" style="margin-bottom: 1.5rem; background-color: var(--color-warning-bg); border-color: var(--color-warning-border); padding: 1rem 1.25rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
      <div>
        <strong style="color: var(--color-warning-text); font-size: 1rem;">
          Tienes <?= $solicitudesPendientes ?> solicitud(es) de compra pendiente(s) de evaluación.
        </strong>
        <div style="font-size: 0.88rem; color: var(--color-ink-2); margin-top: 0.2rem;">
          Compradores interesados en tus lotes esperan tu confirmación para coordinar el retiro.
        </div>
      </div>
      <a href="<?= site_url('solicitudes/recibidas') ?>" class="btn btn-primary btn-sm">
        Revisar Solicitudes Recibidas &rarr;
      </a>
    </div>
  </div>
<?php endif; ?>

<!-- Estadísticas -->
<div class="grid-4" style="margin-bottom: 2rem;">
  <div class="metric-card">
    <span class="metric-card-label">Mis Lotes Publicados</span>
    <div class="metric-card-value">
      <?= $totalLotes ?>
    </div>
    <span class="metric-card-sub"><?= $lotesDisponibles ?> disponibles en la red</span>
  </div>

  <div class="metric-card">
    <span class="metric-card-label">Kilos en Oferta</span>
    <div class="metric-card-value" style="color: var(--color-accent);">
      <?= number_format($totalKilos, 0, ',', '.') ?> <span style="font-size: 1rem; font-weight: 600; color: var(--color-ink-2);">kg</span>
    </div>
    <span class="metric-card-sub">Materia prima circular</span>
  </div>

  <div class="metric-card">
    <span class="metric-card-label">Valor en Oferta (Est.)</span>
    <div class="metric-card-value" style="color: var(--color-success-text);">
      $<?= number_format($valorEstimado, 0, ',', '.') ?>
    </div>
    <span class="metric-card-sub">Valorización total de inventario</span>
  </div>

  <div class="metric-card">
    <span class="metric-card-label">Sedes Industriales</span>
    <div class="metric-card-value" style="color: var(--color-ink); font-size: 1.6rem;">
      <?= $totalPlantas ?? 0 ?> Activa(s)
    </div>
    <span class="metric-card-sub"><a href="<?= site_url('plantas') ?>" style="color: inherit; text-decoration: underline;">Gestionar sedes</a></span>
  </div>
</div>

<!-- Mis lotes -->
<div class="card" style="margin-bottom: 2.5rem;">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
    <div>
      <h3 class="card-title" style="margin: 0;">Mis Publicaciones en MateriaX</h3>
      <p style="color: var(--color-ink-2); font-size: 0.85rem; margin-top: 0.25rem; margin-bottom: 0;">
        Lotes registrados bajo la titularidad exclusiva de tu empresa. Puedes editarlos o darlos de baja cuando lo desees.
      </p>
    </div>
    <div>
      <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary btn-sm">
        Publicar Lote
      </a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th scope="col">Lote / Material</th>
          <th scope="col">Polímero</th>
          <th scope="col">Cantidad</th>
          <th scope="col">Precio / Kg</th>
          <th scope="col">Ubicación</th>
          <th scope="col">Estado</th>
          <th scope="col">Fecha</th>
          <th scope="col" style="text-align: right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($misProductos) && count($misProductos) > 0): ?>
          <?php foreach ($misProductos as $p): ?>
            <tr>
              <td>
                <a href="<?= site_url('productos/ver/' . $p['id']) ?>" style="font-weight: 700; color: var(--color-ink);">
                  <?= esc($p['nombre']) ?>
                </a>
                <div style="font-size: 0.78rem; color: var(--color-ink-2);">
                  ID #<?= esc($p['id']) ?>
                </div>
              </td>
              <td>
                <span class="badge badge-polimero <?= badge_polimero_class($p['tipo_polimero']) ?>"><?= esc($p['tipo_polimero']) ?></span>
              </td>
              <td><strong><?= format_kg($p['cantidad_kg']) ?></strong></td>
              <td><?= format_precio($p['precio_unitario']) ?></td>
              <td><?= esc($p['ubicacion']) ?></td>
              <td>
                <span class="badge <?= badge_estado_class($p['estado']) ?>"><?= esc($p['estado']) ?></span>
              </td>
              <td style="color: var(--color-ink-2); font-size: 0.85rem;">
                <?= date('d/m/Y', strtotime($p['created_at'])) ?>
              </td>
              <td style="text-align: right; white-space: nowrap;">
                <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                  <a href="<?= site_url('productos/ver/' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Ver ficha pública del lote">
                    Ver
                  </a>
                  <a href="<?= site_url('productos/editar/' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Editar lote propio">
                    Editar
                  </a>
                  <a href="<?= site_url('productos/confirmar-eliminar/' . $p['id']) ?>" class="btn btn-danger btn-sm" title="Eliminar este lote">
                    Eliminar
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-ink-2);">
              <p style="font-size: 1.1rem; color: var(--color-ink); font-weight: 600; margin-bottom: 0.5rem;">
                Aún no has publicado ningún lote de excedente industrial
              </p>
              <p style="font-size: 0.92rem; margin-bottom: 1.25rem;">
                Comienza a circular tus mermas, descartes o granzas plásticas para conectar con compradores industriales en toda la región.
              </p>
              <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary">
                Publicar Primer Lote de Material
              </a>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Lotes del mercado -->
<?php if (!empty($lotesMercado) && count($lotesMercado) > 0): ?>
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
    <div>
      <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-ink); margin: 0;">
        Oportunidades Recientes en la Red
      </h3>
      <p style="color: var(--color-ink-2); font-size: 0.88rem; margin: 0.2rem 0 0 0;">
        Materiales industriales publicados por otras empresas registradas en MateriaX.
      </p>
    </div>
    <div>
      <a href="<?= site_url('productos') ?>" class="btn btn-secondary btn-sm">
        Ver Todo el Inventario &rarr;
      </a>
    </div>
  </div>

  <div class="grid-2" style="margin-bottom: 2rem;">
    <?php foreach ($lotesMercado as $lm): ?>
      <div class="card" style="margin-bottom: 0; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
            <span class="badge badge-polimero <?= badge_polimero_class($lm['tipo_polimero']) ?>"><?= esc($lm['tipo_polimero']) ?></span>
            <span class="badge badge-success">Disponible</span>
          </div>
          <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--color-ink); margin: 0.25rem 0 0.5rem 0;">
            <a href="<?= site_url('productos/ver/' . $lm['id']) ?>" style="text-decoration: none; color: var(--color-ink);">
              <?= esc($lm['nombre']) ?>
            </a>
          </h4>
          <p style="color: var(--color-ink-2); font-size: 0.85rem; margin-bottom: 0.75rem;">
            Ofertado por: <strong style="color: var(--color-ink);"><?= esc($lm['empresa_nombre'] ?? 'Empresa Registrada') ?></strong>
            <br>
            Ubicación: <?= esc($lm['ubicacion']) ?>
          </p>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--color-rule); padding-top: 0.75rem; margin-top: 0.5rem;">
          <div>
            <div style="font-size: 0.8rem; color: var(--color-ink-2);">Volumen / Precio</div>
            <div style="font-size: 0.95rem; font-weight: 700; color: var(--color-ink);">
              <?= format_kg($lm['cantidad_kg']) ?> &nbsp;|&nbsp; 
              <span style="color: var(--color-success-text);"><?= format_precio($lm['precio_unitario']) ?>/kg</span>
            </div>
          </div>
          <div>
            <a href="<?= site_url('productos/ver/' . $lm['id']) ?>" class="btn btn-secondary btn-sm">
              Ver Ficha
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?= view('templates/footer') ?>
