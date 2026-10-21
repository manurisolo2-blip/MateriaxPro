<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Certificado Ambiental | MateriaX']) ?>

<!-- Migas de pan -->
<nav aria-label="Migas de pan" class="breadcrumb" style="max-width: 860px; margin: 0 auto 1.25rem;">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('certificados') ?>">Certificados</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page"><?= esc($certificado['codigo_certificado']) ?></span>
</nav>

<div style="max-width: 860px; margin: 0 auto 3rem;">

  <!-- Acciones superiores -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem;">
    <a href="<?= site_url('certificados') ?>" class="btn btn-secondary btn-sm">
      &larr; Volver a Mis Certificados
    </a>
    <span style="font-size: 0.88rem; color: var(--color-ink-2);">
      Operación asociada: <a href="<?= site_url('solicitudes/ver/' . $certificado['transaccion_id']) ?>" style="color: inherit; text-decoration: underline;"><strong><?= esc($certificado['codigo_operacion']) ?></strong></a>
    </span>
  </div>

  <!-- Documento formal del certificado -->
  <article class="card" style="border: 2px solid var(--color-ink); padding: 2.5rem; background-color: var(--color-paper-surface); box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
    
    <!-- Membrete institucional -->
    <header style="border-bottom: 2px solid var(--color-ink); padding-bottom: 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
      <div style="display: flex; gap: 0.75rem; align-items: center;">
        <img src="<?= base_url('assets/logos/isotipo-black.png') ?>" alt="MateriaX" width="44" height="44">
        <div>
          <div style="font-size: 1.35rem; font-weight: 800; letter-spacing: -0.02em; color: var(--color-ink);">
            Materia<span style="color: var(--color-accent);">X</span> Pro
          </div>
          <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-ink-2); font-weight: 600;">
            Red Industrial Circular &bull; Trazabilidad de Polímeros
          </div>
        </div>
      </div>

      <div style="text-align: right;">
        <span class="badge badge-success" style="font-size: 0.8rem; padding: 0.3rem 0.6rem; margin-bottom: 0.3rem;">
          Registro Verificado
        </span>
        <div style="font-family: var(--font-mono); font-size: 0.85rem; font-weight: 700; color: var(--color-ink);">
          <?= esc($certificado['codigo_certificado']) ?>
        </div>
        <div style="font-size: 0.78rem; color: var(--color-ink-2);">
          Emisión: <?= date('d/m/Y', strtotime($certificado['fecha_emision'])) ?>
        </div>
      </div>
    </header>

    <!-- Título principal -->
    <div style="text-align: center; margin-bottom: 2rem;">
      <h1 style="font-size: 1.6rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-ink); margin: 0 0 0.5rem 0;">
        Certificado Oficial de Revalorización Ambiental
      </h1>
      <p style="font-size: 0.95rem; color: var(--color-ink-2); max-width: 620px; margin: 0 auto; line-height: 1.5;">
        Constancia técnica de recirculación industrial, mitigación de huella de carbono y desvío de polímeros hacia procesos de economía circular.
      </p>
    </div>

    <!-- Declaración formal -->
    <div style="background-color: var(--color-paper-2); padding: 1.5rem; border: 1px solid var(--color-rule); border-radius: var(--radius-sm); margin-bottom: 2rem; font-size: 0.95rem; line-height: 1.7; color: var(--color-ink);">
      El Sistema de Gestión y Trazabilidad de <strong>MateriaX Pro</strong> hace constar que se ha completado la transferencia y puesta en valor del siguiente excedente industrial:
      <ul style="margin: 0.75rem 0 0.75rem 1.25rem; padding: 0;">
        <li><strong>Empresa Generadora (Oferente):</strong> <?= esc($certificado['generadora_nombre']) ?> (CUIT: <?= esc($certificado['generadora_cuit'] ?? 'N/D') ?>) &mdash; <?= esc($certificado['generadora_ciudad']) ?>, <?= esc($certificado['generadora_provincia']) ?>.</li>
        <li><strong>Empresa Revalorizadora (Receptora):</strong> <?= esc($certificado['revalorizadora_nombre']) ?> (CUIT: <?= esc($certificado['revalorizadora_cuit'] ?? 'N/D') ?>) &mdash; <?= esc($certificado['revalorizadora_ciudad']) ?>, <?= esc($certificado['revalorizadora_provincia']) ?>.</li>
        <li><strong>Material Recirculado:</strong> <?= esc($certificado['producto_nombre']) ?> (Polímero: <strong><?= esc($certificado['tipo_polimero']) ?></strong>).</li>
      </ul>
    </div>

    <!-- Métricas verificadas -->
    <div class="grid-3" style="margin-bottom: 2rem;">
      <div class="metric-card" style="border: 1px solid var(--color-ink); text-align: center;">
        <span class="metric-card-label">Masa Neta Revalorizada</span>
        <div class="metric-card-value" style="color: var(--color-ink); font-size: 1.6rem;">
          <?= format_kg($certificado['kg_recuperados']) ?>
        </div>
        <span class="metric-card-sub">Desviado de disposición final</span>
      </div>

      <div class="metric-card" style="border: 1px solid var(--color-ink); text-align: center;">
        <span class="metric-card-label">Emisión de CO₂ Mitigada</span>
        <div class="metric-card-value" style="color: var(--color-success-text); font-size: 1.6rem;">
          <?= number_format($certificado['co2_evitado_kg'], 1, ',', '.') ?> kg
        </div>
        <span class="metric-card-sub">Equivalente virgen evitado</span>
      </div>

      <div class="metric-card" style="border: 1px solid var(--color-ink); text-align: center;">
        <span class="metric-card-label">Ahorro Energético</span>
        <div class="metric-card-value" style="color: var(--color-ink); font-size: 1.6rem;">
          <?= number_format($certificado['mwh_ahorrado'], 2, ',', '.') ?> MWh
        </div>
        <span class="metric-card-sub">En síntesis química</span>
      </div>
    </div>

    <!-- Cuadro de especificaciones normativas -->
    <div style="border-top: 1px solid var(--color-rule); border-bottom: 1px solid var(--color-rule); padding: 1rem 0; margin-bottom: 2.5rem; font-size: 0.85rem; color: var(--color-ink-2); line-height: 1.6;">
      <div><strong>Marco Normativo:</strong> <?= esc($certificado['norma_referencia']) ?></div>
      <div><strong>Código de Operación:</strong> <?= esc($certificado['codigo_operacion']) ?></div>
      <?php if (!empty($certificado['nombre_planta'])): ?>
        <div><strong>Centro de Despacho Logístico:</strong> <?= esc($certificado['nombre_planta']) ?> &mdash; <?= esc($certificado['planta_localidad']) ?>, <?= esc($certificado['planta_provincia']) ?></div>
      <?php endif; ?>
      <div><strong>Validación Criptográfica:</strong> SHA-256 verificado en libro de registro circular de MateriaX Pro.</div>
    </div>

    <!-- Firmas institucionales -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; text-align: center; margin-top: 2rem;">
      <div style="padding-top: 2rem; border-top: 1px dashed var(--color-rule);">
        <strong style="display: block; font-size: 0.9rem; color: var(--color-ink);"><?= esc($certificado['generadora_nombre']) ?></strong>
        <span style="font-size: 0.78rem; color: var(--color-ink-2);">Empresa Generadora</span>
      </div>

      <div style="padding-top: 2rem; border-top: 1px dashed var(--color-rule);">
        <strong style="display: block; font-size: 0.9rem; color: var(--color-ink);"><?= esc($certificado['revalorizadora_nombre']) ?></strong>
        <span style="font-size: 0.78rem; color: var(--color-ink-2);">Empresa Revalorizadora</span>
      </div>

      <div style="padding-top: 2rem; border-top: 1px dashed var(--color-rule);">
        <strong style="display: block; font-size: 0.9rem; color: var(--color-ink);">MateriaX Circular Registry</strong>
        <span style="font-size: 0.78rem; color: var(--color-ink-2);">Instituto Técnico Río Tercero &bull; 6° B</span>
      </div>
    </div>

  </article>

</div>

<?= view('templates/footer') ?>
