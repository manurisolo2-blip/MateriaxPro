<?= view('templates/header', ['pageTitle' => 'Registro de Empresa | MateriaX']) ?>

<?php
$rubros = [
    'Inyección de Plásticos',
    'Extrusión y Soplado',
    'Rotomoldeo',
    'Termoformado',
    'Reciclador / Recuperador',
    'Automotriz y Autopartes',
    'Construcción y Tuberías',
    'Envases y Packaging',
    'Otro / Distribuidor Industrial',
];
$selRubro = old('rubro') ?? '';
$selProv  = old('provincia') ?? ($selectedProvincia ?? '');
$selCiudad = old('ciudad') ?? ($selectedCiudad ?? '');
?>

<div class="container-md">
    <div class="card stack-md">
        <div class="page-header">
            <h1 class="page-title">Alta de Empresa Industrial</h1>
            <p class="page-sub">El CUIT y correo fiscal quedan registrados para auditar cada operación y emitir certificados ambientales.</p>
        </div>

        <div class="alert alert-info">
            Verifique que la razón social y el CUIT coincidan con su constancia fiscal antes de enviar la solicitud.
        </div>

        <form action="<?= site_url('register') ?>" method="POST" class="stack-md">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre" class="form-label">Razón Social / Empresa <span class="required-indicator">*</span></label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="<?= esc(old('nombre') ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="cuit" class="form-label">CUIT <span class="required-indicator">*</span></label>
                    <input type="text" id="cuit" name="cuit" class="form-control" placeholder="30-71234567-9" value="<?= esc(old('cuit') ?? '') ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="rubro" class="form-label">Rubro Industrial <span class="required-indicator">*</span></label>
                    <select id="rubro" name="rubro" class="form-select" required>
                        <option value="">Seleccione un rubro...</option>
                        <?php foreach ($rubros as $r): ?>
                            <option value="<?= esc($r) ?>"<?= $selRubro === $r ? ' selected' : '' ?>><?= esc($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Correo Corporativo <span class="required-indicator">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= esc(old('email') ?? '') ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="telefono" class="form-label">Teléfono de Contacto</label>
                    <input type="text" id="telefono" name="telefono" class="form-control" value="<?= esc(old('telefono') ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="direccion" class="form-label">Dirección Fiscal</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" value="<?= esc(old('direccion') ?? '') ?>">
                </div>
            </div>

            <?= view('templates/localidades_fields', [
                'provincias'       => $provincias ?? [],
                'todasLocalidades' => $todasLocalidades ?? [],
                'selProv'          => $selProv,
                'selCiudad'        => $selCiudad,
                'required'         => false,
            ]) ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña <span class="required-indicator">*</span></label>
                    <input type="password" id="password" name="password" class="form-control" minlength="6" required>
                </div>
                <div class="form-group">
                    <label for="pass_confirm" class="form-label">Confirmar Contraseña <span class="required-indicator">*</span></label>
                    <input type="password" id="pass_confirm" name="pass_confirm" class="form-control" minlength="6" required>
                </div>
            </div>
            <div class="flex-between">
                <a href="<?= site_url('login') ?>" class="text-sec">¿Ya tiene cuenta? Ingresar</a>
                <button type="submit" class="btn btn-primary">Registrar Empresa</button>
            </div>
        </form>
    </div>
</div>

<?= view('templates/footer') ?>
