<?= view('templates/header', ['pageTitle' => 'MateriaX Pro | Mercado de Polímeros & Rotomoldeo']) ?>
<?php
  $isAuth = (bool) session()->get('isLoggedIn');
  $link = site_url($isAuth ? 'productos' : 'register');

  $lotes = [
    ['cod' => '#FIL-108', 'tipo' => 'Filamento 3D', 'tit' => 'Sobras de Filamento PETG & Fibra de Carbono', 'desc' => 'Bobinas técnicas de 1.75mm remanentes de prototipado. Tolerancia ±0.02mm, secadas en horno al vacío.', 'kg' => '340 kg', 'precio' => '$1.850 / kg', 'emp' => 'Lab Aditivo Córdoba S.A.S.', 'av' => '3D', 'time' => 'Hace 3 h · Córdoba Cap.', 'specs' => 'PETG + Carbono · 1.75mm · Tolerancia ±0.02mm · Secado al Vacío'],
    ['cod' => '#ROT-204', 'tipo' => 'Rotomoldeo', 'tit' => 'Polietileno Micronizado PEAD (35 Mesh)', 'desc' => 'Polvo virgen sobrante de tolva para tanques. Densidad 0.938 g/cm³, aditivado con protector UV8.', 'kg' => '4.200 kg', 'precio' => '$450 / kg', 'emp' => 'Rotomoldeo del Centro S.A.', 'av' => 'PE', 'time' => 'Hace 5 h · Río Tercero', 'specs' => 'PEAD Virgen · 35 Mesh (425μm) · MFI: 4.2 g/10min · Filtro UV8'],
    ['cod' => '#MOD-019', 'tipo' => 'Matrices & CAD', 'tit' => 'Matrices de Aluminio & Planos CAD Rotomoldeo', 'desc' => 'Dos matrices maquinadas para rotomoldeo y planos técnicos STEP/STL asociados.', 'kg' => '2 U.', 'precio' => 'Consultar', 'emp' => 'Matricería San Martín Industrial', 'av' => 'AL', 'time' => 'Ayer · San Martín, Bs As', 'specs' => 'Aluminio Maquinado · Archivos STEP/STL · Tolerancia ±0.1mm'],
    ['cod' => '#FIL-092', 'tipo' => 'Filamento 3D', 'tit' => 'Scrap Molido PLA+ & ABS Técnico', 'desc' => 'Descartes limpios clasificados por color para peletizado directo, libres de metales.', 'kg' => '850 kg', 'precio' => '$620 / kg', 'emp' => 'Transformadora Rosario Plásticos', 'av' => 'TR', 'time' => 'Ayer · Rosario, Santa Fe', 'specs' => 'PLA+ & ABS Limpio · Apto Peletizado · Sin Metales'],
  ];

  $contacto = [
    ['tit' => 'Mesa de Operaciones', 'desc' => 'Consultas técnicas y homologación de empresas', 'link' => 'mailto:contacto@materiax.com.ar', 'txt' => 'contacto@materiax.com.ar'],
    ['tit' => 'Sede Institucional', 'desc' => 'Instituto Técnico Río Tercero', 'txt' => 'Río Tercero, Córdoba, Argentina'],
    ['tit' => 'Horario Operativo', 'desc' => 'Atención a plantas industriales', 'txt' => 'Lunes a Viernes: 08:00 — 17:00 hs'],
  ];
?>

