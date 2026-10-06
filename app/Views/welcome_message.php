<?= view('templates/header', ['pageTitle' => 'MateriaX | Red Industrial de Reutilización Circular']) ?>
<?php
  $isAuth = (bool) session()->get('isLoggedIn');
  $familias = [
    ['badge' => 'Polietileno (PE)', 'cls' => 'badge-polimero-pe', 'title' => 'PEAD, PEBD & Film', 'desc' => 'Sobrantes de inyección y soplado, baldes industriales, bidones descontaminados y film termocontraíble limpio para extrusión.', 'param' => 'polimero=' . urlencode('Polietileno (PE)'), 'cta' => 'Explorar Lotes de PE'],
    ['badge' => 'Polipropileno (PP)', 'cls' => 'badge-polimero-pp', 'title' => 'PP Homopolímero & Copolímero', 'desc' => 'Scrap de recortes de prensa, carcasas de electrodomésticos, tapas y baldes con índices de fluidez verificados (MFI 8-15).', 'param' => 'polimero=' . urlencode('Polipropileno (PP)'), 'cta' => 'Explorar Lotes de PP'],
    ['badge' => 'Materiales Técnicos', 'cls' => 'badge-polimero-abs', 'title' => 'ABS, PVC & Nylon (PA)', 'desc' => 'Descarte de perfilería rígida de PVC, piezas de ABS y poliamida PA6 molida con fibra de vidrio procedente del sector automotriz.', 'param' => 'polimero=ABS', 'cta' => 'Explorar Materiales Técnicos'],
    ['badge' => 'Equipamiento', 'cls' => 'badge-polimero-equipamiento', 'title' => 'Pallets & Logística Reusable', 'desc' => 'Pallets plásticos reforzados (1200x1000mm) para rack y piso, cajas plásticas industriales, tolvas y tambores homologados.', 'param' => '', 'cta' => 'Ver Equipamiento'],
  ];

  $ecosistema = [
    ['title' => 'Auditoría & Control', 'desc' => 'Velar por la integridad, legalidad técnica y cumplimiento normativo ambiental en toda la red de intercambio de recursos.', 'items' => [
      ['Homologación de Empresas:', 'Validación estricta de CUIT ante AFIP y poderes de representación legal.'],
      ['Moderación de Calidad:', 'Control sobre fichas técnicas para asegurar lotes de polímeros verídicos.'],
      ['Trazabilidad de Masa:', 'Supervisión de indicadores de impacto ecológico y kilogramos valorizados.'],
    ]],
    ['title' => 'Empresas & Plantas', 'desc' => 'Operación industrial en dos facetas operativas integradas sin intermediarios innecesarios:', 'items' => [
      ['Faceta Oferente:', 'Publicación directa de excedentes, scraps limpios, granzas y pallets con ficha técnica oficial.'],
      ['Faceta Demandante:', 'Consulta protegida y reserva de materias primas secundarias para reinyección en procesos.'],
      ['Trato Directo B2B:', 'Datos de contacto corporativo e intercambio directo entre plantas productivas.'],
    ]],
    ['title' => 'Catálogo Abierto', 'desc' => 'Visualización pública de las categorías industriales y trazabilidad abierta de la red de economía circular.', 'items' => [
      ['Catálogo Estandarizado:', 'Clasificación por tipo de polímero (PE, PP, PVC, ABS, PA, PET).'],
      ['Filtros por Origen:', 'Búsqueda por ubicación geográfica y características mecánicas del lote.'],
      ['Acceso Protegido:', 'Datos de contacto y reserva reservados para miembros registrados con sesión iniciada.'],
    ]],
  ];

  $seguridad = [
    ['title' => 'Cifrado Criptográfico BCrypt', 'desc' => 'Todas las credenciales de acceso se protegen con la función nativa password_hash() utilizando algoritmo bcrypt con coste adaptativo, sin almacenamiento en texto claro.'],
    ['title' => 'Filtros de Sesión & CSRF', 'desc' => 'Middleware AuthFilter que intercepta peticiones a módulos protegidos y tokens CSRF activos en formularios para prevenir falsificación de peticiones en sitios cruzados.'],
    ['title' => 'Validación Server-Side', 'desc' => 'Saneamiento estricto de campos numéricos, correos corporativos únicos y cadenas de texto mediante el sistema de validación nativo de CodeIgniter 4 antes de persistir en MySQL.'],
  ];

  $impacto = [
    ['num' => '+24.800', 'lbl' => 'Kilogramos Recuperados', 'sub' => 'Polímeros reinyectados a manufactura'],
    ['num' => '42', 'lbl' => 'Empresas Homologadas', 'sub' => 'Plantas con personería jurídica activa'],
    ['num' => '-52.4 tn', 'lbl' => 'CO₂ Mitigado', 'sub' => 'Ahorro en producción de plástico virgen'],
    ['num' => '100%', 'lbl' => 'Trazabilidad Documental', 'sub' => 'Origen y destino fiscal registrado'],
  ];

  $contacto = [
    ['title' => 'Mesa de Operaciones', 'desc' => 'Consultas sobre lotes y registros corporativos', 'link' => 'mailto:contacto@materiax.com.ar', 'text' => 'contacto@materiax.com.ar'],
    ['title' => 'Sede Institucional', 'desc' => 'Instituto Técnico Río Tercero', 'sub' => 'Río Tercero, Córdoba, Argentina'],
    ['title' => 'Horario Operativo', 'desc' => 'Atención a plantas industriales', 'sub' => 'Lunes a Viernes: 08:00 — 17:00 hs', 'subClass' => 'color: var(--color-success); font-weight: 600;'],
  ];
