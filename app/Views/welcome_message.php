<?= view('templates/header', ['pageTitle' => 'MateriaX Pro | Red de Intercambio Técnico & Sobras de Polímeros']) ?>
<?php
  $isAuth = (bool) session()->get('isLoggedIn');

  $lotesFeed = [
    [
      'codigo' => '#FIL-108',
      'tipo'   => 'Filamento 3D',
      'badge_cls' => 'badge-polimero-3d',
      'titulo' => 'Sobras de Filamento PETG & Fibra de Carbono',
      'desc'   => 'Lote de bobinas técnicas de 1.75mm remanentes de prototipado automotriz. Tolerancia ±0.02mm, secadas en horno al vacío.',
      'kilos'  => '340 kg',
      'origen' => 'Córdoba Capital',
      'link'   => $isAuth ? site_url('productos') : site_url('register'),
    ],
    [
      'codigo' => '#ROT-204',
      'tipo'   => 'Rotomoldeo',
      'badge_cls' => 'badge-polimero-rotomoldeo',
      'titulo' => 'Polietileno Micronizado PEAD (35 Mesh)',
      'desc'   => 'Polvo micronizado virgen sobrante de tolva para tanques huecos. Densidad 0.938 g/cm³, aditivado con protector UV8.',
      'kilos'  => '4.200 kg',
      'origen' => 'Río Tercero, Cba',
      'link'   => $isAuth ? site_url('productos') : site_url('register'),
    ],
    [
      'codigo' => '#MOD-019',
      'tipo'   => 'Modelos 3D & Matrices',
      'badge_cls' => 'badge-polimero-default',
      'titulo' => 'Matrices de Aluminio & Archivos CAD Rotomoldeo',
      'desc'   => 'Dos matrices maquinadas para piezas utilitarias en rotomoldeo y biblioteca de planos técnicos STEP/STL asociados.',
      'kilos'  => '2 Unidades',
      'origen' => 'San Martín, Bs As',
      'link'   => $isAuth ? site_url('productos') : site_url('register'),
    ],
    [
      'codigo' => '#FIL-092',
      'tipo'   => 'Filamento 3D',
      'badge_cls' => 'badge-polimero-3d',
      'titulo' => 'Scrap Molido PLA+ & ABS Técnico',
      'desc'   => 'Descartes limpios de estructuras de soporte y purgas industriales clasificados por color para peletizado directo.',
      'kilos'  => '850 kg',
      'origen' => 'Rosario, Santa Fe',
      'link'   => $isAuth ? site_url('productos') : site_url('register'),
    ],
  ];

  $categorias = [
    [
      'badge' => 'Rotomoldeo',
      'cls'   => 'badge-polimero-rotomoldeo',
      'title' => 'Polvos Micronizados PEAD / PEBD',
      'desc'  => 'Sobrantes de tolva, mezclas de pigmentación y polietilenos micronizados a 35-50 mesh listos para moldeo rotacional.',
      'cta'   => 'Ver Lotes de Rotomoldeo',
    ],
    [
      'badge' => 'Impresión 3D',
      'cls'   => 'badge-polimero-3d',
      'title' => 'Sobras de Bobinas & Scrap Técnico',
      'desc'  => 'Filamentos técnicos (PETG, PLA+, ABS, Nylon PA12, fibra de carbono) y sobrantes de bobinas de extrusión 3D.',
      'cta'   => 'Ver Filamentos 3D',
    ],
    [
      'badge' => 'Modelos & Matrices',
      'cls'   => 'badge-polimero-default',
      'title' => 'Matrices, Moldes & Archivos CAD',
      'desc'  => 'Intercambio de matrices de rotomoldeo liberadas, moldes de fundición de aluminio y modelos 3D con tolerancia mecánica.',
      'cta'   => 'Ver Matrices & Modelos',
    ],
    [
      'badge' => 'Inyección & Mermas',
      'cls'   => 'badge-polimero-pp',
      'title' => 'Scrap PP, Granzas & Baldes',
      'desc'  => 'Recortes de colada, mermas de inyección con índice MFI verificado y pallets plásticos reutilizables para logística.',
      'cta'   => 'Ver Scrap de Inyección',
    ],
  ];

  $ecosistema = [
    [
      'title' => 'Publicación de Sobras',
      'desc'  => 'Empresas de rotomoldeo y talleres de impresión 3D publican directamente sobrantes de tolva, bobinas abiertas o descartes limpios con ficha técnica verificada.',
      'items' => [
        ['Clasificación Técnica:', 'Filtros por mesh, fluidez (MFI), diámetro y tipo de polímero.'],
        ['Control de Calidad:', 'Declaración jurada de estado de lote y ausencia de metales.'],
        ['Valorización Rápida:', 'Monetización de inventario inmovilizado en depósitos industriales.'],
      ]
    ],
    [
      'title' => 'Intercambio & Compra entre Pares',
      'desc'  => 'Comunidad industrial B2B donde las empresas contactan directamente al generador del material secundario sin comisiones opacas.',
      'items' => [
        ['Trato Directo Planta a Planta:', 'Acceso a datos de contacto corporativo y CUIT validado.'],
        ['Reserva Protegida:', 'Compromiso de volumen con confirmación server-side.'],
        ['Ahorro en Materia Prima:', 'Reducción drástica del costo respecto a resina virgen o bobinas nuevas.'],
      ]
    ],
    [
      'title' => 'Auditoría & Trazabilidad',
      'desc'  => 'Homologación de CUIT ante organismos oficiales para garantizar que cada intercambio ocurra entre empresas jurídicamente verificadas.',
      'items' => [
        ['Homologación Fiscal:', 'Control sobre CUIT corporativo y personería activa.'],
        ['Moderación de Lotes:', 'Revisión técnica antes de publicación abierta en la red.'],
        ['Métricas de Masa:', 'Monitoreo de kilogramos de plástico reintegrados a producción.'],
      ]
    ],
  ];

  $metricas = [
    ['num' => '24.800 kg', 'lbl' => 'Material Reincorporado', 'sub' => 'Polímeros circulares recirculados'],
    ['num' => '42', 'lbl' => 'Plantas Homologadas', 'sub' => 'Empresas con CUIT activo en la red'],
    ['num' => '99.4%', 'lbl' => 'Conformidad Técnica', 'sub' => 'Fichas auditadas según norma'],
    ['num' => '100%', 'lbl' => 'Trazabilidad Fiscal', 'sub' => 'Operaciones directas entre empresas'],
  ];

  $contacto = [
    ['title' => 'Mesa de Operaciones B2B', 'desc' => 'Consultas técnicas y homologación de empresas', 'link' => 'mailto:contacto@materiax.com.ar', 'text' => 'contacto@materiax.com.ar'],
    ['title' => 'Sede Institucional', 'desc' => 'Instituto Técnico Río Tercero', 'sub' => 'Río Tercero, Córdoba, Argentina'],
    ['title' => 'Horario Operativo', 'desc' => 'Atención a plantas industriales', 'sub' => 'Lunes a Viernes: 08:00 — 17:00 hs', 'subClass' => 'color: var(--color-success); font-weight: 600;'],
  ];