<div class="marketplace-feed-area" style="max-width: 1080px; margin: 0 auto; width: 100%;">

  <!-- HERO PRINCIPAL -->
  <section class="hero-wrapper" id="inicio" style="grid-template-columns: 1fr; text-align: center; padding: 2.5rem 1.5rem;" aria-label="Presentación MateriaX Pro">
    <div class="hero-content" style="max-width: 780px; margin: 0 auto;">
      <div class="hero-kicker">MateriaX Pro · Mercado Industrial</div>
      <h1 class="hero-heading" style="font-size: 2.2rem; line-height: 1.25;">
        Intercambio directo de <span style="color: var(--color-accent);">sobras de polímeros y filamentos</span>
      </h1>
      <p class="hero-desc" style="max-width: 620px; margin: 0.75rem auto 1.5rem;">
        Compra y venta de excedentes de tolva, bobinas de filamento 3D y matrices industriales con trato directo entre empresas.
      </p>
      <div class="hero-actions" style="justify-content: center;">
        <?php if ($isAuth): ?>
          <a href="<?= site_url('productos') ?>" class="btn btn-primary btn-lg">Explorar Mercado &rarr;</a>
          <a href="<?= site_url('productos/crear') ?>" class="btn btn-secondary btn-lg">Publicar Sobras</a>
        <?php else: ?>
          <a href="<?= site_url('register') ?>" class="btn btn-primary btn-lg">Registrar Empresa &rarr;</a>
          <a href="<?= site_url('login') ?>" class="btn btn-secondary btn-lg">Iniciar Sesión</a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- FEED DE LOTES EN CIRCULACIÓN -->
  <section class="content-section" id="feed" aria-labelledby="heading-feed">
    <div class="section-divider-bar"></div>
    <div class="section-heading">
      <span class="section-kicker">Mercado en Vivo</span>
      <h2 class="section-title" id="heading-feed">Lotes Disponibles</h2>
    </div>
    <div class="exchange-feed-grid">
      <?php foreach ($lotes as $f): ?>
        <article class="marketplace-item-card">
          <header class="marketplace-card-header">
            <div class="marketplace-author-wrap">
              <span class="marketplace-author-avatar" aria-hidden="true"><?= $f['av'] ?></span>
              <div class="marketplace-author-meta">
                <span class="marketplace-author-name"><?= $f['emp'] ?></span>
                <span class="marketplace-author-time"><?= $f['time'] ?></span>
              </div>
            </div>
            <span style="font-size: 0.72rem; font-weight: 700; color: var(--color-ink-muted); font-family: var(--font-mono);"><?= $f['tipo'] ?></span>
          </header>

          <div class="marketplace-card-thumb">
            <div>
              <span style="font-size: 0.72rem; color: var(--color-ink-muted); font-weight: 700;">DISPONIBLE</span>
              <div class="marketplace-card-price"><?= $f['kg'] ?></div>
            </div>
            <div style="text-align: right;">
              <span class="exchange-meta"><?= $f['cod'] ?></span>
              <div style="font-size: 0.84rem; font-weight: 800; color: var(--color-ink);"><?= $f['precio'] ?></div>
            </div>
          </div>

          <div class="marketplace-card-body">
            <div>
              <h3 class="exchange-title"><?= $f['tit'] ?></h3>
              <p class="exchange-desc"><?= $f['desc'] ?></p>
              <div class="lot-specs-text"><?= $f['specs'] ?></div>
            </div>

            <div class="social-card-actions">
              <a href="<?= $link ?>" class="btn btn-primary btn-sm" style="flex: 1; text-align: center;">
                <?= $isAuth ? 'Contactar Empresa' : 'Contactar Vendedor' ?>
              </a>
              <a href="<?= $link ?>" class="btn btn-secondary btn-sm" title="Ver ficha técnica">Ficha</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- CONTACTO INSTITUCIONAL -->
  <section class="content-section section-alt" id="contacto" aria-labelledby="heading-contacto">
    <div class="section-divider-bar"></div>
    <div class="section-heading">
      <span class="section-kicker">Soporte</span>
      <h2 class="section-title" id="heading-contacto">Contacto</h2>
    </div>
    <div class="grid-3">
      <?php foreach ($contacto as $c): ?>
        <div class="card contact-card">
          <h3 class="contact-card-title"><?= $c['tit'] ?></h3>
          <p class="contact-card-desc"><?= $c['desc'] ?></p>
          <?php if (!empty($c['link'])): ?>
            <a href="<?= $c['link'] ?>" class="contact-card-link"><?= $c['txt'] ?></a>
          <?php else: ?>
            <span class="contact-card-sub"><?= $c['txt'] ?></span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

</div>

<?= view('templates/footer') ?>
