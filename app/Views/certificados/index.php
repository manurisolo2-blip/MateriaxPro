<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Certificados Ambientales | MateriaX']) ?>

<div class="container-lg stack-md">
    <nav aria-label="Migas de pan" class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('panel') ?>">Mi Panel</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page">Certificados Ambientales</span>
    </nav>

    <div class="page-header">
        <h1 class="page-title">Certificados de Impacto Ambiental y Huella Verde</h1>
        <p class="page-sub">Registro de emisiones de CO₂ mitigadas y recursos plásticos revalorizados en la red.</p>
    </div>

    <div class="grid-4">
        <div class="metric-card">
            <span class="metric-card-label">Plástico Recuperado</span>
            <div class="metric-card-value text-mono"><?= number_format($metricas['total_kg'], 0, ',', '.') ?> kg</div>
            <span class="metric-card-sub">Desviado de vertederos</span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">CO₂ Evitado Estimado</span>
            <div class="metric-card-value text-mono"><?= number_format($metricas['total_co2'], 1, ',', '.') ?> kg</div>
            <span class="metric-card-sub">Equivalente fósil mitigado</span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Energía Ahorrada</span>
            <div class="metric-card-value text-mono"><?= number_format($metricas['total_mwh'], 2, ',', '.') ?> MWh</div>
            <span class="metric-card-sub">Ahorro petroquímico neto</span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Certificados Emitidos</span>
            <div class="metric-card-value"><?= $metricas['certificados'] ?></div>
            <span class="metric-card-sub">Con firma digital</span>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Constancias Oficiales de Economía Circular</h3></div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código Certificado</th><th>Operación</th><th>Material / Polímero</th>
                        <th>Generadora</th><th>Revalorizadora</th><th>Kg Recuperados</th>
                        <th>CO₂ Mitigado</th><th>Fecha</th><th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($certificados)): ?>
                        <?php foreach ($certificados as $c): ?>
                            <tr>
                                <td><a href="<?= site_url('certificados/ver/' . $c['codigo_certificado']) ?>" class="text-mono"><strong><?= esc($c['codigo_certificado']) ?></strong></a></td>
                                <td><a href="<?= site_url('solicitudes/ver/' . $c['transaccion_id']) ?>"><?= esc($c['codigo_operacion']) ?></a></td>
                                <td><strong><?= esc($c['producto_nombre']) ?></strong><div class="text-muted"><?= esc($c['tipo_polimero']) ?></div></td>
                                <td><?= esc($c['generadora_nombre']) ?></td>
                                <td><?= esc($c['revalorizadora_nombre']) ?></td>
                                <td><strong><?= format_kg($c['kg_recuperados']) ?></strong></td>
                                <td><strong><?= number_format($c['co2_evitado_kg'], 1, ',', '.') ?> kg</strong></td>
                                <td class="text-sec"><?= date('d/m/Y', strtotime($c['fecha_emision'])) ?></td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <a href="<?= site_url('certificados/ver/' . $c['codigo_certificado']) ?>" class="btn btn-primary btn-sm">Ver Certificado &rarr;</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="empty-state">
                                <p class="empty-state-title">Aún no tienes certificados ambientales emitidos</p>
                                <p class="empty-state-desc">Se emiten automáticamente cada vez que se concreta un acuerdo de revalorización sobre un lote.</p>
                                <a href="<?= site_url('solicitudes/recibidas') ?>" class="btn btn-secondary">Revisar Solicitudes Comerciales</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
