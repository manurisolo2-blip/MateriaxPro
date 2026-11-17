<?php
$req         = !empty($required);
$selProv     = $selProv ?? '';
$selCiudad   = $selCiudad ?? '';
$fieldErrors = $fieldErrors ?? [];
?>
<div class="form-row">
    <div class="form-group">
        <label for="provincia" class="form-label">Provincia<?= $req ? ' <span class="required-indicator" aria-hidden="true">*</span>' : '' ?></label>
        <select name="provincia" id="provincia" class="form-select<?= !empty($fieldErrors['provincia']) ? ' is-invalid' : '' ?>"<?= $req ? ' required' : '' ?>>
            <option value="">-- Seleccionar provincia --</option>
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
        <?php if (!empty($fieldErrors['provincia'])): ?>
            <span class="field-error"><?= esc($fieldErrors['provincia']) ?></span>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label for="ciudad" class="form-label">Ciudad / Localidad<?= $req ? ' <span class="required-indicator" aria-hidden="true">*</span>' : '' ?></label>
        <div class="city-combobox" data-city-combobox>
            <input
                type="text"
                name="ciudad"
                id="ciudad"
                list="lista-localidades"
                class="form-control city-combobox-input<?= !empty($fieldErrors['ciudad']) ? ' is-invalid' : '' ?>"
                value="<?= esc($selCiudad) ?>"
                placeholder="<?= $selProv !== '' ? 'Escribí o elegí tu localidad...' : 'Primero elegí una provincia' ?>"
                autocomplete="off"
                <?= $req ? 'required' : '' ?>
            >
            <button type="button" id="ciudad-toggle" class="city-combobox-toggle" aria-label="Ver localidades disponibles" tabindex="-1">&#9662;</button>
            <ul id="ciudad-listbox" class="city-listbox" role="listbox" hidden></ul>
        </div>
        <datalist id="lista-localidades">
            <?php foreach ($todasLocalidades ?? [] as $loc): ?>
                <?php
                $locNombre = trim((string) ($loc['localidad'] ?? $loc['nombre'] ?? ''));
                $locProv   = trim((string) ($loc['provincia'] ?? ''));
                if ($locNombre === '' || preg_match('/^\d+$/', $locNombre)) {
                    continue;
                }
                ?>
                <option value="<?= esc($locNombre) ?>" data-provincia="<?= esc($locProv) ?>"><?= esc($locNombre) ?> (<?= esc($locProv) ?>)</option>
            <?php endforeach; ?>
        </datalist>
        <span class="form-hint" id="ciudad-hint">Elegí una provincia y seleccioná una localidad del listado.</span>
        <?php if (!empty($fieldErrors['ciudad'])): ?>
            <span class="field-error"><?= esc($fieldErrors['ciudad']) ?></span>
        <?php endif; ?>
    </div>
</div>
