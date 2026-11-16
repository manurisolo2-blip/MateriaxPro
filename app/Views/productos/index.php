<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Inventario de Polímeros']) ?>

<?php
$polimerosFiltro = ['Polietileno (PE)', 'Polipropileno (PP)', 'PVC', 'ABS', 'Nylon (PA)', 'PET'];
$uidActual = (int) session()->get('user_id');
$esAdmin = session()->get('rol') === 'admin';
?>

<div class="container-lg stack-md">
    <nav aria-label="Migas de pan" class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page">Mercado de Polímeros</span>
    </nav>

    <div class="page-header flex-between">
        <div>
            <h1 class="page-title">Inventario de Polímeros Industriales</h1>
            <p class="page-sub">Visualización de lotes de excedentes disponibles en la red.</p>
        </div>
        <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary">Publicar Lote</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="<?= site_url('productos') ?>" method="GET" class="flex-row">
                <div class="form-group" style="flex:2;min-width:200px;">
                    <label for="q" class="form-label">Buscar por nombre o ubicación</label>
                    <input type="text" name="q" id="q" class="form-control" placeholder="Ej: Pellet, Río Tercero..." value="<?= esc($busqueda ?? '') ?>">
                </div>
                <div class="form-group" style="flex:1;min-width:180px;">
                    <label for="polimero" class="form-label">Tipo de Polímero</label>
                    <select name="polimero" id="polimero" class="form-select">
                        <option value="">Todos los polímeros</option>
                        <?php foreach ($polimerosFiltro as $pol): ?>
                            <option value="<?= esc($pol) ?>"<?= (($filtroActual ?? '') === $pol) ? ' selected' : '' ?>><?= esc($pol) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-row">
                    <button type="submit" class="btn btn-secondary">Filtrar</button>
                    <?php if (!empty($busqueda) || !empty($filtroActual)): ?>
                        <a href="<?= site_url('productos') ?>" class="btn btn-secondary">Limpiar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th><th>Material / Lote</th><th>Polímero</th><th>Cantidad</th>
                        <th>Precio / Kg</th><th>Ubicación</th><th>Empresa Oferente</th><th>Estado</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $p): ?>
                            <?php $esPropio = ((int) ($p['user_id'] ?? 0) === $uidActual); ?>
                            <tr>
                                <td class="text-muted"><strong>#<?= esc($p['id']) ?></strong></td>
                                <td>
                                    <a href="<?= site_url('productos/ver/' . $p['id']) ?>"><strong><?= esc($p['nombre']) ?></strong></a>
                                    <?php if ($esPropio): ?><span class="badge badge-info">Mi Lote</span><?php endif; ?>
                                </td>
                                <td><span class="badge badge-polimero <?= badge_polimero_class($p['tipo_polimero']) ?>"><?= esc($p['tipo_polimero']) ?></span></td>
                                <td><strong><?= format_kg($p['cantidad_kg']) ?></strong></td>
                                <td><?= format_precio($p['precio_unitario']) ?></td>
                                <td><?= esc($p['ubicacion']) ?></td>
                                <td><?= esc($p['empresa_nombre'] ?? 'Empresa Registrada') ?></td>
                                <td><span class="badge <?= badge_estado_class($p['estado']) ?>"><?= esc($p['estado']) ?></span></td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <a href="<?= site_url('productos/ver/' . $p['id']) ?>" class="btn btn-secondary btn-sm">Ver</a>
                                    <?php if ($esPropio || $esAdmin): ?>
                                        <a href="<?= site_url('productos/editar/' . $p['id']) ?>" class="btn btn-secondary btn-sm">Editar</a>
                                        <a href="<?= site_url('productos/confirmar-eliminar/' . $p['id']) ?>" class="btn btn-danger btn-sm">Eliminar</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="empty-state">
                                <?php if (!empty($busqueda) || !empty($filtroActual)): ?>
                                    <h3 class="empty-state-title">No se encontraron lotes con los filtros aplicados</h3>
                                    <p class="empty-state-desc">Intenta modificando los términos de búsqueda o restableciendo los filtros de polímero.</p>
                                    <a href="<?= site_url('productos') ?>" class="btn btn-secondary btn-sm">Limpiar Filtros de Búsqueda</a>
                                <?php else: ?>
                                    <h3 class="empty-state-title">No hay lotes de polímeros registrados actualmente</h3>
                                    <p class="empty-state-desc">Sé el primero en circular excedentes o mermas plásticas para la red industrial.</p>
                                    <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary btn-sm">Publicar Primer Lote</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
