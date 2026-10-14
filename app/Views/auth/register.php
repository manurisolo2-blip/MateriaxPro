<?= view('templates/header', ['pageTitle' => 'Registro de Empresa | MateriaX']) ?>

<div style="max-width: 680px; margin: 1.5rem auto;">
  <div class="card">
    <div class="card-header" style="text-align: center; display: block;">
      <h2 class="card-title">Registro en la Red MateriaX</h2>
      <p style="color: var(--text-secondary); font-size: 0.88rem; margin-top: 0.25rem;">
        Crea tu cuenta empresarial para publicar, cotizar y solicitar excedentes de polímeros industriales
      </p>
    </div>

    <div class="card-body">
      <form action="<?= site_url('register') ?>" method="POST">
        <?= csrf_field() ?>

        <!-- Aviso de revisión -->
        <div style="background: rgba(15, 118, 110, 0.06); border: 1px solid rgba(15, 118, 110, 0.2); border-radius: var(--radius-md); padding: 0.85rem 1rem; margin-bottom: 1.5rem;">
          <div style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.45;">
            <strong style="color: var(--color-primary);">Proceso de Auditoría Fiscal:</strong> Al completar el formulario, tu solicitud será revisada por el administrador para validar el CUIT y los datos de tu empresa antes de habilitar el acceso comercial a la plataforma.
          </div>
        </div>

        <!-- Datos de la empresa -->
        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1rem;">
          1. Identificación Corporativa
        </h3>

        <div class="form-group">
          <label for="nombre" class="form-label">Razón Social o Nombre de la Empresa <span class="required-indicator" aria-hidden="true">*</span></label>
          <input 
            type="text" 
            name="nombre" 
            id="nombre" 
            class="form-control" 
            value="<?= old('nombre') ?>" 
            placeholder="Ej: Industrias Plásticas del Centro S.A." 
            autocomplete="organization"
            required
            autofocus
          >
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="cuit" class="form-label">CUIT de la Empresa <span class="required-indicator" aria-hidden="true">*</span></label>
            <input 
              type="text" 
              name="cuit" 
              id="cuit" 
              class="form-control" 
              value="<?= old('cuit') ?>" 
              placeholder="30-XXXXXXXX-X"
              required
            >
          </div>

          <div class="form-group">
            <label for="rubro" class="form-label">Rubro / Sector Productivo <span class="required-indicator" aria-hidden="true">*</span></label>
            <select name="rubro" id="rubro" class="form-select" required>
              <option value="">-- Seleccionar Rubro --</option>
              <?php 
                $rubros = [
                  'Moldeo por Inyección', 'Extrusión de Película / Film', 'Reciclado & Granza',
                  'Soplado de Cuerpos Huecos', 'Compuestos & Masterbatch', 'Termoformado & Envases',
                  'Automotriz & Autopartes', 'Petroquímica & Resinas', 'Otro Sector Industrial'
                ];
                foreach ($rubros as $rb): 
              ?>
                <option value="<?= $rb ?>" <?= (old('rubro') === $rb) ? 'selected' : '' ?>><?= $rb ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Contacto y ubicación -->
        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 1.5rem; margin-bottom: 1rem;">
          2. Radicación & Datos de Contacto
        </h3>

        <div class="form-row">
          <div class="form-group">
            <label for="email" class="form-label">Correo Electrónico Corporativo <span class="required-indicator" aria-hidden="true">*</span></label>
            <input 
              type="email" 
              name="email" 
              id="email" 
              class="form-control" 
              value="<?= old('email') ?>" 
              placeholder="contacto@empresa.com" 
              autocomplete="email"
              required
            >
            <p class="form-hint">Se utilizará para iniciar sesión en la plataforma.</p>
          </div>

          <div class="form-group">
            <label for="telefono" class="form-label">Teléfono Institucional <span class="required-indicator" aria-hidden="true">*</span></label>
            <input 
              type="tel" 
              name="telefono" 
              id="telefono" 
              class="form-control" 
              value="<?= old('telefono') ?>" 
              placeholder="+54 3571 XXXXXX"
              autocomplete="tel"
              required
            >
          </div>
        </div>

        <div class="form-group">
          <label for="direccion" class="form-label">Domicilio de Planta o Sede Fiscal <span class="required-indicator" aria-hidden="true">*</span></label>
          <input 
            type="text" 
            name="direccion" 
            id="direccion" 
            class="form-control" 
            value="<?= old('direccion') ?>" 
            placeholder="Ej: Av. Industrial 1250, Parque Industrial" 
            autocomplete="street-address"
            required
          >
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="provincia" class="form-label">Provincia <span class="required-indicator" aria-hidden="true">*</span></label>
            <select name="provincia" id="provincia" class="form-select" required>
              <option value="">-- Seleccionar Provincia --</option>
              <?php 
                $selProv = $selectedProvincia ?? old('provincia', 'Córdoba');
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
            <label for="ciudad" class="form-label">Ciudad / Localidad <span class="required-indicator" aria-hidden="true">*</span></label>
            <div style="position: relative;">
              <input 
                type="text" 
                name="ciudad" 
                id="ciudad" 
                class="form-control" 
                value="<?= esc($selectedCiudad ?? old('ciudad', 'Río Tercero')) ?>" 
                placeholder="Escribe para buscar (ej: Río Tercero, Tero Cojido)..." 
                autocomplete="off"
                required
              >
              <!-- Menú flotante de autocompletado en tiempo real -->
              <div id="autocomplete-list" class="autocomplete-dropdown" style="display: none;"></div>
            </div>
            <p class="form-hint" id="hint-localidades">
              A medida que escribas aparecerán las localidades para seleccionar.
            </p>
          </div>
        </div>

        <!-- Contraseña de acceso -->
        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 1.5rem; margin-bottom: 1rem;">
          3. Credenciales de Acceso
        </h3>

        <div class="form-row">
          <div class="form-group">
            <label for="password" class="form-label">Contraseña de Acceso <span class="required-indicator" aria-hidden="true">*</span></label>
            <input 
              type="password" 
              name="password" 
              id="password" 
              class="form-control" 
              placeholder="Mínimo 6 caracteres" 
              autocomplete="new-password"
              required
            >
          </div>

          <div class="form-group">
            <label for="pass_confirm" class="form-label">Confirmar Contraseña <span class="required-indicator" aria-hidden="true">*</span></label>
            <input 
              type="password" 
              name="pass_confirm" 
              id="pass_confirm" 
              class="form-control" 
              placeholder="Repite la contraseña" 
              autocomplete="new-password"
              required
            >
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">
          Enviar Solicitud de Registro
        </button>
      </form>
    </div>

    <div class="card-footer" style="text-align: center; font-size: 0.9rem;">
      ¿Ya tienes una cuenta empresarial en MateriaX? 
      <a href="<?= site_url('login') ?>" style="font-weight: 600;">Iniciar Sesión</a>
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
  const hintLocalidades = document.getElementById('hint-localidades');

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

    if (hintLocalidades) {
      hintLocalidades.textContent = 'Localidad seleccionada: ' + item.localidad + ' (' + (item.provincia || getSelectedProvNombre()) + ')';
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
