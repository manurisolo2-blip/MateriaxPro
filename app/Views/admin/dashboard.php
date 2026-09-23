<?= view('templates/header', ['pageTitle' => 'Panel de Administración y Auditoría | MateriaX']) ?>

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <span style="font-size: 0.85rem; color: var(--brand-teal); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
      🛡️ AUDITORÍA CORPORATIVA & ADMINISTRACIÓN EXCLUSIVA
    </span>
    <h1 style="font-size: 2.2rem; font-weight: 800; color: var(--text-primary); margin-top: 0.25rem;">
      Auditoría y Gestión de la Red MateriaX
    </h1>
    <p style="color: var(--text-secondary); font-size: 0.95rem;">
      Revisa y aprueba solicitudes de registro empresarial, audita CUITs y supervisa los lotes de polímeros.
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
<div class="grid-4" style="margin-bottom: 2rem;">
  <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">
      Auditorías Pendientes
    </span>
    <div style="font-size: 2.2rem; font-weight: 800; color: <?= ($totalPendientes > 0) ? '#d97706' : 'var(--brand-teal)' ?>; margin-top: 0.25rem;">
      <?= $totalPendientes ?>
    </div>
    <span style="font-size: 0.8rem; color: var(--text-secondary);">
      <?= ($totalPendientes > 0) ? 'Requieren tu aprobación' : 'Al día, sin solicitudes pendientes' ?>
    </span>
  </div>

  <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Empresas Habilitadas</span>
    <div style="font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-top: 0.25rem;">
      <?= $empresasActivas ?>
    </div>
    <span style="font-size: 0.8rem; color: var(--color-success); font-weight: 600;">
      ● Cuentas operando en la red
    </span>
  </div>

  <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Lotes Publicados</span>
    <div style="font-size: 2rem; font-weight: 800; color: var(--brand-teal); margin-top: 0.25rem;">
      <?= $totalLotes ?>
    </div>
    <span style="font-size: 0.8rem; color: var(--text-secondary);">Inventario circular activo</span>
  </div>

  <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Volumen Total en Red</span>
    <div style="font-size: 1.8rem; font-weight: 800; color: var(--text-primary); margin-top: 0.25rem;">
      <?= number_format($totalKg, 0, ',', '.') ?> <span style="font-size: 1rem; font-weight: 500; color: var(--text-muted);">kg</span>
    </div>
    <span style="font-size: 0.8rem; color: var(--text-secondary);">Materiales reciclables</span>
  </div>
</div>

