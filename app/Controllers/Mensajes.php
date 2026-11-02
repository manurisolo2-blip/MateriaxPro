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
            'userId'         => $userId,
        ]);
    }

    /**
     * Procesa y guarda un nuevo mensaje enviado (compatible con AJAX instantáneo y formulario tradicional)
     */
    public function enviar()
    {
        $userId     = (int) session()->get('user_id');
        $userRol    = (string) session()->get('rol');
        $userNombre = (string) session()->get('nombre');

        $isAjax = $this->request->isAJAX()
                  || strpos($this->request->getHeaderLine('Accept'), 'application/json') !== false
                  || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

        // Liberar el bloqueo de archivo de sesión en peticiones AJAX para permitir envío y polling simultáneos sin espera
        if ($isAjax && session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $mensajeTexto  = trim((string) $this->request->getPost('mensaje'));
        $productoId    = (int) $this->request->getPost('producto_id');
        $receptorId    = (int) $this->request->getPost('receptor_id');
        $transaccionId = $this->request->getPost('transaccion_id') ? (int) $this->request->getPost('transaccion_id') : null;

        if ($mensajeTexto === '' || mb_strlen($mensajeTexto) > 2000 || $productoId <= 0 || $receptorId <= 0) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'El mensaje no puede estar vacío ni superar los 2000 caracteres.',
                ])->setStatusCode(400);
            }
            return redirect()->back()->withInput()->with('error', 'El mensaje no puede estar vacío ni superar los 2000 caracteres.');
        }

        // Si el mensaje está asociado a una transacción, validar pertenencia y resolver contraparte automáticamente
        if ($transaccionId) {
            $trx = $this->transaccionModel->find($transaccionId);
            if (!$trx) {
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Transacción no encontrada.'])->setStatusCode(404);
                }
                throw PageNotFoundException::forPageNotFound('Transacción no encontrada.');
            }

            if ((int)$trx['comprador_id'] !== $userId && (int)$trx['vendedor_id'] !== $userId && $userRol !== 'admin') {
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Acceso denegado.'])->setStatusCode(403);
                }
                return redirect()->to(site_url('solicitudes'))->with('error', 'Acceso denegado: no formas parte de esta operación.');
            }

            $contraparteEsperada = ((int)$trx['comprador_id'] === $userId) ? (int)$trx['vendedor_id'] : (int)$trx['comprador_id'];
            if ($receptorId !== $contraparteEsperada && $userRol !== 'admin') {
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
            if ($userNombre === '') {
                $emisor     = $this->userModel->find($userId);
                $userNombre = $emisor['nombre'] ?? 'Tú';
            }

            return $this->response
                ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->setJSON([
                    'status'  => 'success',
                    'message' => 'Mensaje enviado correctamente.',
                    'data'    => [
                        'id'            => (int) $insertId,
                        'mensaje'       => $mensajeTexto,
                        'emisor_id'     => $userId,
                        'emisor_nombre' => $userNombre,
                        'created_at'    => date('d/m/Y H:i'),
                        'es_propio'     => true,
                        'leido'         => 0,
                    ],
                ]);
        }

        // Redireccionar al hilo correspondiente en formulario tradicional
        if ($transaccionId) {
            return redirect()->to(site_url("mensajes/chat/transaccion/{$transaccionId}"))
                             ->with('success', '¡Mensaje enviado a la contraparte!');
        }

        return redirect()->to(site_url("mensajes/chat/producto/{$productoId}/{$receptorId}"))
                         ->with('success', '¡Consulta enviada al vendedor del lote!');
    }

    /**
     * Vista de chat dedicada para una transacción comercial
     */
    public function chatTransaccion($transaccionId)
    {
        $userId = (int) session()->get('user_id');
        $transaccion = $this->transaccionModel->select('transacciones.*, 
                                                        p.nombre AS producto_nombre, 
                                                        p.tipo_polimero,
                                                        c.nombre AS comprador_nombre, 
                                                        v.nombre AS vendedor_nombre')
                                             ->join('productos p', 'p.id = transacciones.producto_id', 'left')
                                             ->join('usuarios c', 'c.id = transacciones.comprador_id', 'left')
                                             ->join('usuarios v', 'v.id = transacciones.vendedor_id', 'left')
                                             ->find($transaccionId);

        if (!$transaccion) {
            throw PageNotFoundException::forPageNotFound('Operación comercial no encontrada.');
        }

        if ((int)$transaccion['comprador_id'] !== $userId && (int)$transaccion['vendedor_id'] !== $userId && session()->get('rol') !== 'admin') {
            return redirect()->to(site_url('mensajes'))->with('error', 'Acceso denegado: no eres parte de esta operación.');
        }

        $esComprador       = ((int)$transaccion['comprador_id'] === $userId);
        $otraEmpresaId     = $esComprador ? (int)$transaccion['vendedor_id'] : (int)$transaccion['comprador_id'];
        $otraEmpresaNombre = $esComprador ? $transaccion['vendedor_nombre'] : $transaccion['comprador_nombre'];

        // Marcar mensajes como leídos
        $this->mensajeModel->marcarLeidosTransaccion((int)$transaccionId, $userId);

        $mensajes = $this->mensajeModel->getMensajesPorTransaccion((int)$transaccionId);

        return view('mensajes/conversacion', [
            'pageTitle'     => 'Chat: ' . esc($otraEmpresaNombre) . ' | MateriaX',
            'tipo'          => 'transaccion',
            'transaccion'   => $transaccion,
            'producto'      => null,
            'otraEmpresa'   => ['id' => $otraEmpresaId, 'nombre' => $otraEmpresaNombre],
            'mensajes'      => $mensajes,
            'userId'        => $userId,
            'transaccionId' => (int) $transaccionId,
            'productoId'    => (int) $transaccion['producto_id'],
            'receptorId'    => $otraEmpresaId,
        ]);
    }

    /**
     * Vista de chat dedicada para consultas sobre un lote de material
     */
    public function chatProducto($productoId, $otraEmpresaId)
    {
        $userId   = (int) session()->get('user_id');
        $producto = $this->productoModel->getProductosConUsuario((int)$productoId);

        if (!$producto) {
            throw PageNotFoundException::forPageNotFound('Lote de polímero no encontrado.');
        }

        $duenioId = (int) $producto['user_id'];
        $esDuenio = ($duenioId === $userId);
        $otraId   = (int) $otraEmpresaId;

        if ($esDuenio) {
            // Si el dueño del lote abre un link donde otraEmpresaId es su propio ID, redirigir al último interesado real
            if ($otraId === $userId || $otraId <= 0) {
                $ultimoInteresado = $this->mensajeModel
                    ->where('producto_id', (int)$productoId)
                    ->where('transaccion_id', null)
                    ->groupStart()
                        ->where('emisor_id !=', $userId)
                        ->orWhere('receptor_id !=', $userId)
                    ->groupEnd()
                    ->orderBy('id', 'DESC')
                    ->first();

                if ($ultimoInteresado) {
                    $interesadoRealId = ((int)$ultimoInteresado['emisor_id'] === $userId)
                        ? (int)$ultimoInteresado['receptor_id']
                        : (int)$ultimoInteresado['emisor_id'];
                    if ($interesadoRealId > 0 && $interesadoRealId !== $userId) {
                        return redirect()->to(site_url('mensajes/chat/producto/' . (int)$productoId . '/' . $interesadoRealId));
                    }
                }

                return redirect()->to(site_url('productos/ver/' . (int)$productoId . '#consultas'))
                                 ->with('error', 'Este lote te pertenece. Aquí verás las consultas cuando otra empresa te escriba.');
            }
            $contraparteId = $otraId;
        } else {
            // Si no soy el dueño del lote, mi contraparte siempre es el titular del lote ($duenioId)
            $contraparteId = $duenioId;
        }

        $otraEmpresa = $this->userModel->find($contraparteId);
        if (!$otraEmpresa) {
            throw PageNotFoundException::forPageNotFound('Empresa de contacto no encontrada.');
        }

        // Marcar consultas como leídas
        $this->mensajeModel->marcarLeidosProducto((int)$productoId, $contraparteId, $userId);

        $mensajes = $this->mensajeModel->getMensajesPorProducto((int)$productoId, $userId, $contraparteId);

        return view('mensajes/conversacion', [
            'pageTitle'     => 'Consulta: ' . esc($producto['nombre']) . ' | MateriaX',
            'tipo'          => 'producto',
            'transaccion'   => null,
            'producto'      => $producto,
            'otraEmpresa'   => ['id' => $contraparteId, 'nombre' => $otraEmpresa['nombre']],
            'mensajes'      => $mensajes,
            'userId'        => $userId,
            'transaccionId' => null,
            'productoId'    => (int) $productoId,
            'receptorId'    => $contraparteId,
        ]);
    }

    /**
     * Endpoint AJAX para polling en tiempo real: devuelve mensajes nuevos a partir de un ID
     */
    public function nuevos()
    {
        $userId  = (int) session()->get('user_id');
        $userRol = (string) session()->get('rol');

        // Liberar sesión inmediatamente para no bloquear otras peticiones concurrentes
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $transaccionId = $this->request->getGet('transaccion_id') ? (int) $this->request->getGet('transaccion_id') : null;
        $productoId    = $this->request->getGet('producto_id') ? (int) $this->request->getGet('producto_id') : null;
        $otraEmpresaId = $this->request->getGet('otra_empresa_id') ? (int) $this->request->getGet('otra_empresa_id') : null;
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
            if ((int)$trx['comprador_id'] !== $userId && (int)$trx['vendedor_id'] !== $userId && $userRol !== 'admin') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado.'])->setStatusCode(403);
            }
        }

        $builder = $this->mensajeModel->builder();
        $builder->select('mensajes_negociacion.*, emisor.nombre AS emisor_nombre');
        $builder->join('usuarios emisor', 'emisor.id = mensajes_negociacion.emisor_id', 'left');

        if ($transaccionId) {
            $builder->where('mensajes_negociacion.transaccion_id', $transaccionId);
        } else {
            $builder->where('mensajes_negociacion.producto_id', $productoId)
                    ->where('mensajes_negociacion.transaccion_id', null);

            if ($otraEmpresaId && $otraEmpresaId > 0) {
                $builder->groupStart()
                            ->groupStart()
                                ->where('mensajes_negociacion.emisor_id', $userId)
                                ->where('mensajes_negociacion.receptor_id', $otraEmpresaId)
                            ->groupEnd()
                            ->orGroupStart()
                                ->where('mensajes_negociacion.emisor_id', $otraEmpresaId)
                                ->where('mensajes_negociacion.receptor_id', $userId)
                            ->groupEnd()
                        ->groupEnd();
            } else {
                $builder->groupStart()
                            ->where('mensajes_negociacion.emisor_id', $userId)
                            ->orWhere('mensajes_negociacion.receptor_id', $userId)
                        ->groupEnd();
            }
        }

        $builder->where('mensajes_negociacion.id >', $ultimoId);
        $builder->orderBy('mensajes_negociacion.id', 'ASC');
        $mensajesNuevos = $builder->get()->getResultArray();

        $nuevosFormateados = [];
        $idsParaMarcarLeidos = [];

        foreach ($mensajesNuevos as $msg) {
            $msgId = (int) $msg['id'];
            if ((int)$msg['receptor_id'] === $userId && (int)$msg['leido'] === 0) {
                $idsParaMarcarLeidos[] = $msgId;
                $msg['leido'] = 1;
            }

            $nuevosFormateados[] = [
                'id'            => $msgId,
                'mensaje'       => (string) $msg['mensaje'],
                'emisor_id'     => (int) $msg['emisor_id'],
                'emisor_nombre' => (string) ($msg['emisor_nombre'] ?? 'Empresa'),
                'created_at'    => !empty($msg['created_at']) ? date('d/m/Y H:i', strtotime($msg['created_at'])) : date('d/m/Y H:i'),
                'es_propio'     => ((int)$msg['emisor_id'] === $userId),
                'leido'         => (int) $msg['leido'],
            ];
        }

        if (!empty($idsParaMarcarLeidos)) {
            $this->mensajeModel->whereIn('id', $idsParaMarcarLeidos)
                               ->set(['leido' => 1, 'fecha_lectura' => date('Y-m-d H:i:s')])
                               ->update();
        }

        // Consultar cuál es el último mensaje propio que ya fue leído por la contraparte
        $leidoBuilder = $this->mensajeModel->builder();
        $leidoBuilder->selectMax('id', 'max_leido_id')
                     ->where('emisor_id', $userId)
                     ->where('leido', 1);
        if ($transaccionId) {
            $leidoBuilder->where('transaccion_id', $transaccionId);
        } else {
            $leidoBuilder->where('producto_id', $productoId)->where('transaccion_id', null);
            if ($otraEmpresaId && $otraEmpresaId > 0) {
                $leidoBuilder->where('receptor_id', $otraEmpresaId);
            }
        }
        $leidoRow   = $leidoBuilder->get()->getRowArray();
        $maxLeidoId = (int) ($leidoRow['max_leido_id'] ?? 0);

        return $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setJSON([
                'status'          => 'success',
                'nuevos'          => $nuevosFormateados,
                'max_leido_id'    => $maxLeidoId,
                'total_no_leidos' => $this->mensajeModel->contarNoLeidos($userId),
            ]);
    }

    /**
     * Endpoint AJAX para actualizar en vivo la Bandeja de Entrada (/mensajes) sin recargar
     */
    public function bandeja()
    {
        $userId = (int) session()->get('user_id');

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $conversaciones = $this->mensajeModel->getConversacionesUsuario($userId);
        $totalNoLeidos  = $this->mensajeModel->contarNoLeidos($userId);

        $items = [];
        foreach ($conversaciones as $conv) {
            $linkDestino = !empty($conv['transaccion_id'])
                ? site_url('mensajes/chat/transaccion/' . $conv['transaccion_id'])
                : site_url('mensajes/chat/producto/' . $conv['producto_id'] . '/' . $conv['otra_empresa_id']);

            $items[] = [
                'clave'               => $conv['clave'],
                'transaccion_id'      => $conv['transaccion_id'] ? (int) $conv['transaccion_id'] : null,
                'producto_id'         => (int) $conv['producto_id'],
                'producto_nombre'     => (string) $conv['producto_nombre'],
                'tipo_polimero'       => (string) ($conv['tipo_polimero'] ?? ''),
                'codigo_operacion'    => $conv['codigo_operacion'],
                'transaccion_estado'  => $conv['transaccion_estado'],
                'otra_empresa_id'     => (int) $conv['otra_empresa_id'],
                'otra_empresa_nombre' => (string) $conv['otra_empresa_nombre'],
                'ultimo_mensaje'      => mb_strimwidth((string) $conv['ultimo_mensaje'], 0, 110, '...'),
                'es_ultimo_mio'       => ((int) $conv['ultimo_emisor_id'] === $userId),
                'ultima_fecha'        => !empty($conv['ultima_fecha']) ? date('d/m/Y H:i', strtotime($conv['ultima_fecha'])) : '',
                'no_leidos'           => (int) $conv['no_leidos'],
                'total_mensajes'      => (int) ($conv['total_mensajes'] ?? 1),
                'mensajes_recientes'  => $conv['mensajes_recientes'] ?? [],
                'link_destino'        => $linkDestino,
            ];
        }

        return $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setJSON([
                'status'          => 'success',
                'total_no_leidos' => $totalNoLeidos,
                'total_hilos'     => count($items),
                'conversaciones'  => $items,
            ]);
    }

    /**
     * Endpoint AJAX para conteo dinámico de mensajes no leídos (badge de cabecera)
     */
    public function conteoNoLeidos()
    {
        $userId = (int) session()->get('user_id');

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $total = $this->mensajeModel->contarNoLeidos($userId);
        return $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setJSON(['status' => 'success', 'no_leidos' => $total]);
    }

    /**
     * Redirige al chat de una transacción
     */
    public function ver($transaccionId = null): RedirectResponse
    {
        if (empty($transaccionId) || !is_numeric($transaccionId)) {
            throw PageNotFoundException::forPageNotFound('Identificador de operación inválido.');
        }

        return redirect()->to(site_url("mensajes/chat/transaccion/{$transaccionId}"));
    }
}
