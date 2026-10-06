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

        <!-- Aviso Informativo de la Etapa de Auditoría -->
        <div style="background: rgba(15, 118, 110, 0.06); border: 1px solid rgba(15, 118, 110, 0.2); border-radius: var(--radius-md); padding: 0.85rem 1rem; margin-bottom: 1.5rem;">
          <div style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.45;">
            <strong style="color: var(--color-primary);">Proceso de Auditoría Fiscal:</strong> Al completar el formulario, tu solicitud será revisada por el administrador para validar el CUIT y los datos de tu empresa antes de habilitar el acceso comercial a la plataforma.
          </div>
        </div>

        <!-- Sección 1: Identificación de la Empresa -->
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

        <!-- Sección 2: Contacto y Radicación -->
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
            <label for="ciudad" class="form-label">Ciudad / Localidad <span class="required-indicator" aria-hidden="true">*</span></label>
            <input 
              type="text" 
              name="ciudad" 
              id="ciudad" 
              class="form-control" 
              value="<?= old('ciudad') ?>" 
              placeholder="Ej: Río Tercero" 
              autocomplete="address-level2"
              required
            >
          </div>

          <div class="form-group">
            <label for="provincia" class="form-label">Provincia <span class="required-indicator" aria-hidden="true">*</span></label>
            <select name="provincia" id="provincia" class="form-select" required>
              <option value="">-- Seleccionar Provincia --</option>
              <?php 
                $provincias = ['Córdoba', 'Buenos Aires', 'Ciudad Autónoma de Buenos Aires', 'Santa Fe', 'Mendoza', 'Entre Ríos', 'Tucumán', 'San Luis', 'Salta', 'Otra Provincia'];
                foreach ($provincias as $prov): 
              ?>
                <option value="<?= $prov ?>" <?= (old('provincia', 'Córdoba') === $prov) ? 'selected' : '' ?>><?= $prov ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Sección 3: Credenciales de Seguridad -->
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

<?= view('templates/footer') ?>