?>

<!-- 1. HERO MACROSTRUCTURE: WORKBENCH / SPEC-SHEET HERO -->
<section class="hero-wrapper" id="inicio" aria-label="Introducción a MateriaX Pro">
  <div class="hero-content">
    <div class="hero-kicker">Red B2B · Rotomoldeo, Filamentos 3D & Matrices</div>
    <h1 class="hero-heading">
      Intercambio directo de <br>
      <span style="color: var(--color-accent);">sobras de polímeros y filamentos</span>
    </h1>
    <p class="hero-desc">
      La plataforma para empresas de rotomoldeo, talleres de fabricación digital y plantas plásticas. Comercializa excedentes de tolva, bobinas técnicas de filamento 3D y matrices industriales con homologación de CUIT y trato directo de empresa a empresa.
    </p>

    <div class="hero-actions">
      <?php if ($isAuth): ?>
        <a href="<?= site_url('productos') ?>" class="btn btn-primary btn-lg">Explorar Mercado de Lotes &rarr;</a>
        <a href="<?= site_url('productos/crear') ?>" class="btn btn-secondary btn-lg">Publicar Sobras de Material</a>
      <?php else: ?>
        <a href="<?= site_url('register') ?>" class="btn btn-primary btn-lg">Registrar Empresa (Sign Up) &rarr;</a>
        <a href="<?= site_url('login') ?>" class="btn btn-secondary btn-lg">Iniciar Sesión</a>
      <?php endif; ?>
    </div>

    <div class="hero-stats-row">
      <div class="hero-stat-card"><span class="hero-stat-num">24.800 kg</span><span class="hero-stat-lbl">Polímero Circulado</span></div>
      <div class="hero-stat-card"><span class="hero-stat-num">42</span><span class="hero-stat-lbl">Plantas Activas</span></div>
      <div class="hero-stat-card"><span class="hero-stat-num" style="color: var(--color-success);">100%</span><span class="hero-stat-lbl">Trazabilidad B2B</span></div>
    </div>
  </div>

  <!-- Ficha Técnica de Lote en Vivo (Workbench Spec Box) -->
  <aside class="featured-lot-box" aria-label="Ficha de lote destacado en vivo">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
      <span style="font-size: 0.78rem; color: var(--color-ink-muted); font-weight: 700; font-family: var(--font-mono);">Lote #ROT-204</span>
      <span class="badge badge-success">Auditado</span>
    </div>
    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--color-ink); margin-bottom: 0.4rem;">PEAD Micronizado para Rotomoldeo</h3>
    <p style="font-size: 0.88rem; color: var(--color-ink-2); line-height: 1.5; margin-bottom: 0.85rem;">
      Sobrante homogéneo de tolva industrial en polvo (35 Mesh). Color negro con aditivación UV8 para moldeo rotacional de tanques y contenedores huecos.
    </p>
    <div class="spec-grid">
      <div class="spec-item"><span class="spec-label">Volumen Disponible</span><span class="spec-value" style="color: var(--color-accent);">4.200 kg</span></div>
      <div class="spec-item"><span class="spec-label">Granulometría</span><span class="spec-value">35 Mesh (425 μm)</span></div>
      <div class="spec-item"><span class="spec-label">Índice Fluidez (MFI)</span><span class="spec-value">4.2 g/10min</span></div>
      <div class="spec-item"><span class="spec-label">Densidad Específica</span><span class="spec-value">0.938 g/cm³</span></div>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.5rem; gap: 0.75rem; flex-wrap: wrap;">
      <span style="font-size: 0.82rem; color: var(--color-ink-muted);">Planta Río Tercero, Córdoba</span>
      <a href="<?= site_url($isAuth ? 'productos' : 'register') ?>" class="btn btn-sm btn-primary">
        <?= $isAuth ? 'Ver Ficha de Lote &rarr;' : 'Registrarse para Ofertar &rarr;' ?>
      </a>
    </div>
  </aside>
