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

            <div class="form-group" style="position: relative;">
              <label for="ciudad" class="form-label">Ciudad / Localidad *</label>
              <div style="position: relative;">
                <input 
                  type="text" 
                  name="ciudad" 
                  id="ciudad" 
                  class="form-control" 
                  value="<?= esc($userCiudad ?? old('ciudad', $user['ciudad'] ?? 'Río Tercero')) ?>" 
                  placeholder="Escribe para buscar (ej: Río Tercero, Tero Cojido)..." 
                  autocomplete="off"
                  required
                >
                <div id="autocomplete-list" class="autocomplete-dropdown" style="display: none;"></div>
              </div>
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

<style>
.autocomplete-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 1050;
  background: var(--bg-surface, #ffffff);
  border: 1px solid var(--border-color, #cbd5e1);
  border-radius: var(--radius-md, 8px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  max-height: 250px;
  overflow-y: auto;
  margin-top: 4px;
}
.autocomplete-item {
  padding: 0.65rem 0.9rem;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--border-subtle, #f1f5f9);
  color: var(--text-primary, #0f172a);
  font-size: 0.9rem;
  transition: background 0.12s ease;
}
.autocomplete-item:last-child {
  border-bottom: none;
}
.autocomplete-item:hover,
.autocomplete-item.active {
  background: rgba(15, 118, 110, 0.1);
  color: var(--color-primary, #0f766e);
}
.autocomplete-badge {
  font-size: 0.75rem;
  padding: 0.15rem 0.5rem;
  border-radius: 9999px;
  background: rgba(15, 118, 110, 0.1);
  color: var(--color-primary, #0f766e);
  font-weight: 500;
  margin-left: 0.5rem;
  white-space: nowrap;
}
.autocomplete-empty {
  padding: 0.75rem 1rem;
  font-size: 0.88rem;
  color: var(--text-secondary, #64748b);
  text-align: center;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const selectProvincia = document.getElementById('provincia');
  const inputCiudad = document.getElementById('ciudad');
  const listContainer = document.getElementById('autocomplete-list');

  let activeIndex = -1;
  let debounceTimer = null;
  let currentItems = [];

  function getSelectedProvId() {
    const opt = selectProvincia.options[selectProvincia.selectedIndex];
    return opt ? opt.getAttribute('data-id') : null;
  }

  function getSelectedProvNombre() {
    return selectProvincia.value || '';
  }

  function renderSuggestions(items, query) {
    currentItems = items || [];
    activeIndex = -1;
    listContainer.innerHTML = '';

    if (!items || items.length === 0) {
      if (query && query.length >= 2) {
        listContainer.innerHTML = '<div class="autocomplete-empty">No se encontró "' + escapeHtml(query) + '". Puedes dejarlo escrito como localidad personalizada.</div>';
        listContainer.style.display = 'block';
      } else {
        listContainer.style.display = 'none';
      }
      return;
    }

    items.forEach(function (item, idx) {
      const div = document.createElement('div');
      div.className = 'autocomplete-item';
      div.setAttribute('data-index', idx);

      const spanName = document.createElement('span');
      if (query && query.length > 0) {
        const regex = new RegExp('(' + escapeRegex(query) + ')', 'gi');
        spanName.innerHTML = escapeHtml(item.localidad).replace(regex, '<strong style="color:var(--color-primary, #0f766e);">$1</strong>');
      } else {
        spanName.textContent = item.localidad;
      }

      const spanProv = document.createElement('span');
      spanProv.className = 'autocomplete-badge';
      spanProv.textContent = item.provincia || getSelectedProvNombre();

      div.appendChild(spanName);
      div.appendChild(spanProv);

      div.addEventListener('mousedown', function (e) {
        e.preventDefault();
        selectItem(item);
      });

      listContainer.appendChild(div);
    });

    listContainer.style.display = 'block';
  }

  function selectItem(item) {
    inputCiudad.value = item.localidad;
    listContainer.style.display = 'none';
    listContainer.innerHTML = '';

    if (item.provincia) {
      for (let i = 0; i < selectProvincia.options.length; i++) {
        if (selectProvincia.options[i].value.toLowerCase() === item.provincia.toLowerCase()) {
          selectProvincia.selectedIndex = i;
          break;
        }
      }
    }
  }

  function fetchLocalidades(query) {
    const provId = getSelectedProvId();
    const provNombre = getSelectedProvNombre();

    let url = '<?= site_url('api/localidades') ?>?';
    const params = [];
    if (provId) params.push('provincia_id=' + encodeURIComponent(provId));
    else if (provNombre) params.push('provincia=' + encodeURIComponent(provNombre));
    if (query) params.push('q=' + encodeURIComponent(query));

    url += params.join('&');

    fetch(url)
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if ((!data || data.length === 0) && query && query.length >= 2 && provId) {
          fetch('<?= site_url('api/localidades') ?>?q=' + encodeURIComponent(query))
            .then(function (r) { return r.json(); })
            .then(function (allData) {
              renderSuggestions(allData, query);
            });
        } else {
          renderSuggestions(data, query);
        }
      })
      .catch(function (err) {
        console.error('Error al autocompletar localidades:', err);
      });
  }

  inputCiudad.addEventListener('input', function () {
    const q = this.value.trim();
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function () {
      fetchLocalidades(q);
    }, 150);
  });

  inputCiudad.addEventListener('focus', function () {
    const q = this.value.trim();
    fetchLocalidades(q);
  });

  document.addEventListener('click', function (e) {
    if (!inputCiudad.contains(e.target) && !listContainer.contains(e.target)) {
      listContainer.style.display = 'none';
    }
  });

  inputCiudad.addEventListener('keydown', function (e) {
    const items = listContainer.querySelectorAll('.autocomplete-item');
    if (!items.length || listContainer.style.display === 'none') return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      activeIndex = (activeIndex + 1) % items.length;
      updateActiveItem(items);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      activeIndex = (activeIndex - 1 + items.length) % items.length;
      updateActiveItem(items);
    } else if (e.key === 'Enter') {
      if (activeIndex >= 0 && activeIndex < currentItems.length) {
        e.preventDefault();
        selectItem(currentItems[activeIndex]);
      }
    } else if (e.key === 'Escape') {
      listContainer.style.display = 'none';
    }
  });

  function updateActiveItem(items) {
    items.forEach(function (el, idx) {
      if (idx === activeIndex) {
        el.classList.add('active');
        el.scrollIntoView({ block: 'nearest' });
      } else {
        el.classList.remove('active');
      }
    });
  }

  selectProvincia.addEventListener('change', function () {
    if (inputCiudad.value.trim() !== '') {
      fetchLocalidades(inputCiudad.value.trim());
    }
  });

  function escapeHtml(str) {
    return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function escapeRegex(str) {
    return (str || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  }
});
</script>

<?= view('templates/footer') ?>
