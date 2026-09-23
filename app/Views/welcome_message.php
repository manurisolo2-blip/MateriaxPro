<?= view('templates/header', ['pageTitle' => 'MateriaX | Red Industrial de Reutilización Circular']) ?>

<!-- ==========================================================================
     SECCIÓN 1: HERO SECTION ASIMÉTRICO (100% PHP & CSS PURO)
     ========================================================================== -->
<section class="hero-wrapper" id="inicio">
  <div>
    <span class="hero-pill">Proyecto Página Web · Hito 1 · 6° B · ITRT</span>
    <h1 class="hero-heading" style="text-align: left; margin-bottom: 1rem;">
      Transformar excedentes plásticos en <br>
      <span style="color: var(--color-accent);">recursos industriales de valor</span>
    </h1>
    <p class="hero-desc" style="text-align: left; max-width: 580px; margin-bottom: 1.75rem;">
      Plataforma corporativa que conecta industrias y plantas manufactureras para publicar, solicitar y reutilizar excedentes de polímeros industriales (PE, PP, PVC, ABS, Nylon) con trazabilidad verificada, sesiones seguras y arquitectura MVC en CodeIgniter 4.
    </p>

    <div class="hero-actions" style="justify-content: flex-start;">
      <?php if (session()->get('isLoggedIn')): ?>
        <a href="<?= site_url('productos') ?>" class="btn btn-primary">
          📦 Ver Inventario de Polímeros
        </a>
        <a href="<?= site_url('productos/crear') ?>" class="btn btn-secondary">
          ➕ Publicar Lote de Material
        </a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="btn btn-primary">
          🏢 Iniciar Sesión Corporativa
        </a>
        <a href="<?= site_url('register') ?>" class="btn btn-secondary">
          📝 Registrar Empresa
        </a>
      <?php endif; ?>
    </div>

    <!-- Ticker de Métricas Rápidas -->
    <div class="hero-stats-row">
      <div class="hero-stat-card">
        <span class="hero-stat-num">2.480 kg</span>
        <span class="hero-stat-lbl">Plástico Recuperado</span>
      </div>
      <div class="hero-stat-card">
        <span class="hero-stat-num">36</span>
        <span class="hero-stat-lbl">Operaciones Activas</span>
      </div>
      <div class="hero-stat-card">
        <span class="hero-stat-num" style="color: var(--color-success);">99.4%</span>
        <span class="hero-stat-lbl">Validación B2B</span>
      </div>
    </div>
  </div>

  <!-- Panel Asimétrico de Destacado Industrial -->
  <div class="featured-lot-box">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
      <span style="font-size: 0.75rem; color: var(--color-accent); font-weight: 700; letter-spacing: 0.05em;">LOTE DESTACADO #PE-904</span>
      <span class="featured-lot-badge">✓ AUDITADO</span>
    </div>

    <h3 style="font-size: 1.3rem; font-weight: 800; color: #ffffff; margin-bottom: 0.5rem;">
      PEAD Molido Inyección & Soplado
    </h3>
    <p style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 1rem;">
      Sobrante industrial homogéneo de baldes y bidones. Descontaminado, libre de metales y con fluidez controlada para extrusión directa.
    </p>

    <div class="spec-grid">
      <div class="spec-item">
        <span class="spec-label">Volumen Disponible</span>
        <span class="spec-value accent">12.500 kg</span>
      </div>
      <div class="spec-item">
        <span class="spec-label">Índice Fluidez (MFI)</span>
        <span class="spec-value">0.35 g/10min</span>
      </div>
      <div class="spec-item">
        <span class="spec-label">Densidad Específica</span>
        <span class="spec-value">0.954 g/cm³</span>
      </div>
      <div class="spec-item">
        <span class="spec-label">Pureza / Filtrado</span>
        <span class="spec-value">99.8% Granza</span>
      </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid var(--border-color);">
      <span style="font-size: 0.82rem; color: var(--text-muted);">📍 San Martín, Buenos Aires</span>
      <?php if (session()->get('isLoggedIn')): ?>
        <a href="<?= site_url('productos') ?>" class="btn btn-sm btn-primary">
          Ver en Inventario &rarr;
        </a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="btn btn-sm btn-primary">
          Acceder para Cotizar &rarr;
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ==========================================================================
     SECCIÓN 2: ECOSISTEMA & SEGURIDAD INDUSTRIAL B2B
     ========================================================================== -->