</section>

<!-- 2. TABLÓN TÁCTIL: SOBRAS DE FILAMENTO & LOTES ACTIVOS EN LA RED -->
<section class="content-section" id="feed" aria-labelledby="heading-feed">
  <div class="section-heading">
    <span class="section-kicker">Intercambio Directo B2B</span>
    <h2 class="section-title" id="heading-feed">Feed de Sobras Técnicas & Materiales en Circulación</h2>
    <p class="section-subtitle">Publicaciones directas cargadas por plantas de rotomoldeo, laboratorios de impresión 3D y transformadores que circulan sus mermas con ficha técnica.</p>
  </div>
  <div class="exchange-feed-grid">
    <?php foreach ($lotesFeed as $f): ?>
      <article class="exchange-card">
        <div>
          <div class="exchange-header">
            <span class="badge <?= $f['badge_cls'] ?>"><?= $f['tipo'] ?></span>
            <span class="exchange-meta"><?= $f['codigo'] ?></span>
          </div>
          <h3 class="exchange-title"><?= $f['titulo'] ?></h3>
          <p class="exchange-desc"><?= $f['desc'] ?></p>
        </div>
        <div>
          <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.65rem; border-top: 1px solid var(--color-rule-subtle); margin-bottom: 0.75rem;">
            <span style="font-weight: 700; font-family: var(--font-mono); color: var(--color-accent); font-size: 0.95rem;"><?= $f['kilos'] ?></span>
            <span style="font-size: 0.78rem; color: var(--color-ink-muted);"><?= $f['origen'] ?></span>
          </div>
          <a href="<?= $f['link'] ?>" class="btn btn-secondary btn-sm btn-block">
            <?= $isAuth ? 'Ver Lote &rarr;' : 'Acceder para Contactar &rarr;' ?>
          </a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<!-- 3. CATEGORÍAS INDUSTRIALES -->