?>

<!-- 1. HERO SECTION -->
<section class="hero-wrapper" id="inicio" aria-label="Introducción a la plataforma MateriaX">
  <div class="hero-content">
    <h1 class="hero-heading">
      Transformar excedentes plásticos en <br>
      <span style="color: var(--color-primary);">recursos industriales de valor</span>
    </h1>
    <p class="hero-desc">
      Plataforma corporativa que conecta industrias y plantas manufactureras para publicar, solicitar y reutilizar excedentes de polímeros industriales (PE, PP, PVC, ABS, Nylon) con trazabilidad verificada, sesiones seguras y arquitectura MVC en CodeIgniter 4.
    </p>

    <div class="hero-actions">
      <?php if ($isAuth): ?>
        <a href="<?= site_url('productos') ?>" class="btn btn-primary">Ver Inventario de Polímeros</a>
        <a href="<?= site_url('productos/crear') ?>" class="btn btn-secondary">Publicar Lote de Material</a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="btn btn-primary">Iniciar Sesión Corporativa</a>
        <a href="<?= site_url('register') ?>" class="btn btn-secondary">Registrar Empresa</a>
      <?php endif; ?>
    </div>

    <div class="hero-stats-row">
      <div class="hero-stat-card"><span class="hero-stat-num">2.480 kg</span><span class="hero-stat-lbl">Plástico Recuperado</span></div>
      <div class="hero-stat-card"><span class="hero-stat-num">36</span><span class="hero-stat-lbl">Operaciones Activas</span></div>
      <div class="hero-stat-card"><span class="hero-stat-num" style="color: var(--color-success);">99.4%</span><span class="hero-stat-lbl">Validación B2B</span></div>
    </div>
  </div>

  <div class="featured-lot-box">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
      <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">Lote #PE-904</span>
      <span class="badge badge-success">Auditado</span>
    </div>
    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">PEAD Molido Inyección & Soplado</h3>
    <p style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 1rem;">
      Sobrante industrial homogéneo de baldes y bidones. Descontaminado, libre de metales y con fluidez controlada para extrusión directa.
    </p>
    <div class="spec-grid">
      <div class="spec-item"><span class="spec-label">Volumen Disponible</span><span class="spec-value" style="color: var(--color-primary);">12.500 kg</span></div>
      <div class="spec-item"><span class="spec-label">Índice Fluidez (MFI)</span><span class="spec-value">0.35 g/10min</span></div>
      <div class="spec-item"><span class="spec-label">Densidad Específica</span><span class="spec-value">0.954 g/cm³</span></div>
      <div class="spec-item"><span class="spec-label">Pureza / Filtrado</span><span class="spec-value">99.8% Granza</span></div>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem;">
      <span style="font-size: 0.82rem; color: var(--text-muted);">Planta San Martín, Buenos Aires</span>
      <a href="<?= site_url($isAuth ? 'productos' : 'login') ?>" class="btn btn-sm btn-primary">
        <?= $isAuth ? 'Ver en Inventario &rarr;' : 'Acceder para Cotizar &rarr;' ?>
      </a>
    </div>
  </div>
</section>

<!-- 2. ECOSISTEMA B2B -->
<section class="content-section" id="ecosistema" aria-labelledby="heading-ecosistema">
  <div class="section-heading">
    <h2 class="section-title" id="heading-ecosistema">Ecosistema B2B de Alta Confianza</h2>
    <p class="section-subtitle">MateriaX garantiza transacciones institucionales seguras mediante verificación de personería jurídica, custodia de especificaciones técnicas y trazabilidad integral en cada planta participante.</p>
  </div>
  <div class="grid-3">
    <?php foreach ($ecosistema as $eco): ?>
      <div class="role-card">
        <div class="role-header"><h3 class="role-title"><?= $eco['title'] ?></h3></div>
        <p style="font-size: 0.9rem; color: var(--text-secondary);"><?= $eco['desc'] ?></p>
        <ul class="role-list">
          <?php foreach ($eco['items'] as $it): ?>
            <li><span><strong><?= $it[0] ?></strong> <?= $it[1] ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- 3. FAMILIAS DE POLÍMEROS -->
