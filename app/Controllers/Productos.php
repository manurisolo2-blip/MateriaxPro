<?php

namespace App\Controllers;

use App\Models\ProductoModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Productos extends BaseController
{
    protected ProductoModel $productoModel;
    protected $helpers = ['form', 'url'];

    protected array $rules = [
        'nombre'          => 'required|min_length[3]|max_length[150]',
        'tipo_polimero'   => 'required|max_length[50]',
        'cantidad_kg'     => 'required|numeric|greater_than[0]',
        'precio_unitario' => 'required|numeric|greater_than_equal_to[0]',
        'ubicacion'       => 'required|min_length[2]|max_length[100]',
        'estado'          => 'required|in_list[Disponible,Reservado,Vendido]',
    ];

    public function __construct()
    {
        $this->productoModel = new ProductoModel();
    }

    public function index()
    {
        $tipoPolimero = $this->request->getGet('polimero');
        $busqueda     = $this->request->getGet('q');

        $builder = $this->productoModel->select('productos.*, usuarios.nombre as empresa_nombre, usuarios.email as empresa_email, usuarios.telefono as empresa_telefono')
                                       ->join('usuarios', 'usuarios.id = productos.user_id', 'left');

        if (!empty($tipoPolimero)) {
            $builder->where('productos.tipo_polimero', $tipoPolimero);
        }
        if (!empty($busqueda)) {
            $builder->groupStart()
                    ->like('productos.nombre', $busqueda)
                    ->orLike('productos.descripcion', $busqueda)
                    ->orLike('productos.ubicacion', $busqueda)
                    ->groupEnd();
        }

        return view('productos/index', [
            'pageTitle'    => 'Inventario de Polímeros Industriales | MateriaX',
            'productos'    => $builder->orderBy('productos.created_at', 'DESC')->findAll(),
            'filtroActual' => $tipoPolimero,
            'busqueda'     => $busqueda,
        ]);
    }

    public function crear()
    {
        return view('productos/crear', ['pageTitle' => 'Publicar Excedente Industrial | MateriaX']);
    }

    public function guardar()
    {
        if (!$this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->getFormData();
        $data['user_id'] = (int) session()->get('user_id');

        if (!$this->productoModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al registrar el lote de material.');
        }

        return redirect()->to(site_url('productos'))->with('success', '¡El lote de material fue publicado exitosamente en la red!');
    }

    public function ver($id = null)
    {
        if (empty($id) || !($producto = $this->productoModel->getProductosConUsuario($id))) {
            throw PageNotFoundException::forPageNotFound('El producto solicitado no existe o fue eliminado.');
        }

        return view('productos/ver', [
            'pageTitle' => esc($producto['nombre']) . ' | MateriaX',
            'producto'  => $producto,
        ]);
    }

    public function editar($id = null)
    {
        $producto = $this->findOrNotFound($id);
        if ($redirect = $this->checkOwnership($producto, 'editar')) return $redirect;

        return view('productos/editar', [
            'pageTitle' => 'Editar: ' . esc($producto['nombre']) . ' | MateriaX',
            'producto'  => $producto,
        ]);
    }

    public function actualizar($id = null)
    {
        if (empty($id) || !($producto = $this->productoModel->find($id))) {
            return redirect()->to(site_url('productos'))->with('error', 'El producto solicitado no existe.');
        }
        if ($redirect = $this->checkOwnership($producto, 'modificar')) return $redirect;

        if (!$this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->productoModel->update($id, $this->getFormData());
        return redirect()->to(site_url('productos'))->with('success', '¡Los datos técnicos y comerciales del lote fueron actualizados con éxito!');
    }

    public function confirmarEliminar($id = null)
    {
        if (empty($id) || !($producto = $this->productoModel->select('productos.*, usuarios.nombre as empresa_nombre')->join('usuarios', 'usuarios.id = productos.user_id', 'left')->find($id))) {
            return redirect()->to(site_url('productos'))->with('error', 'El lote de material no existe o ya fue eliminado.');
        }
        if ($redirect = $this->checkOwnership($producto, 'dar de baja')) return $redirect;

        return view('productos/confirmar_eliminar', [
            'pageTitle' => 'Confirmar Eliminación de Lote #' . $id . ' | MateriaX',
            'producto'  => $producto,
        ]);
    }

    public function eliminar($id = null)
    {
        if (empty($id) || !($producto = $this->productoModel->find($id))) {
            return redirect()->to(site_url('productos'))->with('error', 'El lote no existe o ya fue eliminado previamente.');
        }
        if ($redirect = $this->checkOwnership($producto, 'eliminar')) return $redirect;

        $this->productoModel->delete($id);
        return redirect()->to(site_url('productos'))->with('success', 'El lote de material #' . $id . ' fue retirado y eliminado definitivamente de la red.');
    }

    protected function checkOwnership(array $producto, string $verb): ?RedirectResponse
    {
        $userId = (int) session()->get('user_id');
        if ((int) $producto['user_id'] !== $userId && session()->get('rol') !== 'admin') {
            return redirect()->to(site_url('productos'))->with('error', "Acceso denegado: El lote #{$producto['id']} pertenece a otra empresa. Sólo puedes {$verb} publicaciones de tu propia autoría.");
        }
        return null;
    }

    protected function findOrNotFound($id): array
    {
        if (empty($id) || !($producto = $this->productoModel->find($id))) {
            throw PageNotFoundException::forPageNotFound('El producto solicitado no existe.');
        }
        return $producto;
    }

    protected function getFormData(): array
    {
        return [
            'nombre'          => trim((string) $this->request->getPost('nombre')),
            'tipo_polimero'   => trim((string) $this->request->getPost('tipo_polimero')),
            'cantidad_kg'     => (float) $this->request->getPost('cantidad_kg'),
            'precio_unitario' => (float) $this->request->getPost('precio_unitario'),
            'ubicacion'       => trim((string) $this->request->getPost('ubicacion')),
            'descripcion'     => trim((string) ($this->request->getPost('descripcion') ?? '')),
            'estado'          => trim((string) $this->request->getPost('estado')),
        ];
    }
}
