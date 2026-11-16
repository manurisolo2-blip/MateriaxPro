<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Certificado Ambiental | MateriaX']) ?>

<div class="container-md stack-md">
    <nav aria-label="Migas de pan" class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('certificados') ?>">Certificados</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page"><?= esc($certificado['codigo_certificado']) ?></span>
    </nav>

    <div class="flex-between">
        <a href="<?= site_url('certificados') ?>" class="btn btn-secondary btn-sm">&larr; Volver a Mis Certificados</a>
        <span class="text-sec">Operación asociada: <a href="<?= site_url('solicitudes/ver/' . $certificado['transaccion_id']) ?>"><strong><?= esc($certificado['codigo_operacion']) ?></strong></a></span>
    </div>

    <article class="card stack-md">
        <header class="flex-between">
            <div class="flex-row">
                <img src="<?= base_url('assets/logos/isotipo-black.png') ?>" alt="MateriaX" width="44" height="44">
                <div>
                    <strong>MateriaX Pro</strong>
                    <div class="text-muted">Red Industrial Circular · Trazabilidad de Polímeros</div>
                </div>
            </div>
            <div>
                <span class="badge badge-success">Registro Verificado</span>
                <div class="text-mono"><strong><?= esc($certificado['codigo_certificado']) ?></strong></div>
                <div class="text-muted">Emisión: <?= date('d/m/Y', strtotime($certificado['fecha_emision'])) ?></div>
            </div>
        </header>

        <div class="stack-sm">
            <h1>Certificado Oficial de Revalorización Ambiental</h1>
            <p class="text-sec">Constancia técnica de recirculación industrial, mitigación de huella de carbono y desvío de polímeros hacia economía circular.</p>
        </div>

        <div class="card">
            El Sistema de Gestión y Trazabilidad de <strong>MateriaX Pro</strong> hace constar la transferencia y puesta en valor del siguiente excedente:
            <ul>
                <li><strong>Empresa Generadora:</strong> <?= esc($certificado['generadora_nombre']) ?> (CUIT: <?= esc($certificado['generadora_cuit'] ?? 'N/D') ?>) — <?= esc($certificado['generadora_ciudad']) ?>, <?= esc($certificado['generadora_provincia']) ?>.</li>
                <li><strong>Empresa Revalorizadora:</strong> <?= esc($certificado['revalorizadora_nombre']) ?> (CUIT: <?= esc($certificado['revalorizadora_cuit'] ?? 'N/D') ?>) — <?= esc($certificado['revalorizadora_ciudad']) ?>, <?= esc($certificado['revalorizadora_provincia']) ?>.</li>
                <li><strong>Material Recirculado:</strong> <?= esc($certificado['producto_nombre']) ?> (Polímero: <strong><?= esc($certificado['tipo_polimero']) ?></strong>).</li>
            </ul>
        </div>

        <div class="grid-3">
            <div class="metric-card">
                <span class="metric-card-label">Masa Neta Revalorizada</span>
                <div class="metric-card-value text-mono"><?= format_kg($certificado['kg_recuperados']) ?></div>
                <span class="metric-card-sub">Desviado de disposición final</span>
            </div>
            <div class="metric-card">
                <span class="metric-card-label">Emisión de CO₂ Mitigada</span>
                <div class="metric-card-value text-mono"><?= number_format($certificado['co2_evitado_kg'], 1, ',', '.') ?> kg</div>
                <span class="metric-card-sub">Equivalente virgen evitado</span>
            </div>
            <div class="metric-card">
                <span class="metric-card-label">Ahorro Energético</span>
                <div class="metric-card-value text-mono"><?= number_format($certificado['mwh_ahorrado'], 2, ',', '.') ?> MWh</div>
                <span class="metric-card-sub">En síntesis química</span>
            </div>
        </div>

        <div class="text-sec">
            <div><strong>Marco Normativo:</strong> <?= esc($certificado['norma_referencia']) ?></div>
            <div><strong>Código de Operación:</strong> <?= esc($certificado['codigo_operacion']) ?></div>
            <?php if (!empty($certificado['nombre_planta'])): ?>
                <div><strong>Centro de Despacho Logístico:</strong> <?= esc($certificado['nombre_planta']) ?> — <?= esc($certificado['planta_localidad']) ?>, <?= esc($certificado['planta_provincia']) ?></div>
            <?php endif; ?>
            <div><strong>Validación Criptográfica:</strong> SHA-256 verificado en libro de registro circular de MateriaX Pro.</div>
        </div>

        <div class="grid-3">
            <div><strong><?= esc($certificado['generadora_nombre']) ?></strong><br><span class="text-muted">Empresa Generadora</span></div>
            <div><strong><?= esc($certificado['revalorizadora_nombre']) ?></strong><br><span class="text-muted">Empresa Revalorizadora</span></div>
            <div><strong>MateriaX Circular Registry</strong><br><span class="text-muted">Instituto Técnico Río Tercero · 6° B</span></div>
        </div>
    </article>
</div>

<?= view('templates/footer') ?>
