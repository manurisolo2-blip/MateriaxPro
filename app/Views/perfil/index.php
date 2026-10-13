<?= view('templates/header', ['pageTitle' => 'Mi Cuenta Empresarial | MateriaX']) ?>

<div style="margin-bottom: 2rem;">
  <h1 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin-top: 0.25rem;">
    <?= esc($user['nombre']) ?>
  </h1>
  <p style="color: var(--text-secondary); font-size: 0.95rem;">
    Administración de cuenta empresarial, credenciales de seguridad y publicaciones activas en la red.
  </p>
</div>

<!-- Estadísticas de la cuenta -->
<div class="grid-3" style="margin-top: 0; margin-bottom: 2rem;">
  <div class="metric-card">
    <span class="metric-card-label">Lotes Publicados</span>
    <div class="metric-card-value">
      <?= count($misProductos) ?>
    </div>
    <span class="metric-card-sub">En inventario circular</span>
  </div>

  <div class="metric-card">
    <span class="metric-card-label">Miembro Desde</span>
    <div class="metric-card-value" style="font-size: 1.5rem;">
      <?= date('d/m/Y', strtotime($user['created_at'])) ?>
    </div>
    <span class="metric-card-sub" style="color: var(--color-success); font-weight: 600;">Cuenta Homologada</span>
  </div>

  <div class="metric-card">
    <span class="metric-card-label">Último Acceso Registrado</span>
    <div class="metric-card-value" style="font-size: 1.4rem;">
      <?= !empty($user['ultimo_login']) ? date('d/m/Y H:i', strtotime($user['ultimo_login'])) : 'Sesión actual' ?>
    </div>
    <span class="metric-card-sub">IP / Conexión Segura</span>
  </div>
</div>

<div class="grid-2" style="align-items: start;">
  
  <!-- Datos de la empresa -->
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
              <label for="provincia" class="form-label">Provincia *</label>
              <select name="provincia" id="provincia" class="form-select" required>
                <option value="">-- Seleccionar Provincia --</option>
                <?php 
                  $selProv = $userProv ?? old('provincia', $user['provincia'] ?? 'Córdoba');
                  if (!empty($provincias)):
                    foreach ($provincias as $prov):
                      $pNombre = is_array($prov) ? $prov['provincia'] : $prov;
                      $pId = is_array($prov) ? $prov['id'] : '';
                ?>
                  <option value="<?= esc($pNombre) ?>" data-id="<?= esc($pId) ?>" <?= ($selProv === $pNombre) ? 'selected' : '' ?>>
                    <?= esc($pNombre) ?>
                  </option>
                <?php 
                    endforeach;
                  endif;
                ?>
              </select>
            </div>

            <div class="form-group">
              <label for="ciudad" class="form-label">Ciudad / Localidad *</label>
              <?php $selCiudad = $userCiudad ?? old('ciudad', $user['ciudad'] ?? 'Río Tercero'); ?>
              <select name="ciudad" id="ciudad" class="form-select" required>
                <option value="">-- Seleccionar Localidad --</option>
                <?php if (!empty($localidades)): ?>
                  <?php foreach ($localidades as $loc): ?>
                    <?php $locNombre = is_array($loc) ? $loc['localidad'] : $loc; ?>
                    <option value="<?= esc($locNombre) ?>" <?= ($selCiudad === $locNombre) ? 'selected' : '' ?>>
                      <?= esc($locNombre) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
                <option value="__otra__" <?= ($selCiudad === '__otra__' || old('ciudad_otra')) ? 'selected' : '' ?>>
                  -- Otra localidad (ingresar manualmente) --
                </option>
              </select>
              <input 
                type="text" 
                name="ciudad_otra" 
                id="ciudad_otra" 
                class="form-control" 
                value="<?= esc(old('ciudad_otra')) ?>" 
                placeholder="Escribe el nombre de tu localidad" 
                style="<?= ($selCiudad === '__otra__' || old('ciudad_otra')) ? 'margin-top: 0.5rem;' : 'display: none; margin-top: 0.5rem;' ?>"
              >
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">
            Guardar Cambios
          </button>
        </form>
      </div>
    </div>

    <!-- Cambiar contraseña -->
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
            Actualizar Contraseña
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Mis publicaciones -->
  <div>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Mis Lotes de Material</h3>
        <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary btn-sm">
          Publicar Lote
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
                    <span class="badge badge-polimero <?= badge_polimero_class($p['tipo_polimero']) ?>"><?= esc($p['tipo_polimero']) ?></span>
                    &nbsp;&bull;&nbsp; <strong><?= format_kg($p['cantidad_kg']) ?></strong>
                    &nbsp;&bull;&nbsp; <?= format_precio($p['precio_unitario']) ?> / kg
                  </div>
                </div>

                <div style="display: inline-flex; gap: 0.4rem;">
                  <a href="<?= site_url('productos/editar/' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Editar lote">
                    Editar
                  </a>
                  <a href="<?= site_url('productos/ver/' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Ver ficha">
                    Ver
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  const selectProvincia = document.getElementById('provincia');
  const selectCiudad = document.getElementById('ciudad');
  const inputCiudadOtra = document.getElementById('ciudad_otra');

  function toggleManualInput() {
    if (selectCiudad.value === '__otra__') {
      inputCiudadOtra.style.display = 'block';
      inputCiudadOtra.required = true;
      inputCiudadOtra.focus();
    } else {
      inputCiudadOtra.style.display = 'none';
      inputCiudadOtra.required = false;
    }
  }

  selectCiudad.addEventListener('change', toggleManualInput);

  selectProvincia.addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    const provId = selectedOption ? selectedOption.getAttribute('data-id') : null;
    const provNombre = this.value;

    if (!provNombre) {
      selectCiudad.innerHTML = '<option value="">-- Seleccione una provincia primero --</option>';
      toggleManualInput();
      return;
    }

    selectCiudad.disabled = true;
    selectCiudad.innerHTML = '<option value="">Cargando localidades de ' + provNombre + '...</option>';

    const url = '<?= site_url('api/localidades') ?>?provincia_id=' + encodeURIComponent(provId || '') + '&provincia=' + encodeURIComponent(provNombre);

    fetch(url)
      .then(function (res) {
        if (!res.ok) throw new Error('Error en la respuesta');
        return res.json();
      })
      .then(function (data) {
        selectCiudad.innerHTML = '<option value="">-- Seleccionar Localidad (' + data.length + ') --</option>';
        data.forEach(function (item) {
          const opt = document.createElement('option');
          opt.value = item.localidad;
          opt.textContent = item.localidad;
          selectCiudad.appendChild(opt);
        });

        const optOtra = document.createElement('option');
        optOtra.value = '__otra__';
        optOtra.textContent = '-- Otra localidad (ingresar manualmente) --';
        selectCiudad.appendChild(optOtra);

        selectCiudad.disabled = false;
        toggleManualInput();
      })
      .catch(function (err) {
        console.error('Error al cargar localidades:', err);
        selectCiudad.disabled = false;
        selectCiudad.innerHTML = '<option value="">-- Error al cargar localidades --</option><option value="__otra__" selected>Ingresar manualmente</option>';
        toggleManualInput();
      });
  });

  toggleManualInput();
});
</script>

<?= view('templates/footer') ?>