<section class="content-section" id="categorias" aria-labelledby="heading-categorias">
  <div class="section-heading">
    <span class="section-kicker">Materiales Homologados</span>
    <h2 class="section-title" id="heading-categorias">Familias de Materiales Circulables</h2>
    <p class="section-subtitle">Especializados en polímeros micronizados para rotomodelado, filamentos técnicos para manufactura aditiva y matrices industriales reutilizables.</p>
  </div>
  <div class="polymer-grid">
    <?php foreach ($categorias as $cat): ?>
      <div class="polymer-card">
        <div>
          <span class="polymer-badge <?= $cat['cls'] ?>"><?= $cat['badge'] ?></span>
          <h3 class="polymer-title"><?= $cat['title'] ?></h3>
          <p class="polymer-desc"><?= $cat['desc'] ?></p>
        </div>
        <a href="<?= $isAuth ? site_url('productos') : site_url('register') ?>" class="btn btn-secondary btn-sm btn-block">
          <?= $isAuth ? $cat['cta'] . ' &rarr;' : 'Registrar Empresa para Ver &rarr;' ?>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- 4. ECOSISTEMA Y OPERACIÓN DE LA RED -->
<section class="content-section" id="ecosistema" aria-labelledby="heading-ecosistema">
  <div class="section-heading">
    <span class="section-kicker">Confianza & Verificación</span>
    <h2 class="section-title" id="heading-ecosistema">Cómo Opera la Red entre Empresas</h2>
    <p class="section-subtitle">Conexión directa entre industrias con homologación formal de CUIT, asegurando contratos técnicos transparentes y libre de intermediarios burocráticos.</p>
  </div>
  <div class="grid-3">
    <?php foreach ($ecosistema as $eco): ?>
      <div class="role-card">
        <div class="role-header">
          <h3 class="role-title"><?= $eco['title'] ?></h3>
        </div>
        <p style="font-size: 0.9rem; color: var(--color-ink-2); line-height: 1.5;"><?= $eco['desc'] ?></p>
        <ul class="role-list">
          <?php foreach ($eco['items'] as $it): ?>
            <li><span><strong><?= $it[0] ?></strong> <?= $it[1] ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- 5. BANNER RECTOR DE CONVERSIÓN SIGN UP -->
<?php if (! $isAuth): ?>
  <div class="signup-banner" role="region" aria-label="Invitación a unirse a la red">
    <div class="signup-banner-content">
      <h2 class="signup-banner-title">¿Tu empresa genera sobrantes de tolva, bobinas o matrices?</h2>
      <p class="signup-banner-desc">
        Suma tu planta a MateriaX Pro. Publica tu material en menos de 2 minutos, recupera valor económico inmovilizado y accede a sobras de filamento técnico de otras empresas.
      </p>
    </div>
    <div>
      <a href="<?= site_url('register') ?>" class="btn btn-primary btn-lg" style="white-space: nowrap;">
        Registrar Empresa (Sign Up) &rarr;
      </a>
    </div>
  </div>
