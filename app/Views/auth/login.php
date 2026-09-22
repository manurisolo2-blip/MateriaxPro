<?= view('templates/header', ['pageTitle' => 'Iniciar Sesión | MateriaX']) ?>

<div style="max-width: 480px; margin: 2rem auto;">
  <div class="card">
    <div class="card-header" style="text-align: center; display: block;">
      <h2 class="card-title">Acceso a la Red MateriaX</h2>
      <p style="color: var(--text-secondary); font-size: 0.88rem; margin-top: 0.25rem;">
        Ingresa con tus credenciales de empresa homologada
      </p>
    </div>

    <div class="card-body">
      <form action="<?= site_url('login') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
          <label for="email" class="form-label">Correo Electrónico Corporativo</label>
          <input 
            type="email" 
            name="email" 
            id="email" 
            class="form-control" 
            value="<?= old('email') ?>" 
            placeholder="contacto@empresa.com" 
            required 
            autofocus
          >
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Contraseña</label>
          <input 
            type="password" 
            name="password" 
            id="password" 
            class="form-control" 
            placeholder="Tu contraseña de acceso" 
            required
          >
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">
          Iniciar Sesión
        </button>
      </form>
    </div>

    <div class="card-footer" style="text-align: center; font-size: 0.9rem;">
      ¿Aún no tienes cuenta institucional? 
      <a href="<?= site_url('register') ?>" style="font-weight: 600;">Registrar Empresa</a>
    </div>
  </div>
</div>

<?= view('templates/footer') ?>
