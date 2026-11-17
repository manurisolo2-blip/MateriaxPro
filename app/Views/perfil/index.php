<?= view('templates/header', ['pageTitle' => 'Mi Cuenta Empresarial | MateriaX']) ?>

<div class="container-lg stack-md">
    <div class="page-header">
        <h1 class="page-title"><?= esc($user['nombre']) ?></h1>
        <p class="page-sub">Administración de cuenta empresarial, credenciales de seguridad y publicaciones activas en la red.</p>
    </div>

    <div class="grid-3">
        <div class="metric-card">
            <span class="metric-card-label">Lotes Publicados</span>
            <div class="metric-card-value"><?= count($misProductos) ?></div>
            <span class="metric-card-sub">En inventario circular</span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Miembro Desde</span>
            <div class="metric-card-value"><?= date('d/m/Y', strtotime($user['created_at'])) ?></div>
            <span class="metric-card-sub">Cuenta Homologada</span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Último Acceso Registrado</span>
            <div class="metric-card-value"><?= !empty($user['ultimo_login']) ? date('d/m/Y H:i', strtotime($user['ultimo_login'])) : 'Sesión actual' ?></div>
            <span class="metric-card-sub">Conexión Segura</span>
        </div>
    </div>

    <div class="grid-2">
        <div class="stack-md">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Datos de la Empresa</h3></div>
                <div class="card-body">
                    <form action="<?= site_url('perfil/actualizar') ?>" method="POST" class="stack-sm">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="nombre" class="form-label">Razón Social *</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" value="<?= esc(old('nombre', $user['nombre'])) ?>" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cuit" class="form-label">CUIT *</label>
                                <input type="text" name="cuit" id="cuit" class="form-control" value="<?= esc(old('cuit', $user['cuit'])) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="rubro" class="form-label">Rubro Productivo *</label>
                                <input type="text" name="rubro" id="rubro" class="form-control" value="<?= esc(old('rubro', $user['rubro'] ?? 'Sector Plástico')) ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Correo Electrónico Corporativo</label>
                            <input type="email" class="form-control" value="<?= esc($user['email']) ?>" disabled>
                            <p class="form-hint">El correo electrónico institucional no puede modificarse por políticas de auditoría.</p>
                        </div>
                        <div class="form-group">
                            <label for="telefono" class="form-label">Teléfono de Contacto *</label>
                            <input type="text" name="telefono" id="telefono" class="form-control" value="<?= esc(old('telefono', $user['telefono'])) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="direccion" class="form-label">Domicilio de Planta / Sede *</label>
                            <input type="text" name="direccion" id="direccion" class="form-control" value="<?= esc(old('direccion', $user['direccion'] ?? '')) ?>" required>
                        </div>

                        <?= view('templates/localidades_fields', [
                            'provincias'       => $provincias ?? [],
                            'todasLocalidades' => $todasLocalidades ?? [],
                            'selProv'          => old('provincia', $userProv ?? $user['provincia'] ?? 'Córdoba'),
                            'selCiudad'        => old('ciudad', $userCiudad ?? $user['ciudad'] ?? ''),
                            'fieldErrors'      => is_array($errors ?? null) ? $errors : [],
                            'required'         => true,
                        ]) ?>

                        <button type="submit" class="btn btn-primary btn-block">Guardar Cambios</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Seguridad y Contraseña</h3></div>
                <div class="card-body">
                    <form action="<?= site_url('perfil/cambiar-password') ?>" method="POST" class="stack-sm">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="current_password" class="form-label">Contraseña Actual *</label>
                            <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Ingresa tu contraseña actual" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password" class="form-label">Nueva Contraseña *</label>
                                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repite la nueva clave" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-secondary btn-block">Actualizar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between">
                <h3 class="card-title">Mis Lotes de Material</h3>
                <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary btn-sm">Publicar Lote</a>
            </div>
            <div class="card-body stack-sm">
                <?php if (!empty($misProductos)): ?>
                    <?php foreach ($misProductos as $p): ?>
                        <div class="card flex-between">
                            <div>
                                <a href="<?= site_url('productos/ver/' . $p['id']) ?>"><strong><?= esc($p['nombre']) ?></strong></a>
                                <div class="text-sec">
                                    <span class="badge badge-polimero <?= badge_polimero_class($p['tipo_polimero']) ?>"><?= esc($p['tipo_polimero']) ?></span>
                                    · <strong><?= format_kg($p['cantidad_kg']) ?></strong>
                                    · <?= format_precio($p['precio_unitario']) ?> / kg
                                </div>
                            </div>
                            <div class="flex-row">
                                <a href="<?= site_url('productos/editar/' . $p['id']) ?>" class="btn btn-secondary btn-sm">Editar</a>
                                <a href="<?= site_url('productos/ver/' . $p['id']) ?>" class="btn btn-secondary btn-sm">Ver</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>Aún no has registrado ningún lote de polímero.</p>
                        <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary btn-sm">Publicar Primer Excedente</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= view('templates/footer', ['pageScripts' => ['assets/localidades.js']], ['saveData' => false]) ?>
