<?= view('templates/header', ['pageTitle' => 'Ingresar | MateriaX']) ?>

<div class="container-sm">
    <div class="card stack-md">
        <div class="page-header">
            <h1 class="page-title">Ingresar a MateriaX</h1>
            <p class="page-sub">Acceda con las credenciales de su empresa registrada.</p>
        </div>
        <form action="<?= site_url('login') ?>" method="POST" class="stack-md">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico <span class="required-indicator">*</span></label>
                <input type="email" id="email" name="email" class="form-control" value="<?= esc(old('email') ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Contraseña <span class="required-indicator">*</span></label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="flex-between">
                <a href="<?= site_url('register') ?>" class="text-sec">¿No tiene cuenta? Registre su empresa</a>
                <button type="submit" class="btn btn-primary">Ingresar</button>
            </div>
        </form>
    </div>
</div>

<?= view('templates/footer') ?>
