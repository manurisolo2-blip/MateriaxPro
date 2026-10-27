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
     * Procesa y guarda un nuevo mensaje enviado (compatible con AJAX y formulario tradicional)
     */
    public function enviar()
    {
        $userId = (int) session()->get('user_id');
        $isAjax = $this->request->isAJAX() 
                  || strpos($this->request->getHeaderLine('Accept'), 'application/json') !== false
                  || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

        $rules = [
            'mensaje'     => 'required|min_length[2]|max_length[2000]',
            'producto_id' => 'required|is_natural_no_zero',
            'receptor_id' => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'El mensaje debe tener entre 2 y 2000 caracteres.',
                ])->setStatusCode(400);
            }
            return redirect()->back()->withInput()->with('error', 'El mensaje debe tener entre 2 y 2000 caracteres.');
        }

        $productoId    = (int) $this->request->getPost('producto_id');
        $receptorId    = (int) $this->request->getPost('receptor_id');
        $transaccionId = $this->request->getPost('transaccion_id') ? (int) $this->request->getPost('transaccion_id') : null;
        $mensajeTexto  = trim((string) $this->request->getPost('mensaje'));

        // Seguridad: No enviarse mensajes a sí mismo
        if ($receptorId === $userId) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'No puedes enviarte mensajes a tu propia cuenta.',
                ])->setStatusCode(400);
            }
            return redirect()->back()->with('error', 'No puedes enviarte mensajes a tu propia cuenta.');
        }

        // Si el mensaje está asociado a una transacción, validar pertenencia
        if ($transaccionId) {
            $trx = $this->transaccionModel->find($transaccionId);
            if (!$trx) {
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Transacción no encontrada.'])->setStatusCode(404);
                }
                throw PageNotFoundException::forPageNotFound('Transacción no encontrada.');
            }

            // El emisor debe ser el comprador o el vendedor
            if ((int)$trx['comprador_id'] !== $userId && (int)$trx['vendedor_id'] !== $userId && session()->get('rol') !== 'admin') {
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Acceso denegado.'])->setStatusCode(403);
                }
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
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Lote no encontrado.'])->setStatusCode(404);
                }
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

        $insertId = $this->mensajeModel->insert($nuevoMensaje);
        if (!$insertId) {
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo guardar el mensaje.'])->setStatusCode(500);
            }
            return redirect()->back()->withInput()->with('error', 'No se pudo enviar el mensaje.');
        }

        if ($isAjax) {
            $emisor = $this->userModel->find($userId);
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Mensaje enviado correctamente.',
                'data'    => [
                    'id'            => (int) $insertId,
                    'mensaje'       => esc($mensajeTexto),
                    'emisor_id'     => $userId,
                    'emisor_nombre' => esc($emisor['nombre'] ?? 'Tú'),
                    'created_at'    => date('d/m/Y H:i'),
                    'es_propio'     => true,
                ],
            ]);
        }

        // Redireccionar al hilo correspondiente en formulario tradicional
        if ($transaccionId) {
            return redirect()->to(site_url("solicitudes/ver/{$transaccionId}#chat"))
                             ->with('success', '¡Mensaje enviado a la contraparte!');
        }

        return redirect()->to(site_url("productos/ver/{$productoId}#consultas"))
                         ->with('success', '¡Consulta enviada al vendedor del lote!');
    }

    /**
     * Endpoint AJAX para polling en tiempo real: devuelve mensajes nuevos a partir de un ID
     */
    public function nuevos()
    {
        $userId        = (int) session()->get('user_id');
        $transaccionId = $this->request->getGet('transaccion_id') ? (int) $this->request->getGet('transaccion_id') : null;
        $productoId    = $this->request->getGet('producto_id') ? (int) $this->request->getGet('producto_id') : null;
        $ultimoId      = (int) ($this->request->getGet('ultimo_id') ?? 0);

        if (!$transaccionId && !$productoId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Parámetros insuficientes.'])->setStatusCode(400);
        }

        // Validar pertenencia si es transacción
        if ($transaccionId) {
            $trx = $this->transaccionModel->find($transaccionId);
            if (!$trx) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Transacción no encontrada.'])->setStatusCode(404);
            }
            if ((int)$trx['comprador_id'] !== $userId && (int)$trx['vendedor_id'] !== $userId && session()->get('rol') !== 'admin') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado.'])->setStatusCode(403);
            }
        }

        $builder = $this->mensajeModel->builder();
        if ($transaccionId) {
            $builder->where('transaccion_id', $transaccionId);
        } else {
            $builder->where('producto_id', $productoId)
                    ->groupStart()
                        ->where('emisor_id', $userId)
                        ->orWhere('receptor_id', $userId)
                    ->groupEnd();
        }

        $builder->where('id >', $ultimoId);
        $builder->orderBy('id', 'ASC');
        $mensajesNuevos = $builder->get()->getResultArray();

        $nuevosFormateados = [];
        foreach ($mensajesNuevos as $msg) {
            if ((int)$msg['receptor_id'] === $userId && (int)$msg['leido'] === 0) {
                $this->mensajeModel->update($msg['id'], [
                    'leido'         => 1,
                    'fecha_lectura' => date('Y-m-d H:i:s'),
                ]);
            }

            $emisor = $this->userModel->find($msg['emisor_id']);
            $nuevosFormateados[] = [
                'id'            => (int) $msg['id'],
                'mensaje'       => esc($msg['mensaje']),
                'emisor_id'     => (int) $msg['emisor_id'],
                'emisor_nombre' => esc($emisor['nombre'] ?? 'Empresa'),
                'created_at'    => date('d/m/Y H:i', strtotime($msg['created_at'])),
                'es_propio'     => ((int)$msg['emisor_id'] === $userId),
            ];
        }

        return $this->response->setJSON([
            'status'          => 'success',
            'nuevos'          => $nuevosFormateados,
            'total_no_leidos' => $this->mensajeModel->contarNoLeidos($userId),
        ]);
    }

    /**
     * Endpoint AJAX para conteo dinámico de mensajes no leídos (badge de cabecera)
     */
    public function conteoNoLeidos()
    {
        $userId = (int) session()->get('user_id');
        $total  = $this->mensajeModel->contarNoLeidos($userId);
        return $this->response->setJSON(['status' => 'success', 'no_leidos' => $total]);
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
