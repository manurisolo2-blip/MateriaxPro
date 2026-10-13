<?= view('templates/header', ['pageTitle' => 'MateriaX Pro | Mercado de Polímeros & Rotomoldeo']) ?>
<?php
  $isAuth = (bool) session()->get('isLoggedIn');
  $linkAcceso = site_url($isAuth ? 'productos' : 'register');

  $lotesFeed = [
    [
      'codigo' => '#FIL-108', 'tipo' => 'Filamento 3D',
      'titulo' => 'Sobras de Filamento PETG & Fibra de Carbono',
      'desc' => 'Bobinas técnicas de 1.75mm remanentes de prototipado. Tolerancia ±0.02mm, secadas en horno al vacío.',
      'kilos' => '340 kg', 'precio' => '$1.850 / kg', 'empresa' => 'Lab Aditivo Córdoba S.A.S.', 'avatar' => '3D',
      'tiempo' => 'Hace 3 horas', 'origen' => 'Córdoba Capital',
      'specs' => 'PETG + Carbono · 1.75mm · Tolerancia ±0.02mm · Secado al Vacío',
    ],
    [
      'codigo' => '#ROT-204', 'tipo' => 'Rotomoldeo',
      'titulo' => 'Polietileno Micronizado PEAD (35 Mesh)',
      'desc' => 'Polvo micronizado virgen sobrante de tolva para tanques. Densidad 0.938 g/cm³, aditivado con protector UV8.',
      'kilos' => '4.200 kg', 'precio' => '$450 / kg', 'empresa' => 'Rotomoldeo del Centro S.A.', 'avatar' => 'PE',
      'tiempo' => 'Hace 5 horas', 'origen' => 'Río Tercero, Cba',
      'specs' => 'PEAD Virgen · 35 Mesh (425μm) · MFI: 4.2 g/10min · Filtro UV8',
    ],
    [
      'codigo' => '#MOD-019', 'tipo' => 'Matrices & CAD',
      'titulo' => 'Matrices de Aluminio & Planos CAD Rotomoldeo',
      'desc' => 'Dos matrices maquinadas para piezas utilitarias en rotomoldeo y biblioteca de planos técnicos STEP/STL asociados.',
      'kilos' => '2 Unidades', 'precio' => 'Consultar Lote', 'empresa' => 'Matricería San Martín Industrial', 'avatar' => 'AL',
      'tiempo' => 'Ayer', 'origen' => 'San Martín, Bs As',
      'specs' => 'Aluminio Maquinado · Archivos STEP/STL · Tolerancia ±0.1mm',
    ],
    [
      'codigo' => '#FIL-092', 'tipo' => 'Filamento 3D',
      'titulo' => 'Scrap Molido PLA+ & ABS Técnico',
      'desc' => 'Descartes limpios clasificados por color para peletizado directo, libres de metales.',
      'kilos' => '850 kg', 'precio' => '$620 / kg', 'empresa' => 'Transformadora Rosario Plásticos', 'avatar' => 'TR',
      'tiempo' => 'Ayer', 'origen' => 'Rosario, Santa Fe',
      'specs' => 'PLA+ & ABS Limpio · Apto Peletizado · Sin Metales',
    ],
  ];

  $categorias = [
    ['title' => 'Polvos Micronizados PEAD / PEBD', 'desc' => 'Sobrantes de tolva y polietilenos micronizados a 35-50 mesh para moldeo rotacional.'],
    ['title' => 'Sobras de Bobinas & Scrap 3D', 'desc' => 'Filamentos técnicos (PETG, PLA+, ABS, Nylon PA12, fibra de carbono) y sobrantes de extrusión.'],
    ['title' => 'Matrices, Moldes & Archivos CAD', 'desc' => 'Matrices de rotomoldeo liberadas, moldes de fundición de aluminio y archivos 3D técnicos.'],
    ['title' => 'Scrap PP, Granzas & Mermas', 'desc' => 'Recortes de colada, mermas de inyección con índice MFI verificado y pallets plásticos reutilizables.'],
  ];

  $ecosistema = [
    ['title' => 'Publicación Directa', 'desc' => 'Las plantas publican excedentes de tolva, bobinas abiertas o descartes limpios con ficha técnica y retiro en planta.'],
    ['title' => 'Trato Planta a Planta', 'desc' => 'Contacto directo entre empresas sin intermediarios, con CUIT validado y reserva de volumen en el sistema.'],
    ['title' => 'Trazabilidad y Calidad', 'desc' => 'Homologación formal y control de especificaciones técnicas para garantizar material apto para producción.'],
  ];

  $metricas = [
    ['num' => '24.800 kg', 'lbl' => 'Material Reincorporado', 'sub' => 'Polímeros recirculados'],
    ['num' => '42', 'lbl' => 'Plantas Activas', 'sub' => 'Empresas registradas'],
    ['num' => '99.4%', 'lbl' => 'Conformidad Técnica', 'sub' => 'Fichas auditadas'],
    ['num' => '100%', 'lbl' => 'Trazabilidad Fiscal', 'sub' => 'Operaciones directas'],
  ];

  $contacto = [
    ['title' => 'Mesa de Operaciones', 'desc' => 'Consultas técnicas y homologación de empresas', 'link' => 'mailto:contacto@materiax.com.ar', 'text' => 'contacto@materiax.com.ar'],
    ['title' => 'Sede Institucional', 'desc' => 'Instituto Técnico Río Tercero', 'sub' => 'Río Tercero, Córdoba, Argentina'],
    ['title' => 'Horario Operativo', 'desc' => 'Atención a plantas industriales', 'sub' => 'Lunes a Viernes: 08:00 — 17:00 hs'],
  ];
