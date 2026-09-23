<?= view('templates/header', ['pageTitle' => 'Detalle de Empresa | MateriaX Admin']) ?>

<div style="margin-bottom: 1.5rem;">
  <a href="<?= site_url('admin') ?>" class="btn btn-secondary btn-sm" style="margin-bottom: 1rem; display: inline-block;">
    ← Volver al Panel de Administración
  </a>

  <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
    <div>
      <span style="font-size: 0.85rem; color: var(--brand-teal); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
        FICHA DE EMPRESA REGISTRADA
      </span>
      <h1 style="font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-top: 0.25rem;">
        <?= esc($empresa['nombre']) ?>
      </h1>
      <p style="color: var(--text-secondary); font-size: 0.95rem;">
        Auditoría de datos institucionales y monitoreo de materiales publicados.
      </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
      <?php if ($empresa['estado'] === 'pendiente'): ?>
        <!-- Boton POST de aprobacion directa -->
        <form action="<?= site_url('admin/empresa/aprobar/' . $empresa['id']) ?>" method="POST" style="margin: 0; display: inline;">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-primary" style="background: #059669; border-color: #10b981;">
            ✓ Aprobar Empresa
          </button>
        </form>
        <!-- Boton POST de rechazo -->
        <form action="<?= site_url('admin/empresa/rechazar/' . $empresa['id']) ?>" method="POST" style="margin: 0; display: inline;">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-secondary" style="color: #dc2626; border-color: rgba(220, 38, 38, 0.4);">
            ✕ Rechazar Solicitud
          </button>
        </form>
      <?php elseif ($empresa['estado'] === 'activo'): ?>
        <form action="<?= site_url('admin/empresa/cambiar-estado/' . $empresa['id']) ?>" method="POST" style="margin: 0; display: inline;">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-secondary" style="color: #dc2626; border-color: rgba(220, 38, 38, 0.4);">
            ⏸️ Suspender Acceso
          </button>
        </form>
      <?php elseif ($empresa['estado'] === 'inactivo'): ?>
        <form action="<?= site_url('admin/empresa/cambiar-estado/' . $empresa['id']) ?>" method="POST" style="margin: 0; display: inline;">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-secondary" style="color: #16a34a; border-color: rgba(22, 163, 74, 0.4);">
            ▶️ Reactivar Acceso
          </button>
        </form>
      <?php elseif ($empresa['estado'] === 'rechazado'): ?>
        <form action="<?= site_url('admin/empresa/aprobar/' . $empresa['id']) ?>" method="POST" style="margin: 0; display: inline;">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-secondary" style="color: #059669; border-color: rgba(5, 150, 105, 0.4);">
            ✓ Reconsiderar y Aprobar
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Ficha de Datos Corporativos -->
<div class="card" style="margin-bottom: 2rem;">
  <div class="card-header">
    <h3 class="card-title">Información Fiscal y de Contacto</h3>
  </div>
  <div class="card-body">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">
          Razón Social
        </label>
        <div style="font-size: 1rem; color: var(--text-primary); font-weight: 600; margin-top: 0.25rem;">
          <?= esc($empresa['nombre']) ?>
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">
          CUIT
        </label>
        <div style="font-size: 1rem; font-family: monospace; color: var(--color-accent); font-weight: 700; margin-top: 0.25rem;">
          <?= esc($empresa['cuit']) ?>
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">
          Correo Electrónico
        </label>
        <div style="font-size: 1rem; color: var(--text-primary); margin-top: 0.25rem;">
          <?= esc($empresa['email']) ?>
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">
          Teléfono de Contacto
        </label>
        <div style="font-size: 1rem; color: var(--text-primary); margin-top: 0.25rem;">
          <?= esc($empresa['telefono']) ?>
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">
          Rubro / Sector
        </label>
        <div style="font-size: 1rem; color: var(--text-primary); margin-top: 0.25rem;">
          <?= esc($empresa['rubro'] ?? 'No especificado') ?>
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">
          Ubicación Geográfica
        </label>
        <div style="font-size: 1rem; color: var(--text-primary); margin-top: 0.25rem;">
          <?= esc($empresa['ciudad'] ?? '-') ?>, <?= esc($empresa['provincia'] ?? '-') ?>
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">
          Dirección / Planta
        </label>
        <div style="font-size: 1rem; color: var(--text-primary); margin-top: 0.25rem;">
          <?= esc($empresa['direccion'] ?? 'Sin registrar') ?>
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">
          Estado de la Cuenta
        </label>
        <div style="margin-top: 0.25rem;">
          <?php if ($empresa['estado'] === 'pendiente'): ?>
            <span class="badge badge-reservado">
              ⏳ En Etapa de Auditoría (Pendiente)
            </span>
          <?php elseif ($empresa['estado'] === 'activo'): ?>
            <span class="badge badge-success">● Cuenta Habilitada y Operativa</span>
          <?php elseif ($empresa['estado'] === 'rechazado'): ?>
            <span class="badge" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">
              ✕ Solicitud Rechazada por Auditoría
            </span>
          <?php else: ?>
            <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
              ⏸️ Cuenta Suspendida por Admin
            </span>
          <?php endif; ?>
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">
          Fecha de Registro
        </label>
        <div style="font-size: 0.95rem; color: var(--text-secondary); margin-top: 0.25rem;">
          <?= date('d/m/Y H:i', strtotime($empresa['created_at'])) ?>
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block;">
          Último Acceso al Sistema
        </label>
        <div style="font-size: 0.95rem; color: var(--text-secondary); margin-top: 0.25rem;">
          <?= !empty($empresa['ultimo_login']) ? date('d/m/Y H:i', strtotime($empresa['ultimo_login'])) : 'Sin registros de login' ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Tabla de Lotes Publicados por esta Empresa -->