<section class="content-section" id="ecosistema">
  <div class="section-heading">
    <span class="section-eyebrow">SEGURIDAD & CONFIABILIDAD INDUSTRIAL</span>
    <h2 class="section-title">Ecosistema B2B de Alta Confianza</h2>
    <p class="section-subtitle">
      MateriaX garantiza transacciones institucionales seguras mediante verificación de personería jurídica, custodia de especificaciones técnicas y trazabilidad integral en cada planta participante.
    </p>
  </div>

  <div class="grid-3">
    <!-- Pilar 1: Auditoría & Control Institucional -->
    <div class="role-card">
      <div class="role-header">
        <div class="role-icon">🛡️</div>
        <div>
          <span style="font-size: 0.75rem; color: var(--color-accent); font-weight: 700; text-transform: uppercase;">GOBERNANZA</span>
          <h3 class="role-title">Auditoría & Control</h3>
        </div>
      </div>
      <p style="font-size: 0.9rem; color: var(--text-secondary);">
        Velar por la integridad, legalidad técnica y cumplimiento normativo ambiental en toda la red de intercambio de recursos.
      </p>
      <ul class="role-list">
        <li>
          <span class="status-dot" style="margin-top: 0.45rem; flex-shrink: 0; background-color: var(--color-accent); box-shadow: 0 0 6px var(--color-accent);"></span>
          <span><strong>Homologación de Empresas:</strong> Validación estricta de CUIT ante AFIP y poderes de representación legal.</span>
        </li>
        <li>
          <span class="status-dot" style="margin-top: 0.45rem; flex-shrink: 0; background-color: var(--color-accent); box-shadow: 0 0 6px var(--color-accent);"></span>
          <span><strong>Moderación de Calidad:</strong> Control sobre fichas técnicas para asegurar lotes de polímeros verídicos.</span>
        </li>
        <li>
          <span class="status-dot" style="margin-top: 0.45rem; flex-shrink: 0; background-color: var(--color-accent); box-shadow: 0 0 6px var(--color-accent);"></span>
          <span><strong>Trazabilidad de Masa:</strong> Supervisión de indicadores de impacto ecológico y kilogramos valorizados.</span>
        </li>
      </ul>
    </div>

    <!-- Pilar 2: Empresas Verificadas -->
    <div class="role-card" style="border-color: rgba(56, 189, 248, 0.4);">
      <div class="role-header">
        <div class="role-icon" style="background-color: rgba(16, 185, 129, 0.12); border-color: rgba(16, 185, 129, 0.25);">🏭</div>
        <div>
          <span style="font-size: 0.75rem; color: #34d399; font-weight: 700; text-transform: uppercase;">RED HOMOLOGADA</span>
          <h3 class="role-title">Empresas & Plantas</h3>
        </div>
      </div>
      <p style="font-size: 0.9rem; color: var(--text-secondary);">
        Operación industrial en dos facetas operativas integradas sin intermediarios innecesarios:
      </p>
      <ul class="role-list">
        <li>
          <span class="status-dot" style="margin-top: 0.45rem; flex-shrink: 0; background-color: var(--color-accent); box-shadow: 0 0 6px var(--color-accent);"></span>
          <span><strong>Faceta Oferente:</strong> Publicación directa de excedentes, scraps limpios, granzas y pallets con ficha técnica oficial.</span>
        </li>
        <li>
          <span class="status-dot" style="margin-top: 0.45rem; flex-shrink: 0; background-color: var(--color-accent); box-shadow: 0 0 6px var(--color-accent);"></span>
          <span><strong>Faceta Demandante:</strong> Consulta protegida y reserva de materias primas secundarias para reinyección en procesos.</span>
        </li>
        <li>
          <span class="status-dot" style="margin-top: 0.45rem; flex-shrink: 0; background-color: var(--color-accent); box-shadow: 0 0 6px var(--color-accent);"></span>
          <span><strong>Trato Directo B2B:</strong> Datos de contacto corporativo e intercambio directo entre plantas productivas.</span>
        </li>
      </ul>
    </div>

    <!-- Pilar 3: Transparencia Total -->
    <div class="role-card">
      <div class="role-header">
        <div class="role-icon" style="background-color: rgba(245, 158, 11, 0.12); border-color: rgba(245, 158, 11, 0.25);">🌐</div>
        <div>
          <span style="font-size: 0.75rem; color: #fbbf24; font-weight: 700; text-transform: uppercase;">TRANSPARENCIA</span>
          <h3 class="role-title">Catálogo Abierto</h3>
        </div>
      </div>
      <p style="font-size: 0.9rem; color: var(--text-secondary);">
        Visualización pública de las categorías industriales y trazabilidad abierta de la red de economía circular.
      </p>
      <ul class="role-list">
        <li>
          <span class="status-dot" style="margin-top: 0.45rem; flex-shrink: 0; background-color: var(--color-accent); box-shadow: 0 0 6px var(--color-accent);"></span>
          <span><strong>Catálogo Estandarizado:</strong> Clasificación por tipo de polímero (PE, PP, PVC, ABS, PA, PET).</span>
        </li>
        <li>
          <span class="status-dot" style="margin-top: 0.45rem; flex-shrink: 0; background-color: var(--color-accent); box-shadow: 0 0 6px var(--color-accent);"></span>
          <span><strong>Filtros por Origen:</strong> Búsqueda por ubicación geográfica y características mecánicas del lote.</span>
        </li>
        <li>
          <span class="status-dot" style="margin-top: 0.45rem; flex-shrink: 0; background-color: var(--color-accent); box-shadow: 0 0 6px var(--color-accent);"></span>
          <span><strong>Acceso Protegido:</strong> Datos de contacto y reserva reservados para miembros registrados con sesión iniciada.</span>
        </li>
      </ul>
    </div>
  </div>