?>

<div class="marketplace-layout">

  <!-- 1. SIDEBAR LATERAL IZQUIERDO -->
  <aside class="marketplace-sidebar" aria-label="Navegación y Filtros del Mercado">
    <div>
      <h2 class="marketplace-sidebar-title">
        <svg class="svg-icon svg-icon-md" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        <span>Mercado Industrial</span>
      </h2>
      <p style="font-size: 0.8rem; color: var(--color-ink-muted); margin-top: 0.2rem;">Intercambio directo de polímeros</p>
    </div>

    <div class="marketplace-search-box">
      <span class="marketplace-search-icon" aria-hidden="true"><svg class="svg-icon svg-icon-sm" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span>
      <input type="search" class="marketplace-search-input" placeholder="Buscar polímeros..." aria-label="Buscar en el mercado">
    </div>

    <a href="<?= $isAuth ? site_url('productos/crear') : site_url('register') ?>" class="btn btn-primary btn-block">
      <?= $isAuth ? '+ Publicar Lote' : '+ Registrar Empresa' ?>
    </a>

    <nav aria-label="Categorías">
      <ul class="marketplace-sidebar-nav">
        <li>
          <a href="#feed" class="marketplace-nav-link active">
            <span><svg class="svg-icon svg-icon-sm" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg> Feed de Lotes</span>
          </a>
        </li>
        <li>
          <a href="#categorias" class="marketplace-nav-link">
            <span><svg class="svg-icon svg-icon-sm" viewBox="0 0 24 24" aria-hidden="true"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg> Rotomoldeo (PEAD / PEBD)</span>
          </a>
        </li>
        <li>
          <a href="#categorias" class="marketplace-nav-link">
            <span><svg class="svg-icon svg-icon-sm" viewBox="0 0 24 24" aria-hidden="true"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg> Filamentos 3D & Bobinas</span>
          </a>
        </li>
        <li>
          <a href="#categorias" class="marketplace-nav-link">
            <span><svg class="svg-icon svg-icon-sm" viewBox="0 0 24 24" aria-hidden="true"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5 12 2"></polygon><line x1="12" y1="22" x2="12" y2="15.5"></line></svg> Matrices & Planos CAD</span>
          </a>
        </li>
        <li>
          <a href="#categorias" class="marketplace-nav-link">
            <span><svg class="svg-icon svg-icon-sm" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg> Inyección & Mermas PP</span>
          </a>
        </li>
      </ul>
    </nav>

    <div class="marketplace-filter-block">
      <span class="marketplace-filter-label">Secciones</span>
      <ul class="marketplace-sidebar-nav" style="margin-top: 0.2rem;">
        <li><a href="#ecosistema" class="marketplace-nav-link"><span>Cómo Funciona</span></a></li>
        <li><a href="#metricas" class="marketplace-nav-link"><span>Métricas</span></a></li>
        <li><a href="#entregables" class="marketplace-nav-link"><span>Hito 1 (IT Río Tercero)</span></a></li>
        <li><a href="#contacto" class="marketplace-nav-link"><span>Contacto</span></a></li>
      </ul>
    </div>
  </aside>

  <!-- 2. ÁREA CENTRAL: FEED & CONTENIDO -->
  <div class="marketplace-feed-area">

    <!-- PUBLICADOR RÁPIDO -->
    <div class="social-post-creator" role="region" aria-label="Publicar lote">
      <div class="social-creator-top">
        <div class="social-creator-avatar" aria-hidden="true">
          <svg class="svg-icon svg-icon-sm" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
        </div>
        <a href="<?= $linkAcceso ?>" class="social-creator-input-fake">
          <?= $isAuth ? '¿Qué sobrante de material deseas circular hoy?...' : '¿Tu empresa tiene sobrantes? Registrate para publicar...' ?>
        </a>
        <a href="<?= $linkAcceso ?>" class="btn btn-primary btn-sm" style="white-space: nowrap;">
          <?= $isAuth ? '+ Publicar Lote' : '+ Registrarse' ?>
        </a>
      </div>
    </div>

    <!-- HERO PRINCIPAL -->
    <section class="hero-wrapper" id="inicio" aria-label="Presentación MateriaX Pro">
      <div class="hero-content">
        <div class="hero-kicker">Rotomoldeo, Filamentos 3D & Matrices</div>
        <h1 class="hero-heading">
          Intercambio directo de <br>
          <span style="color: var(--color-accent);">sobras de polímeros y filamentos</span>
        </h1>
        <p class="hero-desc">
          Compra y venta de excedentes de tolva, bobinas de filamento 3D y matrices industriales con trato directo de planta a planta.
        </p>
        <div class="hero-actions">
          <?php if ($isAuth): ?>
            <a href="<?= site_url('productos') ?>" class="btn btn-primary btn-lg">Explorar Mercado &rarr;</a>
            <a href="<?= site_url('productos/crear') ?>" class="btn btn-secondary btn-lg">Publicar Sobras</a>
          <?php else: ?>
            <a href="<?= site_url('register') ?>" class="btn btn-primary btn-lg">Registrar Empresa &rarr;</a>
            <a href="<?= site_url('login') ?>" class="btn btn-secondary btn-lg">Iniciar Sesión</a>
          <?php endif; ?>
        </div>
        <div class="hero-stats-row">
          <div class="hero-stat-card"><span class="hero-stat-num">24.800 kg</span><span class="hero-stat-lbl">Polímero Circulado</span></div>
          <div class="hero-stat-card"><span class="hero-stat-num">42</span><span class="hero-stat-lbl">Plantas Activas</span></div>
          <div class="hero-stat-card"><span class="hero-stat-num" style="color: var(--color-success);">100%</span><span class="hero-stat-lbl">Trazabilidad</span></div>
        </div>
      </div>

      <!-- Ficha de Lote Destacado -->
      <aside class="featured-lot-box" aria-label="Lote destacado en vivo">
        <div class="featured-lot-header">
          <span class="featured-lot-meta">Lote #ROT-204</span>
          <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-success);">Auditado</span>
        </div>
        <h3 class="featured-lot-title">PEAD Micronizado para Rotomoldeo</h3>
        <p class="featured-lot-desc">
          Sobrante de tolva en polvo (35 Mesh). Color negro con protector UV8 para moldeo rotacional de tanques.
        </p>
        <div class="spec-grid">
          <div class="spec-item"><span class="spec-label">Volumen Disponible</span><span class="spec-value" style="color: var(--color-accent);">4.200 kg</span></div>
          <div class="spec-item"><span class="spec-label">Granulometría</span><span class="spec-value">35 Mesh (425 μm)</span></div>
          <div class="spec-item"><span class="spec-label">Fluidez (MFI)</span><span class="spec-value">4.2 g/10min</span></div>
          <div class="spec-item"><span class="spec-label">Densidad</span><span class="spec-value">0.938 g/cm³</span></div>
        </div>
        <div class="featured-lot-footer">
          <span class="featured-lot-location">
            <svg class="svg-icon svg-icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            Planta Río Tercero, Córdoba
          </span>
          <a href="<?= $linkAcceso ?>" class="btn btn-sm btn-primary">
            <?= $isAuth ? 'Ver Lote &rarr;' : 'Ofertar &rarr;' ?>
          </a>
        </div>
      </aside>
    </section>

    <!-- FEED DE LOTES EN CIRCULACIÓN -->
    <section class="content-section section-alt" id="feed" aria-labelledby="heading-feed">
      <div class="section-divider-bar"></div>
      <div class="section-heading">
        <span class="section-kicker">Mercado en Vivo</span>
        <h2 class="section-title" id="heading-feed">Lotes Disponibles</h2>
        <p class="section-subtitle">Excedentes publicados directamente por plantas de rotomoldeo y talleres de fabricación aditiva.</p>
      </div>
      <div class="exchange-feed-grid">
        <?php foreach ($lotesFeed as $f): ?>
          <article class="marketplace-item-card">
            <header class="marketplace-card-header">
              <div class="marketplace-author-wrap">
                <span class="marketplace-author-avatar" aria-hidden="true"><?= $f['avatar'] ?></span>
                <div class="marketplace-author-meta">
                  <span class="marketplace-author-name"><?= $f['empresa'] ?></span>
                  <span class="marketplace-author-time"><?= $f['tiempo'] ?> &middot; <?= $f['origen'] ?></span>
                </div>
              </div>
              <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-ink-muted); font-family: var(--font-mono);"><?= $f['tipo'] ?></span>
            </header>

            <div class="marketplace-card-thumb">
              <div>
                <span style="font-size: 0.72rem; color: var(--color-ink-muted); font-weight: 700;">DISPONIBLE</span>
                <div class="marketplace-card-price"><?= $f['kilos'] ?></div>
              </div>
              <div style="text-align: right;">
                <span class="exchange-meta"><?= $f['codigo'] ?></span>
                <div style="font-size: 0.84rem; font-weight: 800; color: var(--color-ink);"><?= $f['precio'] ?></div>
              </div>
            </div>

            <div class="marketplace-card-body">
              <div>
                <h3 class="exchange-title"><?= $f['titulo'] ?></h3>
                <p class="exchange-desc"><?= $f['desc'] ?></p>
                <div class="lot-specs-text"><?= $f['specs'] ?></div>
              </div>

              <div class="social-card-actions">
                <a href="<?= $linkAcceso ?>" class="btn btn-primary btn-sm" style="flex: 1; text-align: center;">
                  <svg class="svg-icon svg-icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                  <?= $isAuth ? 'Contactar Empresa' : 'Contactar Vendedor' ?>
                </a>
                <a href="<?= $linkAcceso ?>" class="btn btn-secondary btn-sm" title="Ver ficha técnica">
                  Ficha
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- FAMILIAS DE MATERIALES -->
    <section class="content-section" id="categorias" aria-labelledby="heading-categorias">
      <div class="section-divider-bar"></div>
      <div class="section-heading">
        <span class="section-kicker">Materiales</span>
        <h2 class="section-title" id="heading-categorias">Familias de Polímeros</h2>
      </div>
      <div class="polymer-grid">
        <?php foreach ($categorias as $cat): ?>
          <div class="polymer-card">
            <div>
              <h3 class="polymer-title"><?= $cat['title'] ?></h3>
              <p class="polymer-desc"><?= $cat['desc'] ?></p>
            </div>
            <a href="<?= $linkAcceso ?>" class="btn btn-secondary btn-sm btn-block">
              <?= $isAuth ? 'Ver Lotes &rarr;' : 'Registrarse para Ver &rarr;' ?>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- CÓMO FUNCIONA -->
    <section class="content-section section-alt" id="ecosistema" aria-labelledby="heading-ecosistema">
      <div class="section-divider-bar"></div>
      <div class="section-heading">
        <span class="section-kicker">Operación</span>
        <h2 class="section-title" id="heading-ecosistema">Cómo Funciona la Red</h2>
      </div>
      <div class="grid-3">
        <?php foreach ($ecosistema as $eco): ?>
          <div class="role-card">
            <h3 class="role-title" style="margin-bottom: 0.4rem;"><?= $eco['title'] ?></h3>
            <p style="font-size: 0.86rem; color: var(--color-ink-2); line-height: 1.5;"><?= $eco['desc'] ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- BANNER DE REGISTRO -->
    <?php if (! $isAuth): ?>
      <div class="signup-banner" role="region" aria-label="Invitación a unirse a la red">
        <div class="signup-banner-content">
          <h2 class="signup-banner-title">¿Tu empresa genera sobrantes de material?</h2>
          <p class="signup-banner-desc">Publica excedentes de rotomoldeo o filamentos técnicos en 2 minutos y recupera valor económico inmovilizado.</p>
        </div>
        <div>
          <a href="<?= site_url('register') ?>" class="btn btn-primary btn-lg" style="white-space: nowrap;">Registrar Empresa &rarr;</a>
        </div>
      </div>
    <?php endif; ?>

    <!-- MÉTRICAS DE IMPACTO -->
    <section class="content-section section-success" id="metricas" aria-labelledby="heading-metricas">
      <div class="section-divider-bar"></div>
      <div class="section-heading">
        <span class="section-kicker">Métricas</span>
        <h2 class="section-title" id="heading-metricas">Impacto Acumulado</h2>
      </div>
      <div class="grid-4">
        <?php foreach ($metricas as $m): ?>
          <div class="impact-card">
            <div class="impact-num"><?= $m['num'] ?></div>
            <div class="impact-label"><?= $m['lbl'] ?></div>
            <div class="impact-sub"><?= $m['sub'] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ENTREGABLES HITO 1 -->
    <section class="content-section" id="entregables" aria-labelledby="heading-entregables">
      <div class="section-divider-bar"></div>
      <div class="section-heading">
        <span class="section-kicker">Instituto Técnico Río Tercero &middot; 6° B</span>
        <h2 class="section-title" id="heading-entregables">Entregables — Hito 1</h2>
      </div>
      <div class="form-row">
        <div>
          <p style="color: var(--color-ink); font-weight: 700; font-size: 0.9rem; margin-bottom: 0.15rem;">1. DER del Sistema:</p>
          <p style="font-size: 0.84rem; color: var(--color-ink-2); line-height: 1.45;">Documentado en <a href="<?= site_url('../docs/DER.md') ?>">docs/DER.md</a> y Draw.io en <a href="<?= site_url('../docs/DER_drawio.xml') ?>">docs/DER_drawio.xml</a>.</p>
        </div>
        <div>
          <p style="color: var(--color-ink); font-weight: 700; font-size: 0.9rem; margin-bottom: 0.15rem;">2. Modelo Relacional:</p>
          <p style="font-size: 0.84rem; color: var(--color-ink-2); line-height: 1.45;">Normalizado en 1FN, 2FN y 3FN en <a href="<?= site_url('../docs/MODELO_RELACIONAL.md') ?>">docs/MODELO_RELACIONAL.md</a> y script en <code>database.sql</code>.</p>
        </div>
        <div>
          <p style="color: var(--color-ink); font-weight: 700; font-size: 0.9rem; margin-bottom: 0.15rem;">3. Login y Registro Seguro:</p>
          <p style="font-size: 0.84rem; color: var(--color-ink-2); line-height: 1.45;">Autenticación MVC funcionando con sesiones CodeIgniter 4 y hash seguro <code>password_hash()</code>.</p>
        </div>
        <div>
          <p style="color: var(--color-ink); font-weight: 700; font-size: 0.9rem; margin-bottom: 0.15rem;">4. Primer Módulo Funcional (CRUD de Lotes):</p>
          <p style="font-size: 0.84rem; color: var(--color-ink-2); line-height: 1.45;">CRUD de entidad secundaria <strong>productos</strong> con confirmación de borrado server-side.</p>
        </div>
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
            <h3 class="contact-card-title"><?= $c['title'] ?></h3>
            <p class="contact-card-desc"><?= $c['desc'] ?></p>
            <?php if (!empty($c['link'])): ?>
              <a href="<?= $c['link'] ?>" class="contact-card-link"><?= $c['text'] ?></a>
            <?php else: ?>
              <span class="contact-card-sub"><?= $c['sub'] ?></span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

  </div> <!-- /marketplace-feed-area -->

  <!-- 3. RIEL DERECHO: ACTIVIDAD Y DEMANDAS -->
  <aside class="marketplace-social-rail" aria-label="Actividad en vivo y demandas">
    
    <!-- Widget 1: Actividad en Vivo -->
    <div class="social-widget-card">
      <h3 class="social-widget-title"><span class="live-pulse-dot" aria-hidden="true"></span><span>Actividad en Vivo</span></h3>
      <ul class="live-activity-list">
        <li class="live-activity-item">
          <span class="live-pulse-dot" aria-hidden="true"></span>
          <div><strong>Rotomoldeo del Centro</strong> publicó 4.200 kg de PEAD en Río Tercero.<div class="social-time-tag">Hace 2 horas</div></div>
        </li>
        <li class="live-activity-item">
          <span class="live-pulse-dot" aria-hidden="true"></span>
          <div><strong>Lab Aditivo Córdoba</strong> acordó retiro de bobinas PETG (#FIL-108).<div class="social-time-tag">Hace 4 horas</div></div>
        </li>
        <li class="live-activity-item">
          <span class="live-pulse-dot" aria-hidden="true"></span>
          <div><strong>Matricería San Martín</strong> subió 2 matrices de rotomoldeo (#MOD-019).<div class="social-time-tag">Ayer</div></div>
        </li>
        <li class="live-activity-item">
          <span class="live-pulse-dot" aria-hidden="true"></span>
          <div><strong>IT Río Tercero</strong> auditó trazabilidad de scrap circular.<div class="social-time-tag">Ayer</div></div>
        </li>
      </ul>
    </div>

    <!-- Widget 2: Demandas de Compra -->
    <div class="social-widget-card">
      <h3 class="social-widget-title"><svg class="svg-icon svg-icon-sm" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg><span>Demandas de Compra</span></h3>
      <div class="demand-card">
        <div class="demand-title">Buscan 1.000 kg PEAD Molido</div>
        <div class="demand-desc">San Francisco, Córdoba &middot; Scrap limpio para paletizado.</div>
        <div class="demand-action-row">
          <a href="<?= $linkAcceso ?>" class="demand-link">Ofertar &rarr;</a>
        </div>
      </div>
      <div class="demand-card">
        <div class="demand-title">Buscan Bobinas PLA+ / PETG</div>
        <div class="demand-desc">Rosario, Santa Fe &middot; Bobinas de 1.75mm para prototipado continuo.</div>
        <div class="demand-action-row">
          <a href="<?= $linkAcceso ?>" class="demand-link">Ofertar &rarr;</a>
        </div>
      </div>
      <a href="<?= $isAuth ? site_url('productos/crear') : site_url('register') ?>" class="btn btn-secondary btn-sm btn-block">+ Publicar Pedido</a>
    </div>

    <!-- Widget 3: Empresas Participantes -->
    <div class="social-widget-card">
      <h3 class="social-widget-title"><svg class="svg-icon svg-icon-sm" viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg><span>Empresas Registradas</span></h3>
      <div class="company-mini-list">
        <div class="company-mini-item">
          <div><strong>Rotomoldeo del Centro</strong><div class="company-mini-sub">Río Tercero, Córdoba</div></div>
        </div>
        <div class="company-mini-item">
          <div><strong>Lab Aditivo Córdoba</strong><div class="company-mini-sub">Córdoba Capital</div></div>
        </div>
        <div class="company-mini-item">
          <div><strong>Transformadora Rosario</strong><div class="company-mini-sub">Rosario, Santa Fe</div></div>
        </div>
      </div>
    </div>

    <!-- Widget 4: Homologación Hito 1 -->
    <div class="social-widget-card institution-card-compact">
      <div class="institution-head">
        <svg class="svg-icon svg-icon-md" viewBox="0 0 24 24" style="color: var(--color-success);" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
        <h4 class="institution-title">Instituto Técnico Río Tercero</h4>
      </div>
      <p class="institution-desc">
        Proyecto desarrollado para 6° B. Base de datos relacional normalizada y arquitectura Zero-JavaScript.
      </p>
      <div class="institution-actions">
        <a href="#entregables" class="btn btn-secondary btn-sm institution-btn">Ver Hito 1</a>
        <a href="<?= site_url('../docs/DER.md') ?>" class="btn btn-secondary btn-sm institution-btn">docs/DER.md</a>
      </div>
    </div>

  </aside>

</div> <!-- /marketplace-layout -->

<?= view('templates/footer') ?>
