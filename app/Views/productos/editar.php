<?= view('templates/header', ['pageTitle' => 'Editar Lote #' . $producto['id'] . ' | MateriaX']) ?>

<div style="max-width: 760px; margin: 1rem auto;">
  <div style="margin-bottom: 1.25rem;">
    <a href="<?= site_url('productos') ?>" style="color: var(--text-secondary); font-size: 0.9rem;">
      &larr; Volver al inventario de polímeros
    </a>
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--text-primary); margin-top: 0.4rem;">
      Editar Lote: <?= esc($producto['nombre']) ?>
    </h1>
    <p style="color: var(--text-secondary); font-size: 0.95rem;">
      Modifica los datos técnicos, stock o precio del lote publicado.
    </p>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="<?= site_url('productos/actualizar/' . $producto['id']) ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
          <label for="nombre" class="form-label">Denominación del Material o Lote *</label>
          <input 
            type="text" 
            name="nombre" 
            id="nombre" 
            class="form-control" 
            value="<?= old('nombre', $producto['nombre']) ?>" 
            required
          >
          <span class="form-hint">Nombre técnico descriptivo del polímero o excedente (ej: pellet, scrap de inyección, granza reciclada).</span>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="tipo_polimero" class="form-label">Tipo de Polímero *</label>
            <select name="tipo_polimero" id="tipo_polimero" class="form-select" required>
              <?php $selectedPol = old('tipo_polimero', $producto['tipo_polimero']); ?>
              <option value="Polietileno (PE)" <?= ($selectedPol === 'Polietileno (PE)') ? 'selected' : '' ?>>Polietileno (PE / HDPE / LDPE)</option>
              <option value="Polipropileno (PP)" <?= ($selectedPol === 'Polipropileno (PP)') ? 'selected' : '' ?>>Polipropileno (PP)</option>
              <option value="PVC" <?= ($selectedPol === 'PVC') ? 'selected' : '' ?>>Policloruro de Vinilo (PVC)</option>
              <option value="ABS" <?= ($selectedPol === 'ABS') ? 'selected' : '' ?>>Acrilonitrilo Butadieno Estireno (ABS)</option>
              <option value="Nylon (PA)" <?= ($selectedPol === 'Nylon (PA)') ? 'selected' : '' ?>>Nylon / Poliamida (PA)</option>
              <option value="PET" <?= ($selectedPol === 'PET') ? 'selected' : '' ?>>Polietileno Tereftalato (PET)</option>
            </select>
            <span class="form-hint">Familia química según código de identificación de resinas (RIC).</span>
          </div>

          <div class="form-group">
            <label for="estado" class="form-label">Estado Comercial *</label>
            <select name="estado" id="estado" class="form-select" required>
              <?php $selectedEst = old('estado', $producto['estado']); ?>
              <option value="Disponible" <?= ($selectedEst === 'Disponible') ? 'selected' : '' ?>>Disponible</option>
              <option value="Reservado" <?= ($selectedEst === 'Reservado') ? 'selected' : '' ?>>Reservado</option>
              <option value="Vendido" <?= ($selectedEst === 'Vendido') ? 'selected' : '' ?>>Vendido</option>
            </select>
            <span class="form-hint">Disponibilidad para cotización o retiro inmediato.</span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="cantidad_kg" class="form-label">Volumen Disponible (en Kilogramos) *</label>
            <input 
              type="number" 
              step="0.01" 
              name="cantidad_kg" 
              id="cantidad_kg" 
              class="form-control" 
              value="<?= old('cantidad_kg', $producto['cantidad_kg']) ?>" 
              required
            >
            <span class="form-hint">Peso neto total en kg listo para pesaje en báscula y despacho logístico.</span>
          </div>

          <div class="form-group">
            <label for="precio_unitario" class="form-label">Precio Unitario ($ ARS por Kilogramo) *</label>
            <input 
              type="number" 
              step="0.01" 
              name="precio_unitario" 
              id="precio_unitario" 
              class="form-control" 
              value="<?= old('precio_unitario', $producto['precio_unitario']) ?>" 
              required
            >
            <span class="form-hint">Valor neto en pesos argentinos ($ ARS) por kilogramo, sin flete ni IVA.</span>
          </div>
        </div>

        <div class="form-group">
          <label for="ubicacion" class="form-label">Ubicación de Origen / Planta de Retiro *</label>
          <input 
            type="text" 
            name="ubicacion" 
            id="ubicacion" 
            class="form-control" 
            value="<?= old('ubicacion', $producto['ubicacion']) ?>" 
            required
          >
          <span class="form-hint">Planta fabril, parque industrial o depósito habilitado para la carga.</span>
        </div>

        <div class="form-group">
          <label for="descripcion" class="form-label">Ficha Técnica y Especificaciones del Lote</label>
          <textarea 
            name="descripcion" 
            id="descripcion" 
            class="form-textarea" 
            rows="4"
          ><?= old('descripcion', $producto['descripcion']) ?></textarea>
          <span class="form-hint">Detallar fluidez (MFI), coloración, tipo de molienda o pellet, pureza y presentación de carga.</span>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
          <button type="submit" class="btn btn-primary">
            Guardar Cambios
          </button>
          <a href="<?= site_url('productos') ?>" class="btn btn-secondary">
            Cancelar y Volver
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<?= view('templates/footer') ?>
