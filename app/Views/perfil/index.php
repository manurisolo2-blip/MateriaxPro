<?= view('templates/header', ['pageTitle' => 'Mi Cuenta Empresarial | MateriaX']) ?>

<div style="margin-bottom: 2rem;">
  <span style="font-size: 0.85rem; color: var(--color-accent); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
    PANEL DE GESTIÓN CORPORATIVA
  </span>
  <h1 style="font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-top: 0.25rem;">
    <?= esc($user['nombre']) ?>
  </h1>
  <p style="color: var(--text-secondary); font-size: 0.95rem;">
    Administración de cuenta empresarial, credenciales de seguridad y publicaciones activas en la red.
  </p>
</div>

<!-- Tarjetas de Estadísticas de Cuenta -->
<div class="grid-3" style="margin-top: 0; margin-bottom: 2rem;">
  <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Lotes Publicados</span>
    <div style="font-size: 1.8rem; font-weight: 800; color: var(--color-accent); margin-top: 0.25rem;">
      <?= count($misProductos) ?>
    </div>
    <span style="font-size: 0.8rem; color: var(--text-secondary);">En inventario circular</span>
  </div>

  <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Miembro Desde</span>
    <div style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-top: 0.4rem;">
      <?= date('d/m/Y', strtotime($user['created_at'])) ?>
    </div>
    <span style="font-size: 0.8rem; color: var(--color-success);">● Cuenta Homologada</span>
  </div>

  <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Último Acceso Registrado</span>
    <div style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-top: 0.4rem;">
      <?= !empty($user['ultimo_login']) ? date('d/m/Y H:i', strtotime($user['ultimo_login'])) : 'Sesión actual' ?>
    </div>
    <span style="font-size: 0.8rem; color: var(--text-secondary);">IP / Conexión Segura</span>
  </div>
</div>

<div class="grid-2" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; align-items: start;">
  
  <!-- Columna 1: Edición de Datos Corporativos -->
  <div>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Datos de la Empresa</h3>
      </div>
      <div class="card-body">
        <form action="<?= site_url('perfil/actualizar') ?>" method="POST">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="nombre" class="form-label">Razón Social *</label>
            <input 
              type="text" 
              name="nombre" 
              id="nombre" 
              class="form-control" 
              value="<?= old('nombre', $user['nombre']) ?>" 
              required
            >
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="cuit" class="form-label">CUIT *</label>
              <input 
                type="text" 
                name="cuit" 
                id="cuit" 
                class="form-control" 
                value="<?= old('cuit', $user['cuit']) ?>" 
                required
              >
            </div>

            <div class="form-group">
              <label for="rubro" class="form-label">Rubro Productivo *</label>
              <input 
                type="text" 
                name="rubro" 
                id="rubro" 
                class="form-control" 
                value="<?= old('rubro', $user['rubro'] ?? 'Sector Plástico') ?>" 
                required
              >
            </div>
          </div>

          <div class="form-group">
            <label for="email" class="form-label">Correo Electrónico Corporativo</label>
            <input 
              type="email" 
              class="form-control" 
              value="<?= esc($user['email']) ?>" 
              disabled 
              style="opacity: 0.7; cursor: not-allowed;"
            >
            <p class="form-hint">El correo electrónico institucional no puede modificarse por políticas de auditoría.</p>
          </div>

          <div class="form-group">
            <label for="telefono" class="form-label">Teléfono de Contacto *</label>
            <input 
              type="text" 
              name="telefono" 
              id="telefono" 
              class="form-control" 
              value="<?= old('telefono', $user['telefono']) ?>" 
              required
            >
          </div>

          <div class="form-group">
            <label for="direccion" class="form-label">Domicilio de Planta / Sede *</label>
            <input 
              type="text" 
              name="direccion" 
              id="direccion" 
              class="form-control" 
              value="<?= old('direccion', $user['direccion'] ?? '') ?>" 
              required
            >
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="ciudad" class="form-label">Ciudad / Localidad *</label>
              <input 
                type="text" 
                name="ciudad" 
                id="ciudad" 
                class="form-control" 
                value="<?= old('ciudad', $user['ciudad'] ?? '') ?>" 
                required
              >
            </div>

            <div class="form-group">
              <label for="provincia" class="form-label">Provincia *</label>
              <input 
                type="text" 
                name="provincia" 
                id="provincia" 
                class="form-control" 
                value="<?= old('provincia', $user['provincia'] ?? 'Córdoba') ?>" 
                required
              >
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">
            ✓ Guardar Cambios del Perfil
          </button>
        </form>
      </div>
    </div>

    <!-- Módulo de Cambio de Contraseña Segura -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Seguridad & Contraseña</h3>
      </div>
      <div class="card-body">
        <form action="<?= site_url('perfil/cambiar-password') ?>" method="POST">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="current_password" class="form-label">Contraseña Actual *</label>
            <input 
              type="password" 
              name="current_password" 
              id="current_password" 
              class="form-control" 
              placeholder="Ingresa tu contraseña actual" 
              required
            >
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="new_password" class="form-label">Nueva Contraseña *</label>
              <input 
                type="password" 
                name="new_password" 
                id="new_password" 
                class="form-control" 
                placeholder="Mínimo 6 caracteres" 
                required
              >
            </div>

            <div class="form-group">
              <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
              <input 
                type="password" 
                name="confirm_password" 
                id="confirm_password" 
                class="form-control" 
                placeholder="Repite la nueva clave" 
                required
              >
            </div>
          </div>

          <button type="submit" class="btn btn-secondary btn-block" style="margin-top: 0.5rem;">
            🔒 Actualizar Contraseña
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Columna 2: Mis Publicaciones en MateriaX -->
  <div>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Mis Lotes de Material</h3>
        <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary btn-sm">
          ➕ Publicar Lote
        </a>
      </div>
      <div class="card-body" style="padding: 1rem;">
        <?php if (!empty($misProductos) && count($misProductos) > 0): ?>
          <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <?php foreach ($misProductos as $p): ?>
              <div style="background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 0.85rem 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                  <div style="font-weight: 700; color: var(--text-primary);">
                    <a href="<?= site_url('productos/ver/' . $p['id']) ?>" style="color: var(--text-primary); text-decoration: none;"><?= esc($p['nombre']) ?></a>
                  </div>
                  <div style="font-size: 0.82rem; color: var(--text-secondary); margin-top: 0.2rem;">
                    <span class="badge badge-polimero"><?= esc($p['tipo_polimero']) ?></span>
                    &nbsp;•&nbsp; <strong><?= number_format((float)$p['cantidad_kg'], 0, ',', '.') ?> kg</strong>
                    &nbsp;•&nbsp; $<?= number_format((float)$p['precio_unitario'], 2, ',', '.') ?> / kg
                  </div>
                </div>

                <div style="display: flex; gap: 0.4rem;">
                  <a href="<?= site_url('productos/editar/' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Editar lote">
                    ✏
                  </a>
                  <a href="<?= site_url('productos/ver/' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Ver ficha">
                    👁
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
            <p style="font-size: 0.95rem; margin-bottom: 0.75rem;">Aún no has registrado ningún lote de polímero.</p>
            <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary btn-sm">
              Publicar Primer Excedente
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<?= view('templates/footer') ?>
