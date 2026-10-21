<?php
  $p = $planta ?? [];
  $selBascula = old('posee_bascula', $p['posee_bascula'] ?? '1');
  $selCamiones = old('capacidad_carga_camiones', $p['capacidad_carga_camiones'] ?? 'Hasta 45 Ton (Semirremolque / Bitrén)');
  
  $opcionesCamiones = [
    'Hasta 10 Ton (Camión Liviano / Chasis)' => 'Hasta 10 Ton (Camión Liviano / Chasis)',
    'Hasta 28 Ton (Chasis y Acoplado)'       => 'Hasta 28 Ton (Chasis y Acoplado)',
    'Hasta 45 Ton (Semirremolque / Bitrén)'  => 'Hasta 45 Ton (Semirremolque / Bitrén)',
    'Solo Furgón / Utilitario'               => 'Solo Furgón / Utilitario',
  ];
?>

<form action="<?= $action ?>" method="POST">
  <?= csrf_field() ?>

  <div class="form-group">
    <label for="nombre_planta" class="form-label">Denominación o Nombre de la Sede <span class="required-indicator" aria-hidden="true">*</span></label>
    <input type="text" name="nombre_planta" id="nombre_planta" class="form-control" value="<?= old('nombre_planta', $p['nombre_planta'] ?? '') ?>" placeholder="Ej: Planta Fabril Central / Depósito Parque Industrial" required>
    <span class="form-hint">Nombre de referencia para identificar el predio o depósito en las publicaciones.</span>
  </div>

  <div class="form-group">
    <label for="direccion" class="form-label">Dirección Física o Ruta <span class="required-indicator" aria-hidden="true">*</span></label>
    <input type="text" name="direccion" id="direccion" class="form-control" value="<?= old('direccion', $p['direccion'] ?? '') ?>" placeholder="Ej: Av. Savio 1420 / Ruta Provincial 6 km 3" required>
    <span class="form-hint">Domicilio de carga al que accederá el transportista.</span>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="localidad" class="form-label">Ciudad o Localidad <span class="required-indicator" aria-hidden="true">*</span></label>
      <input type="text" name="localidad" id="localidad" class="form-control" value="<?= old('localidad', $p['localidad'] ?? '') ?>" placeholder="Ej: Río Tercero" required>
    </div>

    <div class="form-group">
      <label for="provincia" class="form-label">Provincia <span class="required-indicator" aria-hidden="true">*</span></label>
      <input type="text" name="provincia" id="provincia" class="form-control" value="<?= old('provincia', $p['provincia'] ?? 'Córdoba') ?>" placeholder="Ej: Córdoba" required>
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="posee_bascula" class="form-label">Báscula para Camiones</label>
      <select name="posee_bascula" id="posee_bascula" class="form-select">
        <option value="1" <?= ($selBascula == '1') ? 'selected' : '' ?>>Sí &mdash; Dispone de balanza fiscal de camiones homologada</option>
        <option value="0" <?= ($selBascula == '0') ? 'selected' : '' ?>>No dispone (pesaje por remito previo o báscula externa)</option>
      </select>
      <span class="form-hint">Indica si el pesaje se puede realizar in situ en la planta.</span>
    </div>

    <div class="form-group">
      <label for="capacidad_carga_camiones" class="form-label">Capacidad de Carga y Acceso</label>
      <select name="capacidad_carga_camiones" id="capacidad_carga_camiones" class="form-select">
        <?php foreach ($opcionesCamiones as $val => $lbl): ?>
          <option value="<?= $val ?>" <?= ($selCamiones === $val) ? 'selected' : '' ?>><?= $lbl ?></option>
        <?php endforeach; ?>
      </select>
      <span class="form-hint">Tipo de vehículo pesado con maniobrabilidad en el predio.</span>
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="contacto_responsable" class="form-label">Responsable de Carga o Logística</label>
      <input type="text" name="contacto_responsable" id="contacto_responsable" class="form-control" value="<?= old('contacto_responsable', $p['contacto_responsable'] ?? '') ?>" placeholder="Ej: Ing. Jorge Peralta">
      <span class="form-hint">Persona de contacto para coordinar el turno de retiro.</span>
    </div>

    <div class="form-group">
      <label for="telefono_planta" class="form-label">Teléfono Directo de Planta</label>
      <input type="text" name="telefono_planta" id="telefono_planta" class="form-control" value="<?= old('telefono_planta', $p['telefono_planta'] ?? '') ?>" placeholder="Ej: 03571-429988">
      <span class="form-hint">Línea directa o guardia de logística.</span>
    </div>
  </div>

  <div class="form-group">
    <label for="coordenadas_gps" class="form-label">Coordenadas GPS (Opcional)</label>
    <input type="text" name="coordenadas_gps" id="coordenadas_gps" class="form-control" value="<?= old('coordenadas_gps', $p['coordenadas_gps'] ?? '') ?>" placeholder="Ej: -32.1732, -64.1141">
    <span class="form-hint">Coordenadas de latitud y longitud para geolocalización y cálculo de flete.</span>
  </div>

  <div style="display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
    <button type="submit" class="btn btn-primary"><?= $btnText ?? 'Guardar Planta' ?></button>
    <a href="<?= site_url('plantas') ?>" class="btn btn-secondary">Cancelar y Volver</a>
  </div>
</form>
