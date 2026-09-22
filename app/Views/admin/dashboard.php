<?= view('templates/header', ['pageTitle' => 'Panel de Administración Exclusivo | MateriaX']) ?>

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <span style="font-size: 0.85rem; color: #14b8a6; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
      🛡️ PANEL DE CONTROL ADMINISTRATIVO EXCLUSIVO
    </span>
    <h1 style="font-size: 2.2rem; font-weight: 800; color: #ffffff; margin-top: 0.25rem;">
      Auditoría y Gestión de la Red MateriaX
    </h1>
    <p style="color: var(--text-secondary); font-size: 0.95rem;">
      Supervisión de empresas registradas, validación de estado fiscal y monitoreo global de lotes circulares.
    </p>
  </div>
  <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
    <a href="<?= site_url('admin/lotes') ?>" class="btn btn-secondary">
      📦 Supervisar Todos los Lotes
    </a>
    <a href="<?= site_url('productos') ?>" class="btn btn-secondary">
      🔍 Ver Inventario General
    </a>
  </div>
</div>

<!-- Tarjetas de Métricas Globales del Administrador -->
<div class="grid-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
  <div class="card" style="margin-bottom: 0; padding: 1.25rem; border-left: 4px solid var(--color-accent);">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Empresas Registradas</span>
    <div style="font-size: 2rem; font-weight: 800; color: #ffffff; margin-top: 0.25rem;">
      <?= $totalEmpresas ?>
    </div>
    <span style="font-size: 0.8rem; color: var(--color-success); font-weight: 600;">
      <?= $empresasActivas ?> activas / <?= $empresasInactivas ?> suspendidas
    </span>
  </div>

  <div class="card" style="margin-bottom: 0; padding: 1.25rem; border-left: 4px solid #14b8a6;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Lotes Publicados</span>
    <div style="font-size: 2rem; font-weight: 800; color: #14b8a6; margin-top: 0.25rem;">
      <?= $totalLotes ?>
    </div>
    <span style="font-size: 0.8rem; color: var(--text-secondary);">En inventario circular</span>
  </div>

  <div class="card" style="margin-bottom: 0; padding: 1.25rem; border-left: 4px solid #f59e0b;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Volumen Total en Red</span>
    <div style="font-size: 1.8rem; font-weight: 800; color: #ffffff; margin-top: 0.25rem;">
      <?= number_format($totalKg, 0, ',', '.') ?> <span style="font-size: 1rem; font-weight: 500; color: var(--text-muted);">kg</span>
    </div>
    <span style="font-size: 0.8rem; color: var(--text-secondary);">Polímeros reciclables</span>
  </div>

  <div class="card" style="margin-bottom: 0; padding: 1.25rem; border-left: 4px solid #8b5cf6;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Sesión Administrativa</span>
    <div style="font-size: 1.25rem; font-weight: 800; color: #ffffff; margin-top: 0.4rem;">
      Superadmin
    </div>
    <span style="font-size: 0.8rem; color: #a78bfa; font-family: monospace;">myadminpro@gmail.com</span>
  </div>
</div>

<!-- Tabla Principal de Empresas Registradas -->
<div class="card">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      <h2 class="card-title" style="margin: 0;">Empresas y Cuentas Corporativas</h2>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0.25rem 0 0 0;">
        Listado de empresas que se han registrado en el sistema. Puedes auditar sus datos fiscales y activar o suspender su acceso.
      </p>
    </div>
    <span class="badge" style="background: rgba(20, 184, 166, 0.15); color: #14b8a6; border: 1px solid rgba(20, 184, 166, 0.3);">
      <?= count($empresas) ?> empresas
    </span>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Empresa / Razón Social</th>
          <th>CUIT</th>
          <th>Contacto</th>
          <th>Rubro & Ubicación</th>
          <th>Lotes</th>
          <th>Fecha Registro</th>
          <th>Estado</th>
          <th style="text-align: right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($empresas)): ?>
          <?php foreach ($empresas as $empresa): ?>
            <tr>
              <td style="color: var(--text-muted); font-family: monospace; font-size: 0.85rem;">
                #<?= esc($empresa['id']) ?>
              </td>
              <td>
                <strong style="color: #ffffff; font-size: 0.95rem;">
                  <?= esc($empresa['nombre']) ?>
                </strong>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                  <?= esc($empresa['email']) ?>
                </div>
              </td>
              <td>
                <span style="font-family: monospace; font-weight: 600; color: #e2e8f0; background: rgba(255,255,255,0.05); padding: 0.2rem 0.4rem; border-radius: 4px;">
                  <?= esc($empresa['cuit']) ?>
                </span>
              </td>
              <td style="font-size: 0.85rem; color: var(--text-secondary);">
                <div><?= esc($empresa['telefono']) ?></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= esc($empresa['direccion'] ?? 'Sin dirección') ?></div>
              </td>
              <td style="font-size: 0.85rem;">
                <div style="color: #ffffff;"><?= esc($empresa['rubro'] ?? 'No especificado') ?></div>
                <div style="color: var(--text-muted); font-size: 0.8rem;">
                  <?= esc($empresa['ciudad'] ?? '-') ?>, <?= esc($empresa['provincia'] ?? '-') ?>
                </div>
              </td>
              <td>
                <span class="badge" style="background: rgba(255,255,255,0.08); color: #ffffff;">
                  <?= (int) $empresa['total_lotes'] ?>
                </span>
              </td>
              <td style="font-size: 0.85rem; color: var(--text-secondary);">
                <div><?= date('d/m/Y', strtotime($empresa['created_at'])) ?></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">
                  Último: <?= !empty($empresa['ultimo_login']) ? date('d/m H:i', strtotime($empresa['ultimo_login'])) : 'Nunca' ?>
                </div>
              </td>
              <td>
                <?php if ($empresa['estado'] === 'activo'): ?>
                  <span class="badge badge-success">● Activa</span>
                <?php else: ?>
                  <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.4);">
                    ● Suspendida
                  </span>
                <?php endif; ?>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                  <a href="<?= site_url('admin/empresa/' . $empresa['id']) ?>" class="btn btn-secondary btn-sm" title="Ver ficha y lotes de la empresa">
                    Ver
                  </a>

                  <!-- Formulario POST puro para cambiar estado (Cero JavaScript) -->
                  <form action="<?= site_url('admin/empresa/cambiar-estado/' . $empresa['id']) ?>" method="POST" style="margin: 0; display: inline;">
                    <?= csrf_field() ?>
                    <?php if ($empresa['estado'] === 'activo'): ?>
                      <button type="submit" class="btn btn-secondary btn-sm" style="color: #f87171;" title="Suspender cuenta de la empresa">
                        Pausar
                      </button>
                    <?php else: ?>
                      <button type="submit" class="btn btn-secondary btn-sm" style="color: #4ade80;" title="Reactivar cuenta de la empresa">
                        Activar
                      </button>
                    <?php endif; ?>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
              No se han registrado empresas aún en la plataforma.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= view('templates/footer') ?>
