<?php

namespace App\Controllers;

use App\Models\MensajeModel;
use App\Models\TransaccionModel;
use App\Models\ProductoModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Mensajes extends BaseController
{
    protected MensajeModel $mensajeModel;
    protected TransaccionModel $transaccionModel;
    protected ProductoModel $productoModel;
    protected UserModel $userModel;
    protected $helpers = ['form', 'url'];

    public function __construct()
    {
        $this->mensajeModel     = new MensajeModel();
        $this->transaccionModel = new TransaccionModel();
        $this->productoModel    = new ProductoModel();
        $this->userModel        = new UserModel();
    }

    /**
     * Bandeja de entrada general de mensajes entre empresas
     */
    public function index()
    {
        $userId = (int) session()->get('user_id');
        $conversaciones = $this->mensajeModel->getConversacionesUsuario($userId);
        $totalNoLeidos  = $this->mensajeModel->contarNoLeidos($userId);

        return view('mensajes/index', [
            'pageTitle'      => 'Bandeja de Mensajes y Consultas | MateriaX',
            'conversaciones' => $conversaciones,
            'totalNoLeidos'  => $totalNoLeidos,
        ]);
    }

    /**
     * Procesa y guarda un nuevo mensaje enviado
     */
    public function enviar(): RedirectResponse
    {
        $userId = (int) session()->get('user_id');

        $rules = [
            'mensaje'     => 'required|min_length[2]|max_length[2000]',
            'producto_id' => 'required|is_natural_no_zero',
            'receptor_id' => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'El mensaje debe tener entre 2 y 2000 caracteres.');
        }

        $productoId    = (int) $this->request->getPost('producto_id');
        $receptorId    = (int) $this->request->getPost('receptor_id');
        $transaccionId = $this->request->getPost('transaccion_id') ? (int) $this->request->getPost('transaccion_id') : null;
        $mensajeTexto  = trim((string) $this->request->getPost('mensaje'));

        // Seguridad: No enviarse mensajes a sí mismo
        if ($receptorId === $userId) {
            return redirect()->back()->with('error', 'No puedes enviarte mensajes a tu propia cuenta.');
        }

        // Si el mensaje está asociado a una transacción, validar pertenencia
        if ($transaccionId) {
            $trx = $this->transaccionModel->find($transaccionId);
            if (!$trx) {
                throw PageNotFoundException::forPageNotFound('Transacción no encontrada.');
            }

            // El emisor debe ser el comprador o el vendedor
            if ((int)$trx['comprador_id'] !== $userId && (int)$trx['vendedor_id'] !== $userId && session()->get('rol') !== 'admin') {
                return redirect()->to(site_url('solicitudes'))->with('error', 'Acceso denegado: no formas parte de esta operación.');
            }

            // El receptor debe ser la contraparte
            $contraparteEsperada = ((int)$trx['comprador_id'] === $userId) ? (int)$trx['vendedor_id'] : (int)$trx['comprador_id'];
            if ($receptorId !== $contraparteEsperada && session()->get('rol') !== 'admin') {
                $receptorId = $contraparteEsperada;
            }
        } else {
            // Consulta directa sobre producto
            $producto = $this->productoModel->find($productoId);
            if (!$producto) {
                throw PageNotFoundException::forPageNotFound('Lote de polímero no encontrado.');
            }
        }

        $nuevoMensaje = [
            'producto_id'    => $productoId,
            'transaccion_id' => $transaccionId,
            'emisor_id'      => $userId,
            'receptor_id'    => $receptorId,
            'mensaje'        => $mensajeTexto,
            'leido'          => 0,
            'fecha_lectura'  => null,
        ];

        if (!$this->mensajeModel->insert($nuevoMensaje)) {
            return redirect()->back()->withInput()->with('error', 'No se pudo enviar el mensaje.');
        }

        // Redireccionar al hilo correspondiente
        if ($transaccionId) {
            return redirect()->to(site_url("solicitudes/ver/{$transaccionId}#chat"))
                             ->with('success', '¡Mensaje enviado a la contraparte!');
        }

        return redirect()->to(site_url("productos/ver/{$productoId}#consultas"))
                         ->with('success', '¡Consulta enviada al vendedor del lote!');
    }

    /**
     * Redirige al chat de una transacción
     */
    public function ver($transaccionId = null): RedirectResponse
    {
        if (empty($transaccionId) || !is_numeric($transaccionId)) {
            throw PageNotFoundException::forPageNotFound('Identificador de operación inválido.');
        }

        return redirect()->to(site_url("solicitudes/ver/{$transaccionId}#chat"));
    }
}
