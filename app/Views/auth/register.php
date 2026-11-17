<?= view('templates/header', ['pageTitle' => 'Registro de Empresa | MateriaX']) ?>

<?php
$rubros = $rubros ?? \App\Controllers\BaseController::RUBROS_INDUSTRIALES;
$selRubro    = (string) (old('rubro') ?? '');
$selProv     = (string) (old('provincia') ?? ($selectedProvincia ?? ''));
$selCiudad   = (string) (old('ciudad') ?? ($selectedCiudad ?? ''));
$fieldErrors = session()->getFlashdata('errors') ?? [];
$fieldErrors = is_array($fieldErrors) ? $fieldErrors : [];
$errClass    = static fn (string $field): string => !empty($fieldErrors[$field]) ? ' is-invalid' : '';
?>

<div class="container-md">
    <div class="card register-card">
        <div class="card-header">
            <h1 class="page-title">Crear Cuenta Empresarial</h1>
            <p class="page-sub">Completá los datos reales de tu empresa o emprendimiento para operar en MateriaX. Todos los campos son obligatorios.</p>
        </div>

        <form action="<?= site_url('register') ?>" method="POST" class="card-body stack-lg">
            <?= csrf_field() ?>

            <fieldset class="register-section">
                <legend class="register-legend">Identidad de la empresa</legend>

                <div class="form-group">
                    <label for="nombre" class="form-label">Razón Social o Nombre Comercial <span class="required-indicator">*</span></label>
                    <input type="text" id="nombre" name="nombre" class="form-control<?= $errClass('nombre') ?>" value="<?= esc(old('nombre') ?? '') ?>" placeholder="Ej: Industrias Plásticas del Centro S.A." minlength="3" maxlength="100" autocomplete="organization" required>
                    <?php if (!empty($fieldErrors['nombre'])): ?>
                        <span class="field-error"><?= esc($fieldErrors['nombre']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cuit" class="form-label">CUIT o CUIL del Titular <span class="required-indicator">*</span></label>
                        <input type="text" id="cuit" name="cuit" class="form-control text-mono<?= $errClass('cuit') ?>" value="<?= esc(old('cuit') ?? '') ?>" placeholder="30-71234567-9" inputmode="numeric" minlength="11" maxlength="15" pattern="[0-9.\-\s]{11,15}" required>
                        <span class="form-hint">11 dígitos con o sin guiones. Se valida el dígito verificador oficial.</span>
                        <?php if (!empty($fieldErrors['cuit'])): ?>
                            <span class="field-error"><?= esc($fieldErrors['cuit']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="rubro" class="form-label">Rubro Industrial <span class="required-indicator">*</span></label>
                        <select id="rubro" name="rubro" class="form-select<?= $errClass('rubro') ?>" required>
                            <option value="">Seleccioná un rubro...</option>
                            <?php foreach ($rubros as $r): ?>
                                <option value="<?= esc($r) ?>"<?= $selRubro === $r ? ' selected' : '' ?>><?= esc($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($fieldErrors['rubro'])): ?>
                            <span class="field-error"><?= esc($fieldErrors['rubro']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </fieldset>

            <fieldset class="register-section">
                <legend class="register-legend">Contacto y ubicación fiscal</legend>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">Correo Electrónico <span class="required-indicator">*</span></label>
                        <input type="email" id="email" name="email" class="form-control<?= $errClass('email') ?>" value="<?= esc(old('email') ?? '') ?>" placeholder="compras@tuempresa.com.ar" maxlength="150" autocomplete="email" required>
                        <?php if (!empty($fieldErrors['email'])): ?>
                            <span class="field-error"><?= esc($fieldErrors['email']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="telefono" class="form-label">Teléfono de Contacto <span class="required-indicator">*</span></label>
                        <input type="tel" id="telefono" name="telefono" class="form-control<?= $errClass('telefono') ?>" value="<?= esc(old('telefono') ?? '') ?>" placeholder="Ej: 3571 421500 o +54 9 351 5551234" inputmode="tel" minlength="10" maxlength="30" autocomplete="tel" required>
                        <span class="form-hint">Incluí código de área (mínimo 10 dígitos reales, sin secuencias repetidas).</span>
                        <?php if (!empty($fieldErrors['telefono'])): ?>
                            <span class="field-error"><?= esc($fieldErrors['telefono']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <?= view('templates/localidades_fields', [
                    'provincias'       => $provincias ?? [],
                    'todasLocalidades' => $todasLocalidades ?? [],
                    'selProv'          => $selProv,
                    'selCiudad'        => $selCiudad,
                    'fieldErrors'      => $fieldErrors,
                    'required'         => true,
                ]) ?>

                <div class="form-group">
                    <label for="direccion" class="form-label">Domicilio Fiscal o de Planta <span class="required-indicator">*</span></label>
                    <input type="text" id="direccion" name="direccion" class="form-control<?= $errClass('direccion') ?>" value="<?= esc(old('direccion') ?? '') ?>" placeholder="Ej: Av. Savio 1420 / Parque Industrial Sur" minlength="5" maxlength="150" autocomplete="street-address" required>
                    <?php if (!empty($fieldErrors['direccion'])): ?>
                        <span class="field-error"><?= esc($fieldErrors['direccion']) ?></span>
                    <?php endif; ?>
                </div>
            </fieldset>

            <fieldset class="register-section">
                <legend class="register-legend">Seguridad de acceso</legend>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">Contraseña <span class="required-indicator">*</span></label>
                        <input type="password" id="password" name="password" class="form-control<?= $errClass('password') ?>" placeholder="Mínimo 8 caracteres" minlength="8" maxlength="100" autocomplete="new-password" required>
                        <span class="form-hint">Usá al menos 8 caracteres combinando letras y números.</span>
                        <?php if (!empty($fieldErrors['password'])): ?>
                            <span class="field-error"><?= esc($fieldErrors['password']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="pass_confirm" class="form-label">Confirmar Contraseña <span class="required-indicator">*</span></label>
                        <input type="password" id="pass_confirm" name="pass_confirm" class="form-control<?= $errClass('pass_confirm') ?>" placeholder="Repetí tu contraseña" minlength="8" maxlength="100" autocomplete="new-password" required>
                        <?php if (!empty($fieldErrors['pass_confirm'])): ?>
                            <span class="field-error"><?= esc($fieldErrors['pass_confirm']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </fieldset>

            <div class="register-actions flex-between">
                <p class="text-muted">Tu solicitud pasa por revisión de CUIT antes de habilitar publicaciones.</p>
                <div class="flex-row">
                    <a href="<?= site_url('login') ?>" class="btn btn-secondary">Ya tengo cuenta</a>
                    <button type="submit" class="btn btn-primary">Registrar Empresa</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?= view('templates/footer', ['pageScripts' => ['assets/localidades.js']], ['saveData' => false]) ?>