<!-- BANDEJA DE AUDITORÍA: EMPRESAS PENDIENTES DE APROBACIÓN -->
<div class="card" style="margin-bottom: 2rem; border: 1px solid <?= ($totalPendientes > 0) ? 'rgba(245, 158, 11, 0.4)' : 'var(--border-color)' ?>;">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; background: <?= ($totalPendientes > 0) ? 'rgba(245, 158, 11, 0.05)' : 'transparent' ?>;">
    <div>
      <h2 class="card-title" style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
        <span>⏳ Solicitudes de Registro en Etapa de Auditoría</span>
      </h2>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0.25rem 0 0 0;">
        Empresas que completaron el registro y están esperando que verifiques sus datos fiscales y apruebes su ingreso.
      </p>
    </div>
    <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; font-size: 0.85rem; font-weight: 700;">
      <?= $totalPendientes ?> pendientes de aprobación
    </span>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Empresa Solicitante</th>
          <th>CUIT</th>
          <th>Contacto Institucional</th>
          <th>Rubro & Ubicación</th>
          <th>Fecha de Solicitud</th>
          <th>Estado</th>
          <th style="text-align: right;">Decisión de Auditoría</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($empresasPendientes)): ?>
          <?php foreach ($empresasPendientes as $ep): ?>
            <tr style="background: rgba(245, 158, 11, 0.03);">
              <td style="color: var(--text-muted); font-family: monospace; font-size: 0.85rem;">
                #<?= esc($ep['id']) ?>
              </td>
              <td>
                <strong style="color: var(--text-primary); font-size: 0.95rem;">
                  <?= esc($ep['nombre']) ?>
                </strong>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                  <?= esc($ep['email']) ?>
                </div>
              </td>
              <td>
                <span style="font-family: monospace; font-weight: 700; color: #92400e; background: #fef3c7; padding: 0.25rem 0.5rem; border-radius: 4px; border: 1px solid #fde68a;">
                  <?= esc($ep['cuit']) ?>
                </span>
              </td>
              <td style="font-size: 0.85rem; color: var(--text-secondary);">
                <div>📞 <?= esc($ep['telefono']) ?></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">📍 <?= esc($ep['direccion'] ?? 'Sin dirección') ?></div>
              </td>
              <td style="font-size: 0.85rem;">
                <div style="color: var(--text-primary); font-weight: 600;"><?= esc($ep['rubro'] ?? 'No especificado') ?></div>
                <div style="color: var(--text-muted); font-size: 0.8rem;">
                  <?= esc($ep['ciudad'] ?? '-') ?>, <?= esc($ep['provincia'] ?? '-') ?>
                </div>
              </td>
              <td style="font-size: 0.85rem; color: var(--text-secondary);">
                <div><?= date('d/m/Y', strtotime($ep['created_at'])) ?></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= date('H:i', strtotime($ep['created_at'])) ?> hs</div>
              </td>
              <td>
                <span class="badge badge-reservado">
                  ⏳ En Auditoría
                </span>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                  <a href="<?= site_url('admin/empresa/' . $ep['id']) ?>" class="btn btn-secondary btn-sm" title="Revisar ficha de datos completa">
                    Ver Ficha
                  </a>

                  <!-- Formulario POST de Aprobación Inmediata (Cero JS) -->
                  <form action="<?= site_url('admin/empresa/aprobar/' . $ep['id']) ?>" method="POST" style="margin: 0; display: inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-sm" style="background: #059669; border-color: #10b981;" title="Aprobar empresa y permitirle ingresar">
                      ✓ Aprobar
                    </button>
                  </form>

                  <!-- Formulario POST de Rechazo (Cero JS) -->
                  <form action="<?= site_url('admin/empresa/rechazar/' . $ep['id']) ?>" method="POST" style="margin: 0; display: inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-secondary btn-sm" style="color: #f87171; border-color: rgba(239, 68, 68, 0.4);" title="Rechazar solicitud de registro">
                      ✕ Rechazar
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-secondary);">
              <span style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;">🎉</span>
              <strong>No hay empresas pendientes de auditoría en este momento.</strong>
              <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">
                Todas las solicitudes de registro han sido procesadas.
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- TABLA DE EMPRESAS AUDITADAS Y PROCESADAS -->
<div class="card">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
    <div>
      <h2 class="card-title" style="margin: 0;">Empresas Procesadas en la Red</h2>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0.25rem 0 0 0;">
        Historial de empresas aprobadas, activas, suspendidas o rechazadas en MateriaX.
      </p>
    </div>
    <span class="badge" style="background: rgba(15, 118, 110, 0.1); color: var(--brand-teal); border: 1px solid rgba(15, 118, 110, 0.25);">
      <?= count($empresasAuditadas) ?> empresas procesadas
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
          <th>Último Login</th>
          <th>Estado</th>
          <th style="text-align: right;">Moderación</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($empresasAuditadas)): ?>
          <?php foreach ($empresasAuditadas as $empresa): ?>
            <tr>
              <td style="color: var(--text-muted); font-family: monospace; font-size: 0.85rem;">
                #<?= esc($empresa['id']) ?>
              </td>
              <td>
                <strong style="color: var(--text-primary); font-size: 0.95rem;">
                  <?= esc($empresa['nombre']) ?>
                </strong>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                  <?= esc($empresa['email']) ?>
                </div>
              </td>
              <td>
                <span style="font-family: monospace; font-weight: 600; color: var(--text-primary); background: var(--bg-main); padding: 0.2rem 0.4rem; border-radius: 4px; border: 1px solid var(--border-color);">
                  <?= esc($empresa['cuit']) ?>
                </span>
              </td>
              <td style="font-size: 0.85rem; color: var(--text-secondary);">
                <div><?= esc($empresa['telefono']) ?></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= esc($empresa['direccion'] ?? 'Sin dirección') ?></div>
              </td>
              <td style="font-size: 0.85rem;">
                <div style="color: var(--text-primary);"><?= esc($empresa['rubro'] ?? 'No especificado') ?></div>
                <div style="color: var(--text-muted); font-size: 0.8rem;">
                  <?= esc($empresa['ciudad'] ?? '-') ?>, <?= esc($empresa['provincia'] ?? '-') ?>
                </div>
              </td>
              <td>
                <span class="badge" style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color);">
                  <?= (int) $empresa['total_lotes'] ?>
                </span>
              </td>
              <td style="font-size: 0.85rem; color: var(--text-secondary);">
                <?= !empty($empresa['ultimo_login']) ? date('d/m/Y H:i', strtotime($empresa['ultimo_login'])) : 'Nunca ingresó' ?>
              </td>
              <td>
                <?php if ($empresa['estado'] === 'activo'): ?>
                  <span class="badge badge-success">● Habilitada / Activa</span>
                <?php elseif ($empresa['estado'] === 'rechazado'): ?>
                  <span class="badge" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">
                    ✕ Solicitud Rechazada
                  </span>
                <?php else: ?>
                  <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                    ⏸️ Suspendida
                  </span>
                <?php endif; ?>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                  <a href="<?= site_url('admin/empresa/' . $empresa['id']) ?>" class="btn btn-secondary btn-sm">
                    Ver
                  </a>

                  <?php if ($empresa['estado'] === 'activo'): ?>
                    <form action="<?= site_url('admin/empresa/cambiar-estado/' . $empresa['id']) ?>" method="POST" style="margin: 0; display: inline;">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-secondary btn-sm" style="color: #dc2626;" title="Pausar temporalmente acceso de la empresa">
                        ⏸ Pausar
                      </button>
                    </form>
                  <?php elseif ($empresa['estado'] === 'inactivo'): ?>
                    <form action="<?= site_url('admin/empresa/cambiar-estado/' . $empresa['id']) ?>" method="POST" style="margin: 0; display: inline;">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-secondary btn-sm" style="color: #16a34a;" title="Reactivar acceso comercial de la empresa">
                        ▶ Reactivar
                      </button>
                    </form>
                  <?php elseif ($empresa['estado'] === 'rechazado'): ?>
                    <form action="<?= site_url('admin/empresa/aprobar/' . $empresa['id']) ?>" method="POST" style="margin: 0; display: inline;">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-secondary btn-sm" style="color: #059669;" title="Reconsiderar y autorizar ingreso">
                        ✓ Aprobar
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-secondary);">
              <span style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;">🏢</span>
              <p style="font-size: 1.05rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.35rem;">
                No hay empresas procesadas registradas aún.
              </p>
              <div style="font-size: 0.85rem; color: var(--text-muted);">
                Las cuentas aprobadas, suspendidas o rechazadas figurarán en este registro histórico.
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= view('templates/footer') ?>
