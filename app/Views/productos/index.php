<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Inventario de Polímeros']) ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--text-primary);">Inventario de Polímeros Industriales</h1>
    <p style="color: var(--text-secondary); font-size: 0.95rem;">
      Módulo funcional protegido: visualización de lotes de excedentes disponibles en la red.
    </p>
  </div>
  <div>
    <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary">
      ➕ Publicar Nuevo Lote
    </a>
  </div>
</div>

<!-- Barra de Filtros y Búsqueda (GET nativo) -->
<div class="card" style="margin-bottom: 1.5rem;">
  <div class="card-body" style="padding: 1rem 1.25rem;">
    <form action="<?= site_url('productos') ?>" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
      <div style="flex: 2; min-width: 200px;">
        <label for="q" class="form-label">Buscar por nombre o ubicación</label>
        <input 
          type="text" 
          name="q" 
          id="q" 
          class="form-control" 
          placeholder="Ej: Pellet, Río Tercero..." 
          value="<?= esc($busqueda ?? '') ?>"
        >
      </div>

      <div style="flex: 1; min-width: 180px;">
        <label for="polimero" class="form-label">Tipo de Polímero</label>
        <select name="polimero" id="polimero" class="form-select">
          <option value="">Todos los polímeros</option>
          <option value="Polietileno (PE)" <?= ($filtroActual === 'Polietileno (PE)') ? 'selected' : '' ?>>Polietileno (PE)</option>
          <option value="Polipropileno (PP)" <?= ($filtroActual === 'Polipropileno (PP)') ? 'selected' : '' ?>>Polipropileno (PP)</option>
          <option value="PVC" <?= ($filtroActual === 'PVC') ? 'selected' : '' ?>>PVC</option>
          <option value="ABS" <?= ($filtroActual === 'ABS') ? 'selected' : '' ?>>ABS</option>
          <option value="Nylon (PA)" <?= ($filtroActual === 'Nylon (PA)') ? 'selected' : '' ?>>Nylon (PA)</option>
          <option value="PET" <?= ($filtroActual === 'PET') ? 'selected' : '' ?>>PET</option>
        </select>
      </div>

      <div>
        <button type="submit" class="btn btn-secondary">Filtrar</button>
        <?php if (!empty($busqueda) || !empty($filtroActual)): ?>
          <a href="<?= site_url('productos') ?>" class="btn btn-secondary" title="Limpiar filtros">Limpiar</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Tabla del Módulo Funcional (CRUD) -->
<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Material / Lote</th>
          <th>Polímero</th>
          <th>Cantidad</th>
          <th>Precio / Kg</th>
          <th>Ubicación</th>
          <th>Empresa Oferente</th>
          <th>Estado</th>
          <th style="text-align: right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($productos) && count($productos) > 0): ?>
          <?php foreach ($productos as $p): ?>
            <?php 
              $esPropio = ((int)($p['user_id'] ?? 0) === (int)session()->get('user_id'));
              $esAdmin  = (session()->get('rol') === 'admin');
            ?>
            <tr style="<?= $esPropio ? 'background-color: rgba(20, 184, 166, 0.05);' : '' ?>">
              <td><strong style="color: var(--text-muted);">#<?= esc($p['id']) ?></strong></td>
              <td>
                <a href="<?= site_url('productos/ver/' . $p['id']) ?>" style="font-weight: 700; color: var(--text-primary);">
                  <?= esc($p['nombre']) ?>
                </a>
                <?php if ($esPropio): ?>
                  <span class="badge" style="background: #f0fdfa; color: #0f766e; border: 1px solid #99f6e4; font-size: 0.72rem; margin-left: 0.35rem;">
                    Mi Lote
                  </span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge badge-polimero <?= badge_polimero_class($p['tipo_polimero']) ?>"><?= esc($p['tipo_polimero']) ?></span>
              </td>
              <td><strong><?= number_format((float)$p['cantidad_kg'], 0, ',', '.') ?></strong> kg</td>
              <td>$<?= number_format((float)$p['precio_unitario'], 2, ',', '.') ?></td>
              <td><?= esc($p['ubicacion']) ?></td>
              <td><?= esc($p['empresa_nombre'] ?? 'Empresa Registrada') ?></td>
              <td>
                <?php 
                  $badgeClass = 'badge-disponible';
                  if ($p['estado'] === 'Reservado') $badgeClass = 'badge-reservado';
                  if ($p['estado'] === 'Vendido') $badgeClass = 'badge-vendido';
                ?>
                <span class="badge <?= $badgeClass ?>"><?= esc($p['estado']) ?></span>
              </td>
              <td style="text-align: right; white-space: nowrap;">
                <div style="display: inline-flex; gap: 0.3rem; align-items: center;">
                  <a href="<?= site_url('productos/ver/' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Ver detalles y contacto">
                    👁 Ver
                  </a>
                  <?php if ($esPropio || $esAdmin): ?>
                    <a href="<?= site_url('productos/editar/' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Editar lote propio">
                      ✏ Editar
                    </a>
                    <a href="<?= site_url('productos/confirmar-eliminar/' . $p['id']) ?>" class="btn btn-danger btn-sm" title="Eliminar este lote">
                      🗑
                    </a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" style="text-align: center; padding: 3rem 1.5rem; color: var(--text-secondary);">
              <?php if (!empty($busqueda) || !empty($filtroActual)): ?>
                <p style="font-size: 1.1rem; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary);">
                  No se encontraron lotes de polímeros que coincidan con los filtros aplicados.
                </p>
                <p style="font-size: 0.92rem; margin-bottom: 1.25rem;">
                  Intenta modificando los términos de búsqueda o restableciendo los filtros de polímero.
                </p>
                <a href="<?= site_url('productos') ?>" class="btn btn-secondary btn-sm">
                  Limpiar Filtros de Búsqueda
                </a>
              <?php else: ?>
                <p style="font-size: 1.1rem; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary);">
                  No hay lotes de polímeros disponibles en el inventario actualmente.
                </p>
                <p style="font-size: 0.92rem; margin-bottom: 1.25rem;">
                  Sé el primero en circular excedentes o mermas plásticas para la industria de la red.
                </p>
                <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary btn-sm">
                  ➕ Publicar Primer Lote
                </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= view('templates/footer') ?>