<div class="card">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      <h3 class="card-title" style="margin: 0;">Lotes Publicados por <?= esc($empresa['nombre']) ?></h3>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0.25rem 0 0 0;">
        Materiales y excedentes industriales asociados a esta cuenta.
      </p>
    </div>
    <span class="badge" style="background: var(--bg-main); color: var(--text-primary); border: 1px solid var(--border-color);">
      <?= count($lotes) ?> lotes
    </span>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Lote</th>
          <th>Tipo Polímero</th>
          <th>Volumen (kg)</th>
          <th>Precio Unit.</th>
          <th>Ubicación</th>
          <th>Estado</th>
          <th>Fecha Publicación</th>
          <th style="text-align: right;">Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($lotes)): ?>
          <?php foreach ($lotes as $lote): ?>
            <tr>
              <td>
                <strong style="color: var(--text-primary);"><?= esc($lote['nombre']) ?></strong>
              </td>
              <td>
                <span class="badge badge-polimero">
                  <?= esc($lote['tipo_polimero']) ?>
                </span>
              </td>
              <td><?= number_format($lote['cantidad_kg'], 2, ',', '.') ?> kg</td>
              <td>$<?= number_format($lote['precio_unitario'], 2, ',', '.') ?> /kg</td>
              <td><?= esc($lote['ubicacion']) ?></td>
              <td>
                <?php if ($lote['estado'] === 'Disponible'): ?>
                  <span class="badge badge-success">● Disponible</span>
                <?php elseif ($lote['estado'] === 'Reservado'): ?>
                  <span class="badge badge-warning">● Reservado</span>
                <?php else: ?>
                  <span class="badge badge-vendido">● Vendido</span>
                <?php endif; ?>
              </td>
              <td style="font-size: 0.85rem; color: var(--text-secondary);">
                <?= date('d/m/Y', strtotime($lote['created_at'])) ?>
              </td>
              <td style="text-align: right;">
                <a href="<?= site_url('productos/ver/' . $lote['id']) ?>" class="btn btn-secondary btn-sm" target="_blank">
                  Ver Ficha
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
              Esta empresa aún no ha publicado lotes en el inventario.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= view('templates/footer') ?>
