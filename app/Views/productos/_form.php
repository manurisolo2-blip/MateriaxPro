<?php
$p = $producto ?? [];
$selPol = old('tipo_polimero', $p['tipo_polimero'] ?? '');
$selEst = old('estado', $p['estado'] ?? 'Disponible');
$selPlanta = old('planta_id', $p['planta_id'] ?? '');
$polimeros = [
    'Polietileno (PE)'   => 'Polietileno (PE / HDPE / LDPE)',
    'Polipropileno (PP)' => 'Polipropileno (PP)',
    'PVC'                => 'Policloruro de Vinilo (PVC)',
    'ABS'                => 'Acrilonitrilo Butadieno Estireno (ABS)',
    'Nylon (PA)'         => 'Nylon / Poliamida (PA)',
    'PET'                => 'Polietileno Tereftalato (PET)',
];
?>
<form action="<?= $action ?>" method="POST" class="stack-sm">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="nombre" class="form-label">Denominación del Material o Lote <span class="required-indicator" aria-hidden="true">*</span></label>
        <input type="text" name="nombre" id="nombre" class="form-control" value="<?= esc(old('nombre', $p['nombre'] ?? '')) ?>" placeholder="Ej: Pellet Polietileno Alta Densidad (HDPE) Recuperado" required>
        <span class="form-hint">Nombre técnico descriptivo del polímero o excedente (pellet, scrap, granza reciclada).</span>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="tipo_polimero" class="form-label">Tipo de Polímero <span class="required-indicator" aria-hidden="true">*</span></label>
            <select name="tipo_polimero" id="tipo_polimero" class="form-select" required>
                <option value="">-- Seleccionar Polímero --</option>
                <?php foreach ($polimeros as $val => $lbl): ?>
                    <option value="<?= esc($val) ?>"<?= ($selPol === $val) ? ' selected' : '' ?>><?= esc($lbl) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="estado" class="form-label">Estado Comercial <span class="required-indicator" aria-hidden="true">*</span></label>
            <select name="estado" id="estado" class="form-select" required>
                <?php foreach (['Disponible', 'Reservado', 'Vendido'] as $st): ?>
                    <option value="<?= $st ?>"<?= ($selEst === $st) ? ' selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="cantidad_kg" class="form-label">Volumen Disponible (kg) <span class="required-indicator" aria-hidden="true">*</span></label>
            <input type="number" step="0.01" inputmode="decimal" name="cantidad_kg" id="cantidad_kg" class="form-control" value="<?= esc(old('cantidad_kg', $p['cantidad_kg'] ?? '')) ?>" placeholder="Ej: 1500" required>
        </div>
        <div class="form-group">
            <label for="precio_unitario" class="form-label">Precio Unitario ($ ARS/kg) <span class="required-indicator" aria-hidden="true">*</span></label>
            <input type="number" step="0.01" inputmode="decimal" name="precio_unitario" id="precio_unitario" class="form-control" value="<?= esc(old('precio_unitario', $p['precio_unitario'] ?? '')) ?>" placeholder="Ej: 1450.00" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="planta_id" class="form-label">Planta Fabril de Origen</label>
            <select name="planta_id" id="planta_id" class="form-select">
                <option value="">-- Sin planta asignada (ubicación libre) --</option>
                <?php foreach ($plantas ?? [] as $pl): ?>
                    <option value="<?= esc($pl['id']) ?>"<?= ((string) $selPlanta === (string) $pl['id']) ? ' selected' : '' ?>>
                        <?= esc($pl['nombre_planta']) ?> — <?= esc($pl['localidad']) ?>, <?= esc($pl['provincia']) ?> (<?= !empty($pl['posee_bascula']) ? 'Báscula Sí' : 'Sin Báscula' ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="form-hint"><a href="<?= site_url('plantas/crear') ?>" target="_blank">Registrar nueva sede</a></span>
        </div>
        <div class="form-group">
            <label for="ubicacion" class="form-label">Ubicación Visible / Ciudad <span class="required-indicator" aria-hidden="true">*</span></label>
            <input type="text" name="ubicacion" id="ubicacion" class="form-control" value="<?= esc(old('ubicacion', $p['ubicacion'] ?? '')) ?>" placeholder="Ej: Río Tercero, Córdoba" required>
        </div>
    </div>

    <div class="form-group">
        <label for="descripcion" class="form-label">Ficha Técnica y Especificaciones</label>
        <textarea name="descripcion" id="descripcion" class="form-textarea" rows="4" placeholder="Detalles sobre color, pureza, malla de molienda, humedad, fluidez MFI, envase..."><?= esc(old('descripcion', $p['descripcion'] ?? '')) ?></textarea>
    </div>

    <div class="flex-row">
        <button type="submit" class="btn btn-primary"><?= esc($btnText ?? 'Guardar') ?></button>
        <a href="<?= site_url('productos') ?>" class="btn btn-secondary">Cancelar y Volver</a>
    </div>
</form>