</section>

<!-- ==========================================================================
     SECCIÓN 3: FAMILIAS DE POLÍMEROS INDUSTRIALES
     ========================================================================== -->
<section class="content-section" id="polimeros">
  <div class="section-heading">
    <span class="section-eyebrow">MATERIAS PRIMAS RECUPERADAS</span>
    <h2 class="section-title">Familias de Polímeros en Circulación</h2>
    <p class="section-subtitle">
      Gestión y reutilización de mermas, scraps y pellets plásticos entre plantas productivas para reducir costos de abastecimiento y la huella de carbono.
    </p>
  </div>

  <div class="polymer-grid">
    <!-- Familia 1: Polietileno -->
    <div class="polymer-card">
      <div>
        <span class="polymer-badge">Polietileno (PE)</span>
        <h4 class="polymer-title">PEAD, PEBD & Film</h4>
        <p class="polymer-desc">
          Sobrantes de inyección y soplado, baldes industriales, bidones descontaminados y film termocontraíble limpio para extrusión.
        </p>
      </div>
      <?php if (session()->get('isLoggedIn')): ?>
        <a href="<?= site_url('productos?polimero=Polietileno (PE)') ?>" class="btn btn-secondary btn-sm btn-block">
          Explorar Lotes de PE &rarr;
        </a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="btn btn-secondary btn-sm btn-block">
          Iniciar Sesión para Ver &rarr;
        </a>
      <?php endif; ?>
    </div>

    <!-- Familia 2: Polipropileno -->
    <div class="polymer-card">
      <div>
        <span class="polymer-badge" style="background-color: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.3); color: #34d399;">Polipropileno (PP)</span>
        <h4 class="polymer-title">PP Homopolímero & Copolímero</h4>
        <p class="polymer-desc">
          Scrap de recortes de prensa, carcasas de electrodomésticos, tapas y baldes con índices de fluidez verificados (MFI 8-15).
        </p>
      </div>
      <?php if (session()->get('isLoggedIn')): ?>
        <a href="<?= site_url('productos?polimero=Polipropileno (PP)') ?>" class="btn btn-secondary btn-sm btn-block">
          Explorar Lotes de PP &rarr;
        </a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="btn btn-secondary btn-sm btn-block">
          Iniciar Sesión para Ver &rarr;
        </a>
      <?php endif; ?>
    </div>

    <!-- Familia 3: Materiales Técnicos -->
    <div class="polymer-card">
      <div>
        <span class="polymer-badge" style="background-color: rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.3); color: #fbbf24;">Materiales Técnicos</span>
        <h4 class="polymer-title">ABS, PVC & Nylon (PA)</h4>
        <p class="polymer-desc">
          Descarte de perfilería rígida de PVC, piezas de ABS y poliamida PA6 molida con fibra de vidrio procedente del sector automotriz.
        </p>
      </div>
      <?php if (session()->get('isLoggedIn')): ?>
        <a href="<?= site_url('productos?polimero=ABS') ?>" class="btn btn-secondary btn-sm btn-block">
          Explorar Materiales Técnicos &rarr;
        </a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="btn btn-secondary btn-sm btn-block">
          Iniciar Sesión para Ver &rarr;
        </a>
      <?php endif; ?>
    </div>

    <!-- Familia 4: Equipamiento & Logística -->
    <div class="polymer-card">
      <div>
        <span class="polymer-badge" style="background-color: rgba(168, 85, 247, 0.15); border-color: rgba(168, 85, 247, 0.3); color: #c084fc;">Equipamiento</span>
        <h4 class="polymer-title">Pallets & Logística Reusable</h4>
        <p class="polymer-desc">
          Pallets plásticos reforzados (1200x1000mm) para rack y piso, cajas plásticas industriales, tolvas y tambores homologados.
        </p>
      </div>
      <?php if (session()->get('isLoggedIn')): ?>
        <a href="<?= site_url('productos') ?>" class="btn btn-secondary btn-sm btn-block">
          Ver Equipamiento &rarr;
        </a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="btn btn-secondary btn-sm btn-block">
          Iniciar Sesión para Ver &rarr;
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ==========================================================================
     SECCIÓN 4: INFRAESTRUCTURA Y SEGURIDAD TÉCNICA
     ========================================================================== -->
