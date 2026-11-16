<?php
$p = $planta ?? [];
$selBascula = old('posee_bascula', $p['posee_bascula'] ?? '1');
$selCamiones = old('capacidad_carga_camiones', $p['capacidad_carga_camiones'] ?? 'Hasta 45 Ton (Semirremolque / Bitrén)');
$opcionesCamiones = [
    'Hasta 10 Ton (Camión Liviano / Chasis)',
    'Hasta 28 Ton (Chasis y Acoplado)',
    'Hasta 45 Ton (Semirremolque / Bitrén)',
    'Solo Furgón / Utilitario',
];
?>
<form action="<?= $action ?>" method="POST" class="stack-sm">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="nombre_planta" class="form-label">Denominación de la Sede <span class="required-indicator" aria-hidden="true">*</span></label>
        <input type="text" name="nombre_planta" id="nombre_planta" class="form-control" value="<?= esc(old('nombre_planta', $p['nombre_planta'] ?? '')) ?>" placeholder="Ej: Planta Fabril Central" required>
    </div>

    <div class="form-group">
        <label for="direccion" class="form-label">Dirección Física o Ruta <span class="required-indicator" aria-hidden="true">*</span></label>
        <input type="text" name="direccion" id="direccion" class="form-control" value="<?= esc(old('direccion', $p['direccion'] ?? '')) ?>" placeholder="Ej: Av. Savio 1420 / Ruta Provincial 6 km 3" required>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="localidad" class="form-label">Ciudad o Localidad <span class="required-indicator" aria-hidden="true">*</span></label>
            <input type="text" name="localidad" id="localidad" class="form-control" value="<?= esc(old('localidad', $p['localidad'] ?? '')) ?>" placeholder="Ej: Río Tercero" required>
        </div>
        <div class="form-group">
            <label for="provincia" class="form-label">Provincia <span class="required-indicator" aria-hidden="true">*</span></label>
            <input type="text" name="provincia" id="provincia" class="form-control" value="<?= esc(old('provincia', $p['provincia'] ?? 'Córdoba')) ?>" placeholder="Ej: Córdoba" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="posee_bascula" class="form-label">Báscula para Camiones</label>
            <select name="posee_bascula" id="posee_bascula" class="form-select">
                <option value="1"<?= ((string) $selBascula === '1') ? ' selected' : '' ?>>Sí — Dispone de balanza fiscal homologada</option>
                <option value="0"<?= ((string) $selBascula === '0') ? ' selected' : '' ?>>No dispone (remito o báscula externa)</option>
            </select>
        </div>
        <div class="form-group">
            <label for="capacidad_carga_camiones" class="form-label">Capacidad de Acceso</label>
            <select name="capacidad_carga_camiones" id="capacidad_carga_camiones" class="form-select">
                <?php foreach ($opcionesCamiones as $op): ?>
                    <option value="<?= esc($op) ?>"<?= ($selCamiones === $op) ? ' selected' : '' ?>><?= esc($op) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="contacto_responsable" class="form-label">Responsable de Logística</label>
            <input type="text" name="contacto_responsable" id="contacto_responsable" class="form-control" value="<?= esc(old('contacto_responsable', $p['contacto_responsable'] ?? '')) ?>" placeholder="Ej: Ing. Jorge Peralta">
        </div>
        <div class="form-group">
            <label for="telefono_planta" class="form-label">Teléfono Directo de Planta</label>
            <input type="text" name="telefono_planta" id="telefono_planta" class="form-control" value="<?= esc(old('telefono_planta', $p['telefono_planta'] ?? '')) ?>" placeholder="Ej: 03571-429988">
        </div>
    </div>

    <div class="form-group">
        <label for="coordenadas_gps" class="form-label">Coordenadas GPS (Opcional)</label>
        <input type="text" name="coordenadas_gps" id="coordenadas_gps" class="form-control" value="<?= esc(old('coordenadas_gps', $p['coordenadas_gps'] ?? '')) ?>" placeholder="Ej: -32.1732, -64.1141">
    </div>

    <div class="flex-row">
        <button type="submit" class="btn btn-primary"><?= esc($btnText ?? 'Guardar Planta') ?></button>
        <a href="<?= site_url('plantas') ?>" class="btn btn-secondary">Cancelar y Volver</a>
    </div>
</form>
