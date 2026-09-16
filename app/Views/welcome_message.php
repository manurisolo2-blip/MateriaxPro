<?= view('templates/header', ['pageTitle' => 'MateriaX | Red Industrial de Reutilización Circular']) ?>

<!-- Hero Section -->
<section class="hero-box">
  <span class="hero-pill">Proyecto Página Web · Hito 1 · 6° B</span>
  <h1 class="hero-heading">
    TRANSFORMAR EXCEDENTES EN <br>
    <span style="color: var(--color-accent);">RECURSOS DE VALOR</span>
  </h1>
  <p class="hero-desc">
    Plataforma corporativa que conecta industrias para publicar, solicitar y reutilizar polímeros industriales (PE, PP, PVC, ABS, Nylon) con trazabilidad verificada y arquitectura MVC en CodeIgniter 4.
  </p>

  <div class="hero-actions">
    <?php if (session()->get('isLoggedIn')): ?>
      <a href="<?= site_url('productos') ?>" class="btn btn-primary">
        📦 Ver Inventario de Polímeros
      </a>
      <a href="<?= site_url('productos/crear') ?>" class="btn btn-secondary">
        ➕ Publicar Nuevo Lote
      </a>
    <?php else: ?>
      <a href="<?= site_url('login') ?>" class="btn btn-primary">
        🏢 Iniciar Sesión para Ver Inventario
      </a>
      <a href="<?= site_url('register') ?>" class="btn btn-secondary">
        📝 Registrar Empresa
      </a>
    <?php endif; ?>
  </div>
</section>

<!-- Tarjetas de Características Principales -->
<div class="grid-3" style="margin-top: 1rem; margin-bottom: 3rem;">
  <div class="feature-box">
    <span class="feature-icon">♻️</span>
    <h3 class="feature-title">Economía Circular</h3>
    <p class="feature-text">
      Gestión y reutilización de mermas, scraps y pellets plásticos entre plantas productivas para reducir el impacto ambiental y optimizar costos de materia prima.
    </p>
  </div>

  <div class="feature-box">
    <span class="feature-icon">🔒</span>
    <h3 class="feature-title">Acceso Protegido (Hito 1)</h3>
    <p class="feature-text">
      El inventario y gestión de lotes es de visualización exclusiva para usuarios registrados y autenticados mediante sesiones seguras de CodeIgniter 4.
    </p>
  </div>

  <div class="feature-box">
    <span class="feature-icon">📊</span>
    <h3 class="feature-title">CRUD Completo</h3>
    <p class="feature-text">
      Módulo funcional de la entidad secundaria: alta de lotes, edición de stock y precios, visualización de fichas técnicas y baja de publicaciones.
    </p>
  </div>
</div>

<!-- Resumen de Entrega Hito 1 -->
<div class="card" style="margin-bottom: 2rem;">
  <div class="card-header">
    <h3 class="card-title">Resumen de Entregables — Hito 1 (Primeros Pasos)</h3>
  </div>
  <div class="card-body">
    <div class="form-row">
      <div>
        <p><strong style="color: #ffffff;">1. DER del Sistema:</strong></p>
        <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.75rem;">
          Disponible en <a href="<?= site_url('../docs/DER.md') ?>">docs/DER.md</a> y editable en Draw.io con <a href="<?= site_url('../docs/DER_drawio.xml') ?>">docs/DER_drawio.xml</a>.
        </p>

        <p><strong style="color: #ffffff;">2. Modelo Relacional:</strong></p>
        <p style="font-size: 0.9rem; color: var(--text-secondary);">
          Normalizado en 1FN, 2FN y 3FN en <a href="<?= site_url('../docs/MODELO_RELACIONAL.md') ?>">docs/MODELO_RELACIONAL.md</a> y script SQL en <code>database.sql</code>.
        </p>
      </div>

      <div>
        <p><strong style="color: #ffffff;">3. Login y Registro:</strong></p>
        <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.75rem;">
          Formularios funcionando con sesiones seguras y contraseñas cifradas con <code>password_hash()</code>.
        </p>

        <p><strong style="color: #ffffff;">4. Primer Módulo Funcional:</strong></p>
        <p style="font-size: 0.9rem; color: var(--text-secondary);">
          CRUD de la entidad secundaria <strong>productos</strong> (lotes de polímeros), con listado protegido sólo para usuarios logueados.
        </p>
      </div>
    </div>
  </div>
</div>

<?= view('templates/footer') ?>
