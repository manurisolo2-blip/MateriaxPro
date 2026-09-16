<?= view('templates/header', ['pageTitle' => 'Registro de Empresa | MateriaX']) ?>

<div style="max-width: 560px; margin: 1.5rem auto;">
  <div class="card">
    <div class="card-header" style="text-align: center; display: block;">
      <h2 class="card-title">Registro en la Red MateriaX</h2>
      <p style="color: var(--text-secondary); font-size: 0.88rem; margin-top: 0.25rem;">
        Crea tu cuenta empresarial para publicar y solicitar excedentes de polímeros
      </p>
    </div>

    <div class="card-body">
      <form action="<?= site_url('register') ?>" method="POST">
        <?= csrf_field() ?>

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
          >
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Correo Electrónico Corporativo *</label>
          <input 
            type="email" 
            name="email" 
            id="email" 
            class="form-control" 
            value="<?= old('email') ?>" 
            placeholder="contacto@industria.com" 
            required
          >
          <p class="form-hint">Se utilizará para iniciar sesión y coordinar operaciones.</p>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="cuit" class="form-label">CUIT (Opcional)</label>
            <input 
              type="text" 
              name="cuit" 
              id="cuit" 
              class="form-control" 
              value="<?= old('cuit') ?>" 
              placeholder="30-XXXXXXXX-X"
            >
          </div>

          <div class="form-group">
            <label for="telefono" class="form-label">Teléfono de Contacto</label>
            <input 
              type="text" 
              name="telefono" 
              id="telefono" 
              class="form-control" 
              value="<?= old('telefono') ?>" 
              placeholder="+54 3571 XXXXXX"
            >
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="password" class="form-label">Contraseña *</label>
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

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">
          Registrar Empresa
        </button>
      </form>
    </div>

    <div class="card-footer" style="text-align: center; font-size: 0.9rem;">
      ¿Ya eres parte de MateriaX? 
      <a href="<?= site_url('login') ?>" style="font-weight: 600;">Iniciar Sesión</a>
    </div>
  </div>
</div>

<?= view('templates/footer') ?>
