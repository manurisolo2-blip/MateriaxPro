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
        <div style="background: rgba(20, 184, 166, 0.1); border: 1px solid rgba(20, 184, 166, 0.3); border-radius: var(--radius-md); padding: 0.85rem 1rem; margin-bottom: 1.5rem; display: flex; gap: 0.75rem; align-items: flex-start;">
          <span style="font-size: 1.25rem; line-height: 1;">🛡️</span>
          <div style="font-size: 0.88rem; color: #e2e8f0; line-height: 1.4;">
            <strong style="color: #14b8a6;">Proceso de Auditoría Fiscal:</strong> Al completar el formulario, tu solicitud será revisada por el administrador para validar el CUIT y los datos de tu empresa antes de habilitar el acceso comercial a la plataforma.
          </div>
        </div>

        <!-- Sección 1: Identificación de la Empresa -->
        <h3 style="font-size: 1.05rem; font-weight: 700; color: #ffffff; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1rem;">
          1. Identificación Corporativa
        </h3>

        <div class="form-group">
          <label for="nombre" class="form-label">Razón Social o Nombre de la Empresa *</label>
          <input 
            type="text" 
            name="nombre" 
            id="nombre" 
            class="form-control" 
            value="<?= old('nombre') ?>" 
            placeholder="Ej: Industrias Plásticas del Centro S.A." 
            required
            autofocus
          >
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="cuit" class="form-label">CUIT de la Empresa *</label>
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
            <label for="rubro" class="form-label">Rubro / Sector Productivo *</label>
            <select name="rubro" id="rubro" class="form-select" required>
              <option value="">-- Seleccionar Rubro --</option>
              <option value="Moldeo por Inyección" <?= (old('rubro') === 'Moldeo por Inyección') ? 'selected' : '' ?>>Moldeo por Inyección</option>
              <option value="Extrusión de Película / Film" <?= (old('rubro') === 'Extrusión de Película / Film') ? 'selected' : '' ?>>Extrusión de Película / Film</option>
              <option value="Reciclado & Granza" <?= (old('rubro') === 'Reciclado & Granza') ? 'selected' : '' ?>>Reciclado & Molienda / Granza</option>
              <option value="Soplado de Cuerpos Huecos" <?= (old('rubro') === 'Soplado de Cuerpos Huecos') ? 'selected' : '' ?>>Soplado de Cuerpos Huecos / Bidones</option>
              <option value="Compuestos & Masterbatch" <?= (old('rubro') === 'Compuestos & Masterbatch') ? 'selected' : '' ?>>Compuestos & Masterbatch</option>
              <option value="Termoformado & Envases" <?= (old('rubro') === 'Termoformado & Envases') ? 'selected' : '' ?>>Termoformado & Envases</option>
              <option value="Automotriz & Autopartes" <?= (old('rubro') === 'Automotriz & Autopartes') ? 'selected' : '' ?>>Automotriz & Autopartes</option>
              <option value="Petroquímica & Resinas" <?= (old('rubro') === 'Petroquímica & Resinas') ? 'selected' : '' ?>>Petroquímica & Producción de Resinas</option>
              <option value="Otro Sector Industrial" <?= (old('rubro') === 'Otro Sector Industrial') ? 'selected' : '' ?>>Otro Sector Industrial</option>
            </select>
          </div>
        </div>

        <!-- Sección 2: Contacto y Radicación -->
        <h3 style="font-size: 1.05rem; font-weight: 700; color: #ffffff; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 1.5rem; margin-bottom: 1rem;">
          2. Radicación & Datos de Contacto
        </h3>

        <div class="form-row">
          <div class="form-group">
            <label for="email" class="form-label">Correo Electrónico Corporativo *</label>
            <input 
              type="email" 
              name="email" 
              id="email" 
              class="form-control" 
              value="<?= old('email') ?>" 
              placeholder="contacto@empresa.com" 
              required
            >
            <p class="form-hint">Se utilizará para iniciar sesión en la plataforma.</p>
          </div>

          <div class="form-group">
            <label for="telefono" class="form-label">Teléfono Institucional *</label>
            <input 
              type="text" 
              name="telefono" 
              id="telefono" 
              class="form-control" 
              value="<?= old('telefono') ?>" 
              placeholder="+54 3571 XXXXXX"
              required
            >
          </div>
        </div>

        <div class="form-group">
          <label for="direccion" class="form-label">Domicilio de Planta o Sede Fiscal *</label>
          <input 
            type="text" 
            name="direccion" 
            id="direccion" 
            class="form-control" 
            value="<?= old('direccion') ?>" 
            placeholder="Ej: Av. Industrial 1250, Parque Industrial" 
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
              value="<?= old('ciudad') ?>" 
              placeholder="Ej: Río Tercero" 
              required
            >
          </div>

          <div class="form-group">
            <label for="provincia" class="form-label">Provincia *</label>
            <select name="provincia" id="provincia" class="form-select" required>
              <option value="">-- Seleccionar Provincia --</option>
              <option value="Córdoba" <?= (old('provincia', 'Córdoba') === 'Córdoba') ? 'selected' : '' ?>>Córdoba</option>
              <option value="Buenos Aires" <?= (old('provincia') === 'Buenos Aires') ? 'selected' : '' ?>>Buenos Aires</option>
              <option value="Ciudad Autónoma de Buenos Aires" <?= (old('provincia') === 'Ciudad Autónoma de Buenos Aires') ? 'selected' : '' ?>>Ciudad Autónoma de Buenos Aires</option>
              <option value="Santa Fe" <?= (old('provincia') === 'Santa Fe') ? 'selected' : '' ?>>Santa Fe</option>
              <option value="Mendoza" <?= (old('provincia') === 'Mendoza') ? 'selected' : '' ?>>Mendoza</option>
              <option value="Entre Ríos" <?= (old('provincia') === 'Entre Ríos') ? 'selected' : '' ?>>Entre Ríos</option>
              <option value="Tucumán" <?= (old('provincia') === 'Tucumán') ? 'selected' : '' ?>>Tucumán</option>
              <option value="San Luis" <?= (old('provincia') === 'San Luis') ? 'selected' : '' ?>>San Luis</option>
              <option value="Salta" <?= (old('provincia') === 'Salta') ? 'selected' : '' ?>>Salta</option>
              <option value="Otra Provincia" <?= (old('provincia') === 'Otra Provincia') ? 'selected' : '' ?>>Otra Provincia</option>
            </select>
          </div>
        </div>

        <!-- Sección 3: Credenciales de Seguridad -->
        <h3 style="font-size: 1.05rem; font-weight: 700; color: #ffffff; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 1.5rem; margin-bottom: 1rem;">
          3. Credenciales de Acceso
        </h3>

        <div class="form-row">
          <div class="form-group">
            <label for="password" class="form-label">Contraseña de Acceso *</label>
            <input 
              type="password" 
              name="password" 
              id="password" 
              class="form-control" 
              placeholder="Mínimo 6 caracteres" 
              required
            >
          </div>

          <div class="form-group">
            <label for="pass_confirm" class="form-label">Confirmar Contraseña *</label>
            <input 
              type="password" 
              name="pass_confirm" 
              id="pass_confirm" 
              class="form-control" 
              placeholder="Repite la contraseña" 
              required
            >
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">
          ✓ Completar Registro y Activar Cuenta
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
