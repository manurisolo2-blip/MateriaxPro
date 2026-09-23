<?= view('templates/header', ['pageTitle' => 'Publicar Excedente Industrial | MateriaX']) ?>

<div style="max-width: 760px; margin: 1rem auto;">
  <div style="margin-bottom: 1.25rem;">
    <a href="<?= site_url('productos') ?>" style="color: var(--text-secondary); font-size: 0.9rem;">
      &larr; Volver al inventario de polímeros
    </a>
    <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--text-primary); margin-top: 0.4rem;">
      Publicar Nuevo Lote de Material
    </h1>
    <p style="color: var(--text-secondary); font-size: 0.95rem;">
      Ingresa los datos técnicos del excedente polimérico para ofrecerlo a industrias de la red.
    </p>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="<?= site_url('productos/guardar') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
          <label for="nombre" class="form-label">Denominación del Material o Lote *</label>
          <input 
            type="text" 
            name="nombre" 
            id="nombre" 
            class="form-control" 
            value="<?= old('nombre') ?>" 
            placeholder="Ej: Pellet Polietileno Alta Densidad (HDPE) Recuperado" 
            required
          >
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="tipo_polimero" class="form-label">Tipo de Polímero *</label>
            <select name="tipo_polimero" id="tipo_polimero" class="form-select" required>
              <option value="">-- Seleccionar Polímero --</option>
              <option value="Polietileno (PE)" <?= (old('tipo_polimero') === 'Polietileno (PE)') ? 'selected' : '' ?>>Polietileno (PE / HDPE / LDPE)</option>
              <option value="Polipropileno (PP)" <?= (old('tipo_polimero') === 'Polipropileno (PP)') ? 'selected' : '' ?>>Polipropileno (PP)</option>
              <option value="PVC" <?= (old('tipo_polimero') === 'PVC') ? 'selected' : '' ?>>Policloruro de Vinilo (PVC)</option>
              <option value="ABS" <?= (old('tipo_polimero') === 'ABS') ? 'selected' : '' ?>>Acrilonitrilo Butadieno Estireno (ABS)</option>
              <option value="Nylon (PA)" <?= (old('tipo_polimero') === 'Nylon (PA)') ? 'selected' : '' ?>>Nylon / Poliamida (PA)</option>
              <option value="PET" <?= (old('tipo_polimero') === 'PET') ? 'selected' : '' ?>>Polietileno Tereftalato (PET)</option>
            </select>
          </div>

          <div class="form-group">
            <label for="estado" class="form-label">Estado Comercial *</label>
            <select name="estado" id="estado" class="form-select" required>
              <option value="Disponible" <?= (old('estado', 'Disponible') === 'Disponible') ? 'selected' : '' ?>>Disponible</option>
              <option value="Reservado" <?= (old('estado') === 'Reservado') ? 'selected' : '' ?>>Reservado</option>
              <option value="Vendido" <?= (old('estado') === 'Vendido') ? 'selected' : '' ?>>Vendido</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="cantidad_kg" class="form-label">Cantidad Disponible (en Kilogramos) *</label>
            <input 
              type="number" 
              step="0.01" 
              name="cantidad_kg" 
              id="cantidad_kg" 
              class="form-control" 
              value="<?= old('cantidad_kg') ?>" 
              placeholder="Ej: 1500" 
              required
            >
          </div>

          <div class="form-group">
            <label for="precio_unitario" class="form-label">Precio Unitario ($ por Kg) *</label>
            <input 
              type="number" 
              step="0.01" 
              name="precio_unitario" 
              id="precio_unitario" 
              class="form-control" 
              value="<?= old('precio_unitario') ?>" 
              placeholder="Ej: 1450.00" 
              required
            >
          </div>
        </div>

        <div class="form-group">
          <label for="ubicacion" class="form-label">Ubicación de Origen / Planta Industrial *</label>
          <input 
            type="text" 
            name="ubicacion" 
            id="ubicacion" 
            class="form-control" 
            value="<?= old('ubicacion') ?>" 
            placeholder="Ej: Parque Industrial Río Tercero, Córdoba" 
            required
          >
        </div>

        <div class="form-group">
          <label for="descripcion" class="form-label">Ficha Técnica y Descripción del Lote</label>
          <textarea 
            name="descripcion" 
            id="descripcion" 
            class="form-textarea" 
            rows="4" 
            placeholder="Detalles sobre color, pureza, malla de molienda, humedad, envase (big bags / tambores), etc."
          ><?= old('descripcion') ?></textarea>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
          <button type="submit" class="btn btn-primary">
            ✓ Publicar Lote en la Red
          </button>
          <a href="<?= site_url('productos') ?>" class="btn btn-secondary">
            Cancelar
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<?= view('templates/footer') ?>
