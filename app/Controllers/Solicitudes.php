<?php

namespace App\Controllers;

use App\Models\TransaccionModel;
use App\Models\ProductoModel;
use App\Models\UserModel;
use App\Models\CertificadoModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Solicitudes extends BaseController
{
    protected TransaccionModel $transaccionModel;
    protected ProductoModel $productoModel;
    protected UserModel $userModel;
    protected CertificadoModel $certificadoModel;
    protected $helpers = ['form', 'url'];

    public function __construct()
    {
        $this->transaccionModel = new TransaccionModel();
        $this->productoModel    = new ProductoModel();
        $this->userModel        = new UserModel();
        $this->certificadoModel = new CertificadoModel();
    }

    /**
     * Bandeja principal: redirige a solicitudes recibidas
     */
    public function index()
    {
        return redirect()->to(site_url('solicitudes/recibidas'));
    }

    /**
     * Solicitudes y ofertas recibidas sobre los lotes del usuario logueado
     */
    public function recibidas()
    {
        $userId = (int) session()->get('user_id');
        $solicitudes = $this->transaccionModel->getSolicitudesRecibidas($userId);

        $pendientes = count(array_filter($solicitudes, fn($s) => $s['estado'] === 'solicitada'));
        $aprobadas  = count(array_filter($solicitudes, fn($s) => $s['estado'] === 'aprobada'));

        return view('solicitudes/recibidas', [
            'pageTitle'   => 'Solicitudes Recibidas (Ventas) | MateriaX',
            'solicitudes' => $solicitudes,
            'pendientes'  => $pendientes,
            'aprobadas'   => $aprobadas,
        ]);
    }

    /**
     * Solicitudes de compra enviadas por el usuario logueado a otros oferentes
     */
    public function enviadas()
    {
        $userId = (int) session()->get('user_id');
        $solicitudes = $this->transaccionModel->getSolicitudesEnviadas($userId);

        return view('solicitudes/enviadas', [
            'pageTitle'   => 'Mis Compras y Solicitudes Enviadas | MateriaX',
            'solicitudes' => $solicitudes,
        ]);
    }

    /**
     * Formulario para iniciar una solicitud de compra sobre un lote de polímero
     */
    public function crear($productoId = null)
    {
        if (empty($productoId) || !is_numeric($productoId)) {
            throw PageNotFoundException::forPageNotFound('Identificador de lote no válido.');
        }

        $producto = $this->productoModel->getProductosConUsuario((int) $productoId);
        if (!$producto) {
            throw PageNotFoundException::forPageNotFound('El lote seleccionado no existe.');
        }

        $userId = (int) session()->get('user_id');

        // Regla de negocio: una empresa no puede comprar su propio lote
        if ((int) $producto['user_id'] === $userId) {
            return redirect()->to(site_url('productos/ver/' . $productoId))
                             ->with('error', 'No puedes enviar una solicitud de compra sobre un lote de tu propia empresa.');
        }

        // Regla: Solo lotes disponibles
        if ($producto['estado'] !== 'Disponible') {
            return redirect()->to(site_url('productos/ver/' . $productoId))
                             ->with('error', "Este lote ya no se encuentra disponible (Estado actual: {$producto['estado']}).");
        }

        return view('solicitudes/crear', [
            'pageTitle' => 'Solicitar Lote: ' . esc($producto['nombre']) . ' | MateriaX',
            'producto'  => $producto,
        ]);
    }

    /**
     * Procesa y registra la solicitud de compra
     */
    public function guardar()
    {
        $productoId = (int) $this->request->getPost('producto_id');
        $producto   = $this->productoModel->find($productoId);

        if (!$producto) {
            throw PageNotFoundException::forPageNotFound('El producto no existe.');
        }

        $userId = (int) session()->get('user_id');
        if ((int) $producto['user_id'] === $userId) {
            return redirect()->to(site_url('productos'))
                             ->with('error', 'Operación denegada: no puedes solicitar tus propias publicaciones.');
        }

        $rules = [
            'producto_id'             => 'required|is_natural_no_zero',
            'cantidad_kg'             => 'required|numeric|greater_than[0]',
            'precio_unitario_pactado' => 'required|numeric|greater_than_equal_to[0]',
            'costo_flete'             => 'permit_empty|numeric|greater_than_equal_to[0]',
            'modalidad_retiro'        => 'required|in_list[retiro_comprador_planta,entrega_vendedor,flete_convenido]',
            'observaciones'           => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $cantidadKg   = (float) $this->request->getPost('cantidad_kg');
        $precioKg     = (float) $this->request->getPost('precio_unitario_pactado');
        $costoFlete   = (float) ($this->request->getPost('costo_flete') ?: 0);
        $subtotal     = round($cantidadKg * $precioKg, 2);
        $montoTotal   = round($subtotal + $costoFlete, 2);

        $codigoOp = $this->transaccionModel->generarCodigoOperacion();

        $data = [
            'codigo_operacion'        => $codigoOp,
            'producto_id'             => $productoId,
            'comprador_id'            => $userId,
            'vendedor_id'             => (int) $producto['user_id'],
            'planta_origen_id'        => !empty($producto['planta_id']) ? (int) $producto['planta_id'] : null,
            'cantidad_kg'             => $cantidadKg,
            'precio_unitario_pactado' => $precioKg,
            'subtotal'                => $subtotal,
            'costo_flete'             => $costoFlete,
            'monto_total'             => $montoTotal,
            'modalidad_retiro'        => $this->request->getPost('modalidad_retiro'),
            'estado'                  => 'solicitada',
            'observaciones'           => trim((string) $this->request->getPost('observaciones')),
            'fecha_solicitud'         => date('Y-m-d H:i:s'),
        ];

        if (!$this->transaccionModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Error al emitir la solicitud de compra.');
        }

        return redirect()->to(site_url('solicitudes/enviadas'))
                         ->with('success', "¡Solicitud {$codigoOp} enviada con éxito al vendedor! Podrás hacer el seguimiento de su respuesta aquí.");
    }

    /**
     * Ficha detallada de la transacción
     */
    public function ver($id = null)
    {
        $trx = $this->validarAccesoTransaccion($id);
        if ($trx instanceof RedirectResponse) {
            return $trx;
        }

        $certificado = $this->certificadoModel->where('transaccion_id', $trx['id'])->first();

        return view('solicitudes/ver', [
            'pageTitle'   => "Operación {$trx['codigo_operacion']} | MateriaX",
            'transaccion' => $trx,
            'certificado' => $certificado,
        ]);
    }

    /**
     * El vendedor acepta o rechaza una propuesta
     */
    public function responder($id = null)
    {
        if (empty($id) || !is_numeric($id)) {
            throw PageNotFoundException::forPageNotFound('Operación no válida.');
        }

        $userId = (int) session()->get('user_id');
        $trx = $this->transaccionModel->find((int) $id);

        if (!$trx) {
            throw PageNotFoundException::forPageNotFound('Transacción no encontrada.');
        }

        // Solo el vendedor puede responder a la oferta
        if ((int) $trx['vendedor_id'] !== $userId && session()->get('rol') !== 'admin') {
            return redirect()->to(site_url('solicitudes/recibidas'))
                             ->with('error', 'Acceso denegado: solo el titular del lote puede responder a esta oferta.');
        }

        if ($trx['estado'] !== 'solicitada') {
            return redirect()->to(site_url('solicitudes/ver/' . $id))
                             ->with('error', "La transacción ya fue procesada previamente (Estado: {$trx['estado']}).");
        }

        $accion = $this->request->getPost('accion');

        if ($accion === 'aceptar') {
            $this->transaccionModel->update($id, [
                'estado'        => 'aprobada',
                'fecha_acuerdo' => date('Y-m-d H:i:s'),
            ]);

            // Actualizar estado del producto a Reservado
            $this->productoModel->update($trx['producto_id'], [
                'estado' => 'Reservado',
            ]);

            // Emisión automática del Certificado de Impacto Ambiental
            $producto = $this->productoModel->find($trx['producto_id']);
            $trxActualizada = array_merge($trx, ['estado' => 'aprobada']);
            $cert = $this->certificadoModel->emitirParaTransaccion($trxActualizada, $producto ?? []);

            $mensaje = "¡Solicitud {$trx['codigo_operacion']} ACEPTADA! El lote quedó en estado 'Reservado'";
            if ($cert) {
                $mensaje .= " y se emitió automáticamente el Certificado de Huella Verde ({$cert['codigo_certificado']}).";
            } else {
                $mensaje .= " para coordinar el retiro logístico.";
            }

            return redirect()->to(site_url('solicitudes/ver/' . $id))->with('success', $mensaje);
        }

        if ($accion === 'rechazar') {
            $this->transaccionModel->update($id, [
                'estado'       => 'rechazada',
                'fecha_cierre' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to(site_url('solicitudes/recibidas'))
                             ->with('success', "La solicitud {$trx['codigo_operacion']} fue rechazada.");
        }

        return redirect()->to(site_url('solicitudes/recibidas'));
    }

    /**
     * El comprador cancela su solicitud pendiente
     */
    public function cancelar($id = null)
    {
        if (empty($id) || !is_numeric($id)) {
            throw PageNotFoundException::forPageNotFound('Operación no válida.');
        }

        $userId = (int) session()->get('user_id');
        $trx = $this->transaccionModel->find((int) $id);

        if (!$trx) {
            throw PageNotFoundException::forPageNotFound('Transacción no encontrada.');
        }

        if ((int) $trx['comprador_id'] !== $userId) {
            return redirect()->to(site_url('solicitudes/enviadas'))
                             ->with('error', 'Acceso denegado: no puedes cancelar solicitudes de otras empresas.');
        }

        if ($trx['estado'] !== 'solicitada') {
            return redirect()->to(site_url('solicitudes/enviadas'))
                             ->with('error', "No puedes cancelar una solicitud que ya está en estado {$trx['estado']}.");
        }

        $this->transaccionModel->update($id, [
            'estado'       => 'cancelada',
            'fecha_cierre' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('solicitudes/enviadas'))
                         ->with('success', "Has cancelado la solicitud {$trx['codigo_operacion']}.");
    }

    /**
     * Valida permisos para visualizar la ficha de la transacción
     */
    protected function validarAccesoTransaccion($id): array|RedirectResponse
    {
        if (empty($id) || !is_numeric($id)) {
            throw PageNotFoundException::forPageNotFound('Operación no válida.');
        }

        $trx = $this->transaccionModel->getDetalleTransaccion((int) $id);
        if (!$trx) {
            throw PageNotFoundException::forPageNotFound('Transacción no encontrada.');
        }

        $userId = (int) session()->get('user_id');
        $rol    = session()->get('rol');

        // Permiso: solo comprador, vendedor o admin
        if ((int) $trx['comprador_id'] !== $userId && (int) $trx['vendedor_id'] !== $userId && $rol !== 'admin') {
            return redirect()->to(site_url('panel'))
                             ->with('error', 'Acceso denegado: no formas parte de esta transacción comercial.');
        }

        return $trx;
    }
}
