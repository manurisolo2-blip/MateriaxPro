<?php

namespace App\Controllers;

use App\Models\{MensajeModel, ProductoModel, TransaccionModel, UserModel};
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Mensajes extends BaseController
{
    protected MensajeModel $mensajeModel;
    protected TransaccionModel $transaccionModel;
    protected ProductoModel $productoModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->mensajeModel     = new MensajeModel();
        $this->transaccionModel = new TransaccionModel();
        $this->productoModel    = new ProductoModel();
        $this->userModel        = new UserModel();
    }

    public function index()
    {
        $userId = $this->userId();
        return view('mensajes/index', [
            'pageTitle'      => 'Bandeja de Mensajes y Consultas | MateriaX',
            'conversaciones' => $this->mensajeModel->getConversacionesUsuario($userId),
            'totalNoLeidos'  => $this->mensajeModel->contarNoLeidos($userId),
            'userId'         => $userId,
        ]);
    }

    public function enviar()
    {
        $userId = $this->userId();
        $isAjax = $this->isAjax();
        if ($isAjax) {
            $this->releaseSession();
        }

        $fail = fn (string $msg, int $code = 400, ?string $to = null) => $isAjax
            ? $this->noStoreJson(['status' => 'error', 'message' => $msg], $code)
            : ($to ? redirect()->to(site_url($to))->with('error', $msg) : redirect()->back()->withInput()->with('error', $msg));

        $texto         = trim((string) $this->request->getPost('mensaje'));
        $productoId    = (int) $this->request->getPost('producto_id');
        $receptorId    = (int) $this->request->getPost('receptor_id');
        $transaccionId = $this->request->getPost('transaccion_id') ? (int) $this->request->getPost('transaccion_id') : null;

        if ($texto === '' || mb_strlen($texto) > 2000 || $productoId <= 0 || $receptorId <= 0) {
            return $fail('El mensaje no puede estar vacío ni superar los 2000 caracteres.');
        }

        if ($transaccionId) {
            if (!($trx = $this->transaccionModel->find($transaccionId))) {
                return $isAjax ? $fail('Transacción no encontrada.', 404) : throw PageNotFoundException::forPageNotFound('Transacción no encontrada.');
            }
            if ((int) $trx['comprador_id'] !== $userId && (int) $trx['vendedor_id'] !== $userId && !$this->isAdmin()) {
                return $fail($isAjax ? 'Acceso denegado.' : 'Acceso denegado: no formas parte de esta operación.', 403, 'solicitudes');
            }
            $contraparte = ((int) $trx['comprador_id'] === $userId) ? (int) $trx['vendedor_id'] : (int) $trx['comprador_id'];
            if ($receptorId !== $contraparte && !$this->isAdmin()) {
                $receptorId = $contraparte;
            }
        } elseif (!$this->productoModel->find($productoId)) {
            return $isAjax ? $fail('Lote no encontrado.', 404) : throw PageNotFoundException::forPageNotFound('Lote de polímero no encontrado.');
        }

        if ($receptorId === $userId) {
            return $fail('No puedes enviarte mensajes a tu propia cuenta.');
        }

        $insertId = $this->mensajeModel->insert([
            'producto_id'    => $productoId,
            'transaccion_id' => $transaccionId,
            'emisor_id'      => $userId,
            'receptor_id'    => $receptorId,
            'mensaje'        => $texto,
            'leido'          => 0,
            'fecha_lectura'  => null,
        ]);
        if (!$insertId) {
            return $fail($isAjax ? 'No se pudo guardar el mensaje.' : 'No se pudo enviar el mensaje.', 500);
        }

        if ($isAjax) {
            $nombre = (string) session()->get('nombre');
            if ($nombre === '') {
                $nombre = $this->userModel->find($userId)['nombre'] ?? 'Tú';
            }
            return $this->noStoreJson([
                'status'  => 'success',
                'message' => 'Mensaje enviado correctamente.',
                'data'    => [
                    'id'            => (int) $insertId,
                    'mensaje'       => $texto,
                    'emisor_id'     => $userId,
                    'emisor_nombre' => $nombre,
                    'created_at'    => date('d/m/Y H:i'),
                    'es_propio'     => true,
                    'leido'         => 0,
                ],
            ]);
        }

        return $transaccionId
            ? redirect()->to(site_url("mensajes/chat/transaccion/{$transaccionId}"))->with('success', '¡Mensaje enviado a la contraparte!')
            : redirect()->to(site_url("mensajes/chat/producto/{$productoId}/{$receptorId}"))->with('success', '¡Consulta enviada al vendedor del lote!');
    }

    public function chatTransaccion($transaccionId)
    {
        $userId = $this->userId();
        $trx    = $this->transaccionModel
            ->select(
                'transacciones.*, p.nombre AS producto_nombre, p.tipo_polimero,
                 c.nombre AS comprador_nombre, c.cuit AS comprador_cuit, c.email AS comprador_email, c.telefono AS comprador_telefono,
                 v.nombre AS vendedor_nombre, v.cuit AS vendedor_cuit, v.email AS vendedor_email, v.telefono AS vendedor_telefono'
            )
            ->join('productos p', 'p.id = transacciones.producto_id', 'left')
            ->join('usuarios c', 'c.id = transacciones.comprador_id', 'left')
            ->join('usuarios v', 'v.id = transacciones.vendedor_id', 'left')
            ->find($transaccionId);

        if (!$trx) {
            throw PageNotFoundException::forPageNotFound('Operación comercial no encontrada.');
        }
        if ((int) $trx['comprador_id'] !== $userId && (int) $trx['vendedor_id'] !== $userId && !$this->isAdmin()) {
            return redirect()->to(site_url('mensajes'))->with('error', 'Acceso denegado: no eres parte de esta operación.');
        }

        $esComprador       = ((int) $trx['comprador_id'] === $userId);
        $otraEmpresaId     = $esComprador ? (int) $trx['vendedor_id'] : (int) $trx['comprador_id'];
        $otraEmpresaNombre = $esComprador ? $trx['vendedor_nombre'] : $trx['comprador_nombre'];
        $prefix            = $esComprador ? 'vendedor_' : 'comprador_';

        $this->mensajeModel->marcarLeidosTransaccion((int) $transaccionId, $userId);

        return view('mensajes/conversacion', [
            'pageTitle'     => 'Chat: ' . esc($otraEmpresaNombre) . ' | MateriaX',
            'tipo'          => 'transaccion',
            'transaccion'   => $trx,
            'producto'      => null,
            'otraEmpresa'   => [
                'id'       => $otraEmpresaId,
                'nombre'   => $otraEmpresaNombre,
                'cuit'     => $trx[$prefix . 'cuit'] ?? null,
                'email'    => $trx[$prefix . 'email'] ?? null,
                'telefono' => $trx[$prefix . 'telefono'] ?? null,
            ],
            'mensajes'      => $this->mensajeModel->getMensajesPorTransaccion((int) $transaccionId),
            'userId'        => $userId,
            'transaccionId' => (int) $transaccionId,
            'productoId'    => (int) $trx['producto_id'],
            'receptorId'    => $otraEmpresaId,
        ]);
    }

    public function chatProducto($productoId, $otraEmpresaId)
    {
        $userId   = $this->userId();
        $producto = $this->productoModel->getProductosConUsuario((int) $productoId);
        if (!$producto) {
            throw PageNotFoundException::forPageNotFound('Lote de polímero no encontrado.');
        }

        $duenioId = (int) $producto['user_id'];
        $otraId   = (int) $otraEmpresaId;

        if ($duenioId === $userId) {
            if ($otraId === $userId || $otraId <= 0) {
                $ultimo = $this->mensajeModel->where(['producto_id' => (int) $productoId, 'transaccion_id' => null])
                    ->groupStart()->where('emisor_id !=', $userId)->orWhere('receptor_id !=', $userId)->groupEnd()
                    ->orderBy('id', 'DESC')->first();
                if ($ultimo) {
                    $realId = ((int) $ultimo['emisor_id'] === $userId) ? (int) $ultimo['receptor_id'] : (int) $ultimo['emisor_id'];
                    if ($realId > 0 && $realId !== $userId) {
                        return redirect()->to(site_url("mensajes/chat/producto/{$productoId}/{$realId}"));
                    }
                }
                return redirect()->to(site_url("productos/ver/{$productoId}#consultas"))
                    ->with('error', 'Este lote te pertenece. Aquí verás las consultas cuando otra empresa te escriba.');
            }
            $contraparteId = $otraId;
        } else {
            $contraparteId = $duenioId;
        }

        if (!($otraEmpresa = $this->userModel->find($contraparteId))) {
            throw PageNotFoundException::forPageNotFound('Empresa de contacto no encontrada.');
        }

        $this->mensajeModel->marcarLeidosProducto((int) $productoId, $contraparteId, $userId);

        return view('mensajes/conversacion', [
            'pageTitle'     => 'Consulta: ' . esc($producto['nombre']) . ' | MateriaX',
            'tipo'          => 'producto',
            'transaccion'   => null,
            'producto'      => $producto,
            'otraEmpresa'   => [
                'id'       => $contraparteId,
                'nombre'   => $otraEmpresa['nombre'],
                'cuit'     => $otraEmpresa['cuit'] ?? null,
                'email'    => $otraEmpresa['email'] ?? null,
                'telefono' => $otraEmpresa['telefono'] ?? null,
            ],
            'mensajes'      => $this->mensajeModel->getMensajesPorProducto((int) $productoId, $userId, $contraparteId),
            'userId'        => $userId,
            'transaccionId' => null,
            'productoId'    => (int) $productoId,
            'receptorId'    => $contraparteId,
        ]);
    }

    public function nuevos()
    {
        $userId = $this->userId();
        $this->releaseSession();

        $transaccionId = $this->request->getGet('transaccion_id') ? (int) $this->request->getGet('transaccion_id') : null;
        $productoId    = $this->request->getGet('producto_id') ? (int) $this->request->getGet('producto_id') : null;
        $otraEmpresaId = $this->request->getGet('otra_empresa_id') ? (int) $this->request->getGet('otra_empresa_id') : null;
        $ultimoId      = (int) ($this->request->getGet('ultimo_id') ?? 0);

        if (!$transaccionId && !$productoId) {
            return $this->noStoreJson(['status' => 'error', 'message' => 'Parámetros insuficientes.'], 400);
        }

        if ($transaccionId) {
            if (!($trx = $this->transaccionModel->find($transaccionId))) {
                return $this->noStoreJson(['status' => 'error', 'message' => 'Transacción no encontrada.'], 404);
            }
            if ((int) $trx['comprador_id'] !== $userId && (int) $trx['vendedor_id'] !== $userId && !$this->isAdmin()) {
                return $this->noStoreJson(['status' => 'error', 'message' => 'No autorizado.'], 403);
            }
        }

        $b = $this->mensajeModel->builder()
            ->select('mensajes_negociacion.*, emisor.nombre AS emisor_nombre')
            ->join('usuarios emisor', 'emisor.id = mensajes_negociacion.emisor_id', 'left');

        if ($transaccionId) {
            $b->where('mensajes_negociacion.transaccion_id', $transaccionId);
        } else {
            $b->where('mensajes_negociacion.producto_id', $productoId)->where('mensajes_negociacion.transaccion_id', null);
            if ($otraEmpresaId && $otraEmpresaId > 0) {
                $b->groupStart()
                    ->groupStart()->where('mensajes_negociacion.emisor_id', $userId)->where('mensajes_negociacion.receptor_id', $otraEmpresaId)->groupEnd()
                    ->orGroupStart()->where('mensajes_negociacion.emisor_id', $otraEmpresaId)->where('mensajes_negociacion.receptor_id', $userId)->groupEnd()
                  ->groupEnd();
            } else {
                $b->groupStart()->where('mensajes_negociacion.emisor_id', $userId)->orWhere('mensajes_negociacion.receptor_id', $userId)->groupEnd();
            }
        }

        $rows = $b->where('mensajes_negociacion.id >', $ultimoId)->orderBy('mensajes_negociacion.id', 'ASC')->get()->getResultArray();
        $nuevos = $idsLeidos = [];

        foreach ($rows as $msg) {
            $msgId = (int) $msg['id'];
            if ((int) $msg['receptor_id'] === $userId && (int) $msg['leido'] === 0) {
                $idsLeidos[] = $msgId;
                $msg['leido'] = 1;
            }
            $nuevos[] = [
                'id'            => $msgId,
                'mensaje'       => (string) $msg['mensaje'],
                'emisor_id'     => (int) $msg['emisor_id'],
                'emisor_nombre' => (string) ($msg['emisor_nombre'] ?? 'Empresa'),
                'created_at'    => !empty($msg['created_at']) ? date('d/m/Y H:i', strtotime($msg['created_at'])) : date('d/m/Y H:i'),
                'es_propio'     => ((int) $msg['emisor_id'] === $userId),
                'leido'         => (int) $msg['leido'],
            ];
        }

        if (!empty($idsLeidos)) {
            $this->mensajeModel->whereIn('id', $idsLeidos)->set(['leido' => 1, 'fecha_lectura' => date('Y-m-d H:i:s')])->update();
        }

        $lb = $this->mensajeModel->builder()->selectMax('id', 'max_leido_id')->where(['emisor_id' => $userId, 'leido' => 1]);
        if ($transaccionId) {
            $lb->where('transaccion_id', $transaccionId);
        } else {
            $lb->where('producto_id', $productoId)->where('transaccion_id', null);
            if ($otraEmpresaId && $otraEmpresaId > 0) {
                $lb->where('receptor_id', $otraEmpresaId);
            }
        }
        $maxLeidoId = (int) ($lb->get()->getRowArray()['max_leido_id'] ?? 0);

        return $this->noStoreJson([
            'status'          => 'success',
            'nuevos'          => $nuevos,
            'max_leido_id'    => $maxLeidoId,
            'total_no_leidos' => $this->mensajeModel->contarNoLeidos($userId),
        ]);
    }

    public function bandeja()
    {
        $userId = $this->userId();
        $this->releaseSession();

        $conversaciones = $this->mensajeModel->getConversacionesUsuario($userId);
        $items          = array_map(static fn ($c) => [
            'clave'               => $c['clave'],
            'transaccion_id'      => $c['transaccion_id'] ? (int) $c['transaccion_id'] : null,
            'producto_id'         => (int) $c['producto_id'],
            'producto_nombre'     => (string) $c['producto_nombre'],
            'tipo_polimero'       => (string) ($c['tipo_polimero'] ?? ''),
            'codigo_operacion'    => $c['codigo_operacion'],
            'transaccion_estado'  => $c['transaccion_estado'],
            'otra_empresa_id'     => (int) $c['otra_empresa_id'],
            'otra_empresa_nombre' => (string) $c['otra_empresa_nombre'],
            'ultimo_mensaje'      => mb_strimwidth((string) $c['ultimo_mensaje'], 0, 110, '...'),
            'es_ultimo_mio'       => ((int) $c['ultimo_emisor_id'] === $userId),
            'ultima_fecha'        => !empty($c['ultima_fecha']) ? date('d/m/Y H:i', strtotime($c['ultima_fecha'])) : '',
            'no_leidos'           => (int) $c['no_leidos'],
            'total_mensajes'      => (int) ($c['total_mensajes'] ?? 1),
            'mensajes_recientes'  => $c['mensajes_recientes'] ?? [],
            'link_destino'        => !empty($c['transaccion_id'])
                ? site_url('mensajes/chat/transaccion/' . $c['transaccion_id'])
                : site_url('mensajes/chat/producto/' . $c['producto_id'] . '/' . $c['otra_empresa_id']),
        ], $conversaciones);

        return $this->noStoreJson([
            'status'          => 'success',
            'total_no_leidos' => $this->mensajeModel->contarNoLeidos($userId),
            'total_hilos'     => count($items),
            'conversaciones'  => $items,
        ]);
    }

    public function conteoNoLeidos()
    {
        $userId = $this->userId();
        $this->releaseSession();
        return $this->noStoreJson(['status' => 'success', 'no_leidos' => $this->mensajeModel->contarNoLeidos($userId)]);
    }

    public function ver($transaccionId = null): RedirectResponse
    {
        if (empty($transaccionId) || !is_numeric($transaccionId)) {
            throw PageNotFoundException::forPageNotFound('Identificador de operación inválido.');
        }
        return redirect()->to(site_url("mensajes/chat/transaccion/{$transaccionId}"));
    }
}
