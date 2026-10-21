<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Certificados Ambientales | MateriaX']) ?>

<!-- Migas de pan -->
<nav aria-label="Migas de pan" class="breadcrumb" style="max-width: 1120px; margin: 0 auto 1.25rem;">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('panel') ?>">Mi Panel</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page">Certificados Ambientales</span>
</nav>

<div style="max-width: 1120px; margin: 0 auto 2.5rem;">

  <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <div>
      <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--color-ink); margin: 0 0 0.35rem 0;">
        Certificados de Impacto Ambiental y Huella Verde
      </h1>
      <p style="color: var(--color-ink-2); font-size: 0.95rem; margin: 0;">
        Registro oficial y trazabilidad de emisiones de CO₂ mitigadas y recursos plásticos revalorizados en la red.
      </p>
    </div>
  </div>

  <!-- Métricas ecológicas de la empresa -->
  <div class="grid-4" style="margin-bottom: 1.75rem;">
    <div class="metric-card">
      <span class="metric-card-label">Plástico Recuperado</span>
      <div class="metric-card-value" style="color: var(--color-accent);">
        <?= number_format($metricas['total_kg'], 0, ',', '.') ?> <span style="font-size: 1rem; color: var(--color-ink-2);">kg</span>
      </div>
      <span class="metric-card-sub">Desviado de vertederos</span>
    </div>

    <div class="metric-card">
      <span class="metric-card-label">CO₂ Evitado Estimado</span>
      <div class="metric-card-value" style="color: var(--color-success-text);">
        <?= number_format($metricas['total_co2'], 1, ',', '.') ?> <span style="font-size: 1rem; color: var(--color-ink-2);">kg</span>
      </div>
      <span class="metric-card-sub">Equivalente fósil mitigado</span>
    </div>

    <div class="metric-card">
      <span class="metric-card-label">Energía Ahorrada</span>
      <div class="metric-card-value" style="color: var(--color-ink);">
        <?= number_format($metricas['total_mwh'], 2, ',', '.') ?> <span style="font-size: 1rem; color: var(--color-ink-2);">MWh</span>
      </div>
      <span class="metric-card-sub">Ahorro petroquímico neto</span>
    </div>

    <div class="metric-card">
      <span class="metric-card-label">Certificados Emitidos</span>
      <div class="metric-card-value">
        <?= $metricas['certificados'] ?>
      </div>
      <span class="metric-card-sub">Con firma digital</span>
    </div>
  </div>

  <!-- Listado de certificados -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Constancias Oficiales de Economía Circular</h3>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th scope="col">Código Certificado</th>
            <th scope="col">Operación</th>
            <th scope="col">Material / Polímero</th>
            <th scope="col">Generadora</th>
            <th scope="col">Revalorizadora</th>
            <th scope="col">Kg Recuperados</th>
            <th scope="col">CO₂ Mitigado</th>
            <th scope="col">Fecha</th>
            <th scope="col" style="text-align: right;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($certificados) && count($certificados) > 0): ?>
            <?php foreach ($certificados as $c): ?>
              <tr>
                <td>
                  <a href="<?= site_url('certificados/ver/' . $c['codigo_certificado']) ?>" style="font-family: var(--font-mono); font-weight: 700; color: var(--color-ink);">
                    <?= esc($c['codigo_certificado']) ?>
                  </a>
                </td>
                <td>
                  <a href="<?= site_url('solicitudes/ver/' . $c['transaccion_id']) ?>" style="color: inherit; text-decoration: underline;">
                    <?= esc($c['codigo_operacion']) ?>
                  </a>
                </td>
                <td>
                  <strong><?= esc($c['producto_nombre']) ?></strong>
                  <div style="font-size: 0.78rem; color: var(--color-ink-2);">
                    <?= esc($c['tipo_polimero']) ?>
                  </div>
                </td>
                <td><?= esc($c['generadora_nombre']) ?></td>
                <td><?= esc($c['revalorizadora_nombre']) ?></td>
                <td><strong><?= format_kg($c['kg_recuperados']) ?></strong></td>
                <td>
                  <strong style="color: var(--color-success-text);">
                    <?= number_format($c['co2_evitado_kg'], 1, ',', '.') ?> kg
                  </strong>
                </td>
                <td style="font-size: 0.82rem; color: var(--color-ink-2);">
                  <?= date('d/m/Y', strtotime($c['fecha_emision'])) ?>
                </td>
                <td style="text-align: right; white-space: nowrap;">
                  <a href="<?= site_url('certificados/ver/' . $c['codigo_certificado']) ?>" class="btn btn-primary btn-sm">
                    Ver Certificado &rarr;
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" style="text-align: center; padding: 3rem 1.5rem; color: var(--color-ink-2);">
                <p style="font-size: 1.1rem; color: var(--color-ink); font-weight: 600; margin-bottom: 0.5rem;">
                  Aún no tienes certificados ambientales emitidos
                </p>
                <p style="font-size: 0.92rem; margin-bottom: 1.25rem;">
                  Los certificados se emiten automáticamente cada vez que se concreta un acuerdo de revalorización sobre un lote de material en la plataforma.
                </p>
                <a href="<?= site_url('solicitudes/recibidas') ?>" class="btn btn-secondary">
                  Revisar Solicitudes Comerciales
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
