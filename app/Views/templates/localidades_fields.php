<?php
$req = !empty($required);
$selProv = $selProv ?? '';
$selCiudad = $selCiudad ?? '';
?>
<div class="form-row">
    <div class="form-group">
        <label for="provincia" class="form-label">Provincia<?= $req ? ' <span class="required-indicator" aria-hidden="true">*</span>' : '' ?></label>
        <select name="provincia" id="provincia" class="form-select"<?= $req ? ' required' : '' ?>>
            <option value="">-- Seleccionar Provincia --</option>
            <?php foreach ($provincias ?? [] as $prov): ?>
                <?php
                $pNombre = is_array($prov) ? ($prov['provincia'] ?? $prov['nombre'] ?? '') : (string) $prov;
                $pId = is_array($prov) ? ($prov['id'] ?? '') : '';
                ?>
                <option value="<?= esc($pNombre) ?>"<?= $pId !== '' ? ' data-id="' . esc($pId) . '"' : '' ?><?= ($selProv === $pNombre) ? ' selected' : '' ?>>
                    <?= esc($pNombre) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="ciudad" class="form-label">Ciudad / Localidad<?= $req ? ' <span class="required-indicator" aria-hidden="true">*</span>' : '' ?></label>
        <input type="text" name="ciudad" id="ciudad" list="lista-localidades" class="form-control" value="<?= esc($selCiudad) ?>" placeholder="Escriba para buscar localidad (ej: Río Tercero)..." autocomplete="off"<?= $req ? ' required' : '' ?>>
        <datalist id="lista-localidades">
            <?php foreach ($todasLocalidades ?? [] as $loc): ?>
                <?php $locNombre = $loc['localidad'] ?? $loc['nombre'] ?? ''; ?>
                <option value="<?= esc($locNombre) ?>" data-provincia="<?= esc($loc['provincia'] ?? '') ?>"><?= esc($locNombre) ?> (<?= esc($loc['provincia'] ?? '') ?>)</option>
            <?php endforeach; ?>
        </datalist>
    </div>
</div>
