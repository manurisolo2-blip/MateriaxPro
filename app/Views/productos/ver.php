<?= view('templates/header', ['pageTitle' => esc($producto['nombre']) . ' | MateriaX']) ?>

<div style="max-width: 850px; margin: 1.5rem auto;">
  <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <a href="<?= site_url('productos') ?>" style="color: var(--text-secondary); font-size: 0.9rem;">
        &larr; Volver al inventario general
      </a>
      <h1 style="font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-top: 0.35rem;">
        <?= esc($producto['nombre']) ?>
      </h1>
      <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 0.5rem;">
        <span class="badge badge-polimero <?= badge_polimero_class($producto['tipo_polimero']) ?>"><?= esc($producto['tipo_polimero']) ?></span>
        <?php 
          $badgeClass = 'badge-disponible';
          if ($producto['estado'] === 'Reservado') $badgeClass = 'badge-reservado';
          if ($producto['estado'] === 'Vendido') $badgeClass = 'badge-vendido';
        ?>
        <span class="badge <?= $badgeClass ?>"><?= esc($producto['estado']) ?></span>
        <span style="color: var(--text-muted); font-size: 0.85rem;">Publicado el <?= date('d/m/Y H:i', strtotime($producto['created_at'])) ?></span>
      </div>
    </div>

    <?php 
      $esPropio = ((int)($producto['user_id'] ?? 0) === (int)session()->get('user_id'));
      $esAdmin  = (session()->get('rol') === 'admin');
    ?>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
      <?php if ($esPropio || $esAdmin): ?>
        <a href="<?= site_url('productos/editar/' . $producto['id']) ?>" class="btn btn-secondary btn-sm" title="Editar ficha de lote">
          ✏ Editar Lote
        </a>
        <a href="<?= site_url('productos/confirmar-eliminar/' . $producto['id']) ?>" class="btn btn-danger btn-sm" title="Eliminar publicación">
          🗑 Dar de Baja
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Métricas Principales del Lote -->
  <div class="grid-3" style="margin-top: 0; margin-bottom: 1.5rem;">
    <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
      <span style="font-size: 0.82rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Volumen Total</span>
      <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-top: 0.25rem;">
        <?= number_format((float)$producto['cantidad_kg'], 0, ',', '.') ?> <span style="font-size: 1rem; font-weight: 500; color: var(--text-secondary);">kg</span>
      </div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
      <span style="font-size: 0.82rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Precio Unitario</span>
      <div style="font-size: 1.6rem; font-weight: 800; color: var(--color-success); margin-top: 0.25rem;">
        $<?= number_format((float)$producto['precio_unitario'], 2, ',', '.') ?> <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-secondary);">/ kg</span>
      </div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
      <span style="font-size: 0.82rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Valor Estimado Lote</span>
      <div style="font-size: 1.6rem; font-weight: 800; color: var(--color-accent); margin-top: 0.25rem;">
        $<?= number_format((float)$producto['cantidad_kg'] * (float)$producto['precio_unitario'], 2, ',', '.') ?>
      </div>
    </div>
  </div>

  <!-- Ficha Técnica -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Especificaciones Técnicas del Material</h3>
    </div>
    <div class="card-body">
      <div style="margin-bottom: 1.25rem;">
        <strong style="color: var(--text-secondary); font-size: 0.9rem;">Ubicación de Origen / Planta:</strong>
        <p style="font-size: 1.05rem; color: var(--text-primary); margin-top: 0.2rem;">📍 <?= esc($producto['ubicacion']) ?></p>
      </div>

      <div>
        <strong style="color: var(--text-secondary); font-size: 0.9rem;">Descripción y Control de Calidad:</strong>
        <div style="margin-top: 0.4rem; padding: 1rem; background-color: var(--bg-card); border-radius: var(--radius-md); border: 1px solid var(--border-color); color: var(--text-primary); white-space: pre-line; line-height: 1.6;">
          <?= !empty($producto['descripcion']) ? esc($producto['descripcion']) : '<em>Sin especificaciones adicionales registradas para este lote.</em>' ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Información de la Empresa Oferente -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Datos de Contacto Institucional</h3>
    </div>
    <div class="card-body">
      <div class="form-row">
        <div>
          <span style="font-size: 0.85rem; color: var(--text-muted);">Empresa Oferente:</span>
          <p style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">🏢 <?= esc($producto['empresa_nombre'] ?? 'Empresa Registrada') ?></p>
        </div>
        <div>
          <span style="font-size: 0.85rem; color: var(--text-muted);">Correo Electrónico:</span>
          <p style="font-size: 1.05rem; color: var(--color-accent);">✉ <?= esc($producto['empresa_email'] ?? 'No especificado') ?></p>
        </div>
        <div>
          <span style="font-size: 0.85rem; color: var(--text-muted);">Teléfono:</span>
          <p style="font-size: 1.05rem; color: var(--text-primary);">📞 <?= esc($producto['empresa_telefono'] ?? 'No especificado') ?></p>
        </div>
        <div>
          <span style="font-size: 0.85rem; color: var(--text-muted);">CUIT:</span>
          <p style="font-size: 1.05rem; color: var(--text-primary);">📄 <?= esc($producto['empresa_cuit'] ?? 'Sin CUIT') ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<?= view('templates/footer') ?>
