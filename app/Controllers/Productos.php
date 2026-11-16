<?php

namespace App\Controllers;

use App\Models\{MensajeModel, PlantaModel, ProductoModel};
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Productos extends BaseController
{
    protected ?ProductoModel $productoModel = null;
    protected ?PlantaModel $plantaModel = null;

    protected array $rules = [
        'nombre'          => 'required|min_length[3]|max_length[150]',
        'tipo_polimero'   => 'required|max_length[50]',
        'cantidad_kg'     => 'required|numeric|greater_than[0]',
        'precio_unitario' => 'required|numeric|greater_than_equal_to[0]',
        'ubicacion'       => 'required|min_length[2]|max_length[100]',
        'estado'          => 'required|in_list[Disponible,Reservado,Vendido]',
        'planta_id'       => 'permit_empty|is_natural_no_zero',
    ];

    protected function getProductoModel(): ProductoModel
    {
        return $this->productoModel ??= new ProductoModel();
    }

    protected function getPlantaModel(): PlantaModel
    {
        return $this->plantaModel ??= new PlantaModel();
    }

    public function index()
    {
        $tipoPolimero = $this->request->getGet('polimero');
        $busqueda     = $this->request->getGet('q');

        $b = $this->getProductoModel()->select(
            'productos.*, usuarios.nombre as empresa_nombre, usuarios.email as empresa_email,
             usuarios.telefono as empresa_telefono, plantas_industriales.nombre_planta'
        )
            ->join('usuarios', 'usuarios.id = productos.user_id', 'left')
            ->join('plantas_industriales', 'plantas_industriales.id = productos.planta_id', 'left');

        if (!empty($tipoPolimero)) {
            $b->where('productos.tipo_polimero', $tipoPolimero);
        }
        if (!empty($busqueda)) {
            $b->groupStart()
                ->like('productos.nombre', $busqueda)
                ->orLike('productos.descripcion', $busqueda)
                ->orLike('productos.ubicacion', $busqueda)
              ->groupEnd();
        }

        return view('productos/index', [
            'pageTitle'    => 'Inventario de Polímeros Industriales | MateriaX',
            'productos'    => $b->orderBy('productos.created_at', 'DESC')->findAll(),
            'filtroActual' => $tipoPolimero,
            'busqueda'     => $busqueda,
        ]);
    }

    public function crear()
    {
        return view('productos/crear', [
            'pageTitle' => 'Publicar Excedente Industrial | MateriaX',
            'plantas'   => $this->getPlantaModel()->getPlantasPorEmpresa($this->userId(), true),
        ]);
    }

    public function guardar()
    {
        if (!$this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data            = $this->getFormData();
        $data['user_id'] = $this->userId();

        if (!$this->getProductoModel()->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al registrar el lote de material.');
        }

        return redirect()->to(site_url('productos'))->with('success', '¡El lote de material fue publicado exitosamente en la red!');
    }

    public function ver($id = null)
    {
        if (empty($id) || !($producto = $this->getProductoModel()->getProductosConUsuario($id))) {
            throw PageNotFoundException::forPageNotFound('El producto solicitado no existe o fue eliminado.');
        }

        $userId             = $this->userId();
        $consultas          = [];
        $consultasRecibidas = [];
        if ($userId > 0) {
            $mensajeModel = new MensajeModel();
            if ($userId !== (int) $producto['user_id']) {
                // Marcar como leídas las respuestas del vendedor al entrar a la ficha del lote
                $mensajeModel->marcarLeidosProducto((int) $id, (int) $producto['user_id'], $userId);
                $consultas = $mensajeModel->getMensajesPorProducto((int) $id, $userId, (int) $producto['user_id']);
            } else {
                $consultasRecibidas = $mensajeModel->getMensajesPorProducto((int) $id);
            }
        }

        return view('productos/ver', [
            'pageTitle'          => esc($producto['nombre']) . ' | MateriaX',
            'producto'           => $producto,
            'userId'             => $userId,
            'consultas'          => $consultas,
            'consultasRecibidas' => $consultasRecibidas,
        ]);
    }

    public function editar($id = null)
    {
        if (empty($id) || !($producto = $this->getProductoModel()->find($id))) {
            throw PageNotFoundException::forPageNotFound('El producto solicitado no existe.');
        }
        if ($r = $this->checkOwnership($producto, 'editar')) {
            return $r;
        }

        return view('productos/editar', [
            'pageTitle' => 'Editar: ' . esc($producto['nombre']) . ' | MateriaX',
            'producto'  => $producto,
            'plantas'   => $this->getPlantaModel()->getPlantasPorEmpresa((int) $producto['user_id']),
        ]);
    }

    public function actualizar($id = null)
    {
        $productoModel = $this->getProductoModel();
        if (empty($id) || !($producto = $productoModel->find($id))) {
            return redirect()->to(site_url('productos'))->with('error', 'El producto solicitado no existe.');
        }
        if ($r = $this->checkOwnership($producto, 'modificar')) {
            return $r;
        }
        if (!$this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $productoModel->update($id, $this->getFormData());
        return redirect()->to(site_url('productos'))->with('success', '¡Los datos técnicos y comerciales del lote fueron actualizados con éxito!');
    }

    public function confirmarEliminar($id = null)
    {
        $producto = !empty($id)
            ? $this->getProductoModel()->select('productos.*, usuarios.nombre as empresa_nombre')->join('usuarios', 'usuarios.id = productos.user_id', 'left')->find($id)
            : null;
        if (!$producto) {
            return redirect()->to(site_url('productos'))->with('error', 'El lote de material no existe o ya fue eliminado.');
        }
        if ($r = $this->checkOwnership($producto, 'dar de baja')) {
            return $r;
        }

        return view('productos/confirmar_eliminar', [
            'pageTitle' => 'Confirmar Eliminación de Lote #' . $id . ' | MateriaX',
            'producto'  => $producto,
        ]);
    }

    public function eliminar($id = null)
    {
        $productoModel = $this->getProductoModel();
        if (empty($id) || !($producto = $productoModel->find($id))) {
            return redirect()->to(site_url('productos'))->with('error', 'El lote no existe o ya fue eliminado previamente.');
        }
        if ($r = $this->checkOwnership($producto, 'eliminar')) {
            return $r;
        }

        $productoModel->delete($id);
        return redirect()->to(site_url('productos'))->with('success', "El lote de material #{$id} fue retirado y eliminado definitivamente de la red.");
    }

    protected function checkOwnership(array $producto, string $verb): ?RedirectResponse
    {
        if ((int) $producto['user_id'] !== $this->userId() && !$this->isAdmin()) {
            return redirect()->to(site_url('productos'))
                ->with('error', "Acceso denegado: El lote #{$producto['id']} pertenece a otra empresa. Sólo puedes {$verb} publicaciones de tu propia autoría.");
        }
        return null;
    }

    protected function getFormData(): array
    {
        $plantaId = $this->request->getPost('planta_id');
        return [
            'nombre'          => trim((string) $this->request->getPost('nombre')),
            'tipo_polimero'   => trim((string) $this->request->getPost('tipo_polimero')),
            'cantidad_kg'     => (float) $this->request->getPost('cantidad_kg'),
            'precio_unitario' => (float) $this->request->getPost('precio_unitario'),
            'ubicacion'       => trim((string) $this->request->getPost('ubicacion')),
            'planta_id'       => (!empty($plantaId) && is_numeric($plantaId)) ? (int) $plantaId : null,
            'descripcion'     => trim((string) ($this->request->getPost('descripcion') ?? '')),
            'estado'          => trim((string) $this->request->getPost('estado')),
        ];
    }
}