<?php endif; ?>

<!-- 6. MÉTRICAS DE IMPACTO INDUSTRIAL -->
<section class="content-section" id="metricas" aria-labelledby="heading-metricas">
  <div class="section-heading">
    <span class="section-kicker">Métricas Verificadas</span>
    <h2 class="section-title" id="heading-metricas">Impacto Acumulado en la Red</h2>
    <p class="section-subtitle">Registros auditados correspondientes a las transacciones canalizadas a través de la infraestructura MateriaX.</p>
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

<!-- 7. ENTREGABLES INSTITUCIONALES HITO 1 -->
<section class="content-section" style="margin-bottom: var(--space-md);" aria-label="Resumen de entregables académicos">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Resumen de Entregables — Hito 1</h3>
      <span style="font-size: 0.82rem; color: var(--color-ink-muted); font-weight: 700; font-family: var(--font-mono);">Instituto Técnico Río Tercero &middot; 6° B</span>
    </div>
    <div class="card-body">
      <div class="form-row">
        <?php 
          $entregables = [
            ['1. DER del Sistema', 'Documentado formalmente en <a href="' . site_url('../docs/DER.md') . '">docs/DER.md</a> y editable en Draw.io con <a href="' . site_url('../docs/DER_drawio.xml') . '">docs/DER_drawio.xml</a>.'],
            ['2. Modelo Relacional', 'Normalizado rigurosamente en 1FN, 2FN y 3FN en <a href="' . site_url('../docs/MODELO_RELACIONAL.md') . '">docs/MODELO_RELACIONAL.md</a> y script ejecutable en <code>database.sql</code>.'],
            ['3. Login y Registro Seguro', 'Autenticación MVC funcionando con sesiones de CodeIgniter 4 y hash seguro <code>password_hash()</code>.'],
            ['4. Primer Módulo Funcional (CRUD de Lotes)', 'CRUD de la entidad secundaria <strong>productos</strong> (lotes de polímeros), con listado protegido sólo para usuarios con sesión activa y confirmación de borrado server-side.'],
          ];
          foreach ($entregables as $ent): 
        ?>
          <div style="margin-bottom: 0.75rem;">
            <p style="color: var(--color-ink); font-weight: 700; font-size: 0.92rem; margin-bottom: 0.2rem;"><?= $ent[0] ?>:</p>
            <p style="font-size: 0.88rem; color: var(--color-ink-2); line-height: 1.45;"><?= $ent[1] ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- 8. CONTACTO INSTITUCIONAL -->
<section class="content-section" id="contacto" style="padding-bottom: var(--space-xl);" aria-labelledby="heading-contacto">
  <div class="section-heading">
    <span class="section-kicker">Mesa de Ayuda</span>
    <h2 class="section-title" id="heading-contacto">Contacto & Soporte Técnico de la Red</h2>
    <p class="section-subtitle">Coordina homologaciones de planta, verificación técnica de lotes o asistencia para el registro corporativo.</p>
  </div>
  <div class="grid-3">
    <?php foreach ($contacto as $c): ?>
      <div class="card" style="margin-bottom: 0; padding: 1.75rem 1.5rem; text-align: left;">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--color-ink); margin-bottom: 0.4rem;"><?= $c['title'] ?></h3>
        <p style="font-size: 0.88rem; color: var(--color-ink-2); margin-bottom: 0.85rem; line-height: 1.5;"><?= $c['desc'] ?></p>
        <?php if (!empty($c['link'])): ?>
          <a href="<?= $c['link'] ?>" style="font-weight: 700; color: var(--color-accent); font-family: var(--font-mono); font-size: 0.88rem;"><?= $c['text'] ?></a>
        <?php else: ?>
          <span style="<?= $c['subClass'] ?? 'color: var(--color-ink); font-size: 0.9rem; font-weight: 600;' ?>"><?= $c['sub'] ?></span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?= view('templates/footer') ?>