<section class="content-section" id="seguridad">
  <div class="section-heading">
    <span class="section-eyebrow">ARQUITECTURA DE SEGURIDAD</span>
    <h2 class="section-title">Infraestructura & Protección de Datos</h2>
    <p class="section-subtitle">
      Desarrollado bajo las directrices estrictas de CodeIgniter 4, garantizando robustez institucional y validación en el servidor.
    </p>
  </div>

  <div class="grid-3">
    <div class="feature-box">
      <span class="feature-icon">🔒</span>
      <h3 class="feature-title">Cifrado Criptográfico BCrypt</h3>
      <p class="feature-text">
        Todas las credenciales de acceso se protegen con la función nativa <code>password_hash()</code> utilizando algoritmo bcrypt con coste adaptativo, sin almacenamiento en texto claro.
      </p>
    </div>

    <div class="feature-box">
      <span class="feature-icon">🛡️</span>
      <h3 class="feature-title">Filtros de Sesión & CSRF</h3>
      <p class="feature-text">
        Middleware <code>AuthFilter</code> que intercepta peticiones a módulos protegidos y tokens CSRF activos en formularios para prevenir falsificación de peticiones en sitios cruzados.
      </p>
    </div>

    <div class="feature-box">
      <span class="feature-icon">📋</span>
      <h3 class="feature-title">Validación Server-Side</h3>
      <p class="feature-text">
        Saneamiento estricto de campos numéricos, correos corporativos únicos y cadenas de texto mediante el sistema de validación nativo de CodeIgniter 4 antes de persistir en MySQL.
      </p>
    </div>
  </div>
</section>

<!-- ==========================================================================
     SECCIÓN 5: MÉTRICAS DE IMPACTO CIRCULAR
     ========================================================================== -->
<section class="content-section" id="metricas">
  <div class="section-heading">
    <span class="section-eyebrow">IMPACTO ECOLÓGICO E INDUSTRIAL</span>
    <h2 class="section-title">Métricas de la Red MateriaX</h2>
    <p class="section-subtitle">
      Indicadores calculados sobre las operaciones y lotes canalizados hacia procesos de reciclaje y valorización.
    </p>
  </div>

  <div class="impact-grid">
    <div class="impact-card">
      <div class="impact-num">+24.800</div>
      <div class="impact-label">Kilogramos Recuperados</div>
      <div class="impact-sub">Polímeros reinyectados a manufactura</div>
    </div>

    <div class="impact-card">
      <div class="impact-num">42</div>
      <div class="impact-label">Empresas Homologadas</div>
      <div class="impact-sub">Plantas con personería jurídica activa</div>
    </div>

    <div class="impact-card">
      <div class="impact-num">-52.4 tn</div>
      <div class="impact-label">CO₂ Mitigado</div>
      <div class="impact-sub">Ahorro en producción de plástico virgen</div>
    </div>

    <div class="impact-card">
      <div class="impact-num">100%</div>
      <div class="impact-label">Trazabilidad Documental</div>
      <div class="impact-sub">Origen y destino fiscal registrado</div>
    </div>
  </div>