<section class="content-section" id="polimeros" aria-labelledby="heading-polimeros">
  <div class="section-heading">
    <h2 class="section-title" id="heading-polimeros">Familias de Polímeros en Circulación</h2>
    <p class="section-subtitle">Gestión y reutilización de mermas, scraps y pellets plásticos entre plantas productivas para reducir costos de abastecimiento y la huella de carbono.</p>
  </div>
  <div class="polymer-grid">
    <?php foreach ($familias as $f): ?>
      <div class="polymer-card">
        <div>
          <span class="polymer-badge <?= $f['cls'] ?>"><?= $f['badge'] ?></span>
          <h4 class="polymer-title"><?= $f['title'] ?></h4>
          <p class="polymer-desc"><?= $f['desc'] ?></p>
        </div>
        <a href="<?= $isAuth ? site_url('productos' . ($f['param'] ? '?' . $f['param'] : '')) : site_url('login') ?>" class="btn btn-secondary btn-sm btn-block">
          <?= $isAuth ? $f['cta'] . ' &rarr;' : 'Iniciar Sesión para Ver &rarr;' ?>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- 4. SEGURIDAD E INFRAESTRUCTURA -->
<section class="content-section" id="seguridad" aria-labelledby="heading-seguridad">
  <div class="section-heading">
    <h2 class="section-title" id="heading-seguridad">Infraestructura & Protección de Datos</h2>
    <p class="section-subtitle">Desarrollado bajo las directrices estrictas de CodeIgniter 4, garantizando robustez institucional y validación en el servidor.</p>
  </div>
  <div class="grid-3">
    <?php foreach ($seguridad as $s): ?>
      <div class="role-card">
        <h3 class="role-title"><?= $s['title'] ?></h3>
        <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.55;"><?= $s['desc'] ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- 5. MÉTRICAS DE IMPACTO -->
<section class="content-section" id="metricas" aria-labelledby="heading-metricas">
  <div class="section-heading">
    <h2 class="section-title" id="heading-metricas">Métricas de la Red MateriaX</h2>
    <p class="section-subtitle">Indicadores calculados sobre las operaciones y lotes canalizados hacia procesos de reciclaje y valorización.</p>
  </div>
  <div class="grid-4">
    <?php foreach ($impacto as $imp): ?>
      <div class="impact-card">
        <div class="impact-num"><?= $imp['num'] ?></div>
        <div class="impact-label"><?= $imp['lbl'] ?></div>
        <div class="impact-sub"><?= $imp['sub'] ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- 6. ENTREGABLES HITO 1 -->
<section class="content-section" style="margin-bottom: 2rem;" aria-label="Resumen de entregables">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Resumen de Entregables — Hito 1 (Primeros Pasos)</h3>
      <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">Instituto Técnico Río Tercero &middot; 6° B</span>
    </div>
    <div class="card-body">
      <div class="form-row">
        <?php 
          $entregables = [
            ['1. DER del Sistema', 'Documentado formalmente en <a href="' . site_url('../docs/DER.md') . '">docs/DER.md</a> y editable en Draw.io con <a href="' . site_url('../docs/DER_drawio.xml') . '">docs/DER_drawio.xml</a>.'],
            ['2. Modelo Relacional', 'Normalizado rigurosamente en 1FN, 2FN y 3FN en <a href="' . site_url('../docs/MODELO_RELACIONAL.md') . '">docs/MODELO_RELACIONAL.md</a> y script ejecutable en <code>database.sql</code>.'],
            ['3. Login y Registro Seguro', 'Autenticación MVC funcionando con sesiones de CodeIgniter 4 y hash seguro <code>password_hash()</code>.'],
            ['4. Primer Módulo Funcional (CRUD de Productos)', 'CRUD de la entidad secundaria <strong>productos</strong> (lotes de polímeros), con listado protegido sólo para usuarios con sesión activa y confirmación de borrado server-side.'],
          ];
          foreach ($entregables as $ent): 
        ?>
          <div style="margin-bottom: 0.75rem;">
            <p><strong><?= $ent[0] ?>:</strong></p>
            <p style="font-size: 0.9rem; color: var(--text-secondary);"><?= $ent[1] ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- 7. CONTACTO -->
<section class="content-section" id="contacto" style="border-bottom: 1px solid var(--border-color); padding-bottom: 3.5rem;" aria-labelledby="heading-contacto">
  <div class="section-heading">
    <h2 class="section-title" id="heading-contacto">Contacto & Mesa de Ayuda Institucional</h2>
    <p class="section-subtitle">Coordina inspecciones técnicas, consultas normativas o asistencia para la homologación de plantas en la red.</p>
  </div>
  <div class="grid-3">
    <?php foreach ($contacto as $c): ?>
      <div class="card" style="margin-bottom: 0; padding: 1.75rem 1.5rem; text-align: center;">
        <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.4rem;"><?= $c['title'] ?></h3>
        <p style="font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 0.85rem;"><?= $c['desc'] ?></p>
        <?php if (!empty($c['link'])): ?>
          <a href="<?= $c['link'] ?>" style="font-weight: 600; color: var(--color-primary);"><?= $c['text'] ?></a>
        <?php else: ?>
          <span style="<?= $c['subClass'] ?? 'color: var(--text-primary); font-size: 0.9rem; font-weight: 500;' ?>"><?= $c['sub'] ?></span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?= view('templates/footer') ?>
