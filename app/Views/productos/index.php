<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Inventario de Polímeros']) ?>

<!-- Migas de Pan (WCAG Breadcrumbs) -->
<nav aria-label="Migas de pan" class="breadcrumb">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page">Mercado de Polímeros</span>
</nav>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--text-primary);">Inventario de Polímeros Industriales</h1>
    <p style="color: var(--text-secondary); font-size: 0.95rem;">
      Módulo funcional protegido: visualización de lotes de excedentes disponibles en la red.
    </p>
  </div>
  <div>
    <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary">
      Publicar Lote
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
          <th scope="col">ID</th>
          <th scope="col">Material / Lote</th>
          <th scope="col">Polímero</th>
          <th scope="col">Cantidad</th>
          <th scope="col">Precio / Kg</th>
          <th scope="col">Ubicación</th>
          <th scope="col">Empresa Oferente</th>
          <th scope="col">Estado</th>
          <th scope="col" style="text-align: right;">Acciones</th>
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
              <td><strong><?= format_kg($p['cantidad_kg']) ?></strong></td>
              <td><?= format_precio($p['precio_unitario']) ?></td>
              <td><?= esc($p['ubicacion']) ?></td>
              <td><?= esc($p['empresa_nombre'] ?? 'Empresa Registrada') ?></td>
              <td>
                <span class="badge <?= badge_estado_class($p['estado']) ?>"><?= esc($p['estado']) ?></span>
              </td>
              <td style="text-align: right; white-space: nowrap;">
                <div style="display: inline-flex; gap: 0.3rem; align-items: center;">
                  <a href="<?= site_url('productos/ver/' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Ver detalles y contacto">
                    Ver
                  </a>
                  <?php if ($esPropio || $esAdmin): ?>
                    <a href="<?= site_url('productos/editar/' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Editar lote propio">
                      Editar
                    </a>
                    <a href="<?= site_url('productos/confirmar-eliminar/' . $p['id']) ?>" class="btn btn-danger btn-sm" title="Eliminar este lote">
                      Eliminar
                    </a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" style="padding: 0;">
              <div class="empty-state">
                <?php if (!empty($busqueda) || !empty($filtroActual)): ?>
                  <h3 class="empty-state-title">
                    No se encontraron lotes con los filtros aplicados
                  </h3>
                  <p class="empty-state-desc">
                    Intenta modificando los términos de búsqueda o restableciendo los filtros de polímero para ver todo el inventario circular.
                  </p>
                  <a href="<?= site_url('productos') ?>" class="btn btn-secondary btn-sm">
                    Limpiar Filtros de Búsqueda
                  </a>
                <?php else: ?>
                  <h3 class="empty-state-title">
                    No hay lotes de polímeros registrados actualmente
                  </h3>
                  <p class="empty-state-desc">
                    Sé el primero en circular excedentes o mermas plásticas para la red industrial.
                  </p>
                  <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary btn-sm">
                    Publicar Primer Lote
                  </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= view('templates/footer') ?>