</section>

<!-- ==========================================================================
     SECCIÓN 6: RESUMEN DE ENTREGABLES ACADÉMICOS (HITO 1)
     ========================================================================== -->
<section class="content-section" style="margin-bottom: 2rem;">
  <div class="card" style="border-color: rgba(56, 189, 248, 0.3);">
    <div class="card-header" style="background-color: rgba(30, 41, 59, 0.6);">
      <h3 class="card-title">Resumen de Entregables — Hito 1 (Primeros Pasos)</h3>
      <span style="font-size: 0.8rem; color: var(--color-accent); font-weight: 700;">Instituto Técnico Río Tercero · 6° B</span>
    </div>
    <div class="card-body">
      <div class="form-row">
        <div>
          <p><strong style="color: #ffffff;">1. DER del Sistema:</strong></p>
          <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.75rem;">
            Documentado formalmente en <a href="<?= site_url('../docs/DER.md') ?>">docs/DER.md</a> y editable en Draw.io con <a href="<?= site_url('../docs/DER_drawio.xml') ?>">docs/DER_drawio.xml</a>.
          </p>

          <p><strong style="color: #ffffff;">2. Modelo Relacional:</strong></p>
          <p style="font-size: 0.9rem; color: var(--text-secondary);">
            Normalizado rigurosamente en 1FN, 2FN y 3FN en <a href="<?= site_url('../docs/MODELO_RELACIONAL.md') ?>">docs/MODELO_RELACIONAL.md</a> y script ejecutable en <code>database.sql</code>.
          </p>
        </div>

        <div>
          <p><strong style="color: #ffffff;">3. Login y Registro Seguro:</strong></p>
          <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.75rem;">
            Autenticación MVC funcionando con sesiones de CodeIgniter 4 y hash seguro <code>password_hash()</code>.
          </p>

          <p><strong style="color: #ffffff;">4. Primer Módulo Funcional (CRUD de Productos):</strong></p>
          <p style="font-size: 0.9rem; color: var(--text-secondary);">
            CRUD de la entidad secundaria <strong>productos</strong> (lotes de polímeros), con listado protegido sólo para usuarios con sesión activa y confirmación de borrado server-side.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==========================================================================
     SECCIÓN 7: CONTACTO INSTITUCIONAL
     ========================================================================== -->
<section class="content-section" id="contacto" style="border-bottom: 1px solid var(--border-color); padding-bottom: 3.5rem;">
  <div class="section-heading">
    <span class="section-eyebrow">CANALES DE ATENCIÓN</span>
    <h2 class="section-title">Contacto & Mesa de Ayuda Institucional</h2>
    <p class="section-subtitle">
      Coordina inspecciones técnicas, consultas normativas o asistencia para la homologación de plantas en la red.
    </p>
  </div>

  <div class="grid-3">
    <div class="card" style="margin-bottom: 0; padding: 1.5rem; text-align: center;">
      <div style="font-size: 2rem; margin-bottom: 0.5rem;">📧</div>
      <h4 style="color: #ffffff; margin-bottom: 0.25rem;">Mesa de Operaciones</h4>
      <p style="font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 0.75rem;">Consultas sobre lotes y registros corporativos</p>
      <a href="mailto:contacto@materiax.com.ar" style="font-weight: 600;">contacto@materiax.com.ar</a>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 1.5rem; text-align: center;">
      <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏢</div>
      <h4 style="color: #ffffff; margin-bottom: 0.25rem;">Sede Institucional</h4>
      <p style="font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 0.75rem;">Instituto Técnico Río Tercero</p>
      <span style="color: #ffffff; font-size: 0.9rem;">Río Tercero, Córdoba, Argentina</span>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 1.5rem; text-align: center;">
      <div style="font-size: 2rem; margin-bottom: 0.5rem;">⏱️</div>
      <h4 style="color: #ffffff; margin-bottom: 0.25rem;">Horario Operativo</h4>
      <p style="font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 0.75rem;">Atención a plantas industriales</p>
      <span style="color: var(--color-success); font-weight: 600; font-size: 0.9rem;">Lunes a Viernes: 08:00 — 17:00 hs</span>
    </div>
  </div>
</section>

<?= view('templates/footer') ?>
