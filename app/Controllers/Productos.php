<?php

namespace App\Controllers;

use App\Models\ProductoModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Productos extends BaseController
{
    protected $productoModel;
    protected $helpers = ['form', 'url'];

    public function __construct()
    {
        $this->productoModel = new ProductoModel();
    }

    /**
     * 1. LISTADO DE PRODUCTOS (Visible solo para usuarios logueados)
     */
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

        $productos = $builder->orderBy('productos.created_at', 'DESC')->findAll();

        return view('productos/index', [
            'pageTitle'    => 'Inventario de Polímeros Industriales | MateriaX',
            'productos'    => $productos,
            'filtroActual' => $tipoPolimero,
            'busqueda'     => $busqueda,
        ]);
    }

    /**
     * 2. FORMULARIO PARA CREAR UN NUEVO PRODUCTO
     */
    public function crear()
    {
        return view('productos/crear', [
            'pageTitle' => 'Publicar Excedente Industrial | MateriaX',
        ]);
    }

    /**
     * 3. GUARDAR EL NUEVO PRODUCTO
     */
    public function guardar()
    {
        $rules = [
            'nombre'          => 'required|min_length[3]|max_length[150]',
            'tipo_polimero'   => 'required|max_length[50]',
            'cantidad_kg'     => 'required|numeric|greater_than[0]',
            'precio_unitario' => 'required|numeric|greater_than_equal_to[0]',
            'ubicacion'       => 'required|min_length[2]|max_length[100]',
            'estado'          => 'required|in_list[Disponible,Reservado,Vendido]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'user_id'         => (int) session()->get('user_id'),
            'nombre'          => trim($this->request->getPost('nombre')),
            'tipo_polimero'   => trim($this->request->getPost('tipo_polimero')),
            'cantidad_kg'     => (float) $this->request->getPost('cantidad_kg'),
            'precio_unitario' => (float) $this->request->getPost('precio_unitario'),
            'ubicacion'       => trim($this->request->getPost('ubicacion')),
            'descripcion'     => trim($this->request->getPost('descripcion') ?? ''),
            'estado'          => trim($this->request->getPost('estado')),
        ];

        $insertId = $this->productoModel->insert($data);

        if (!$insertId) {
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al registrar el lote de material.');
        }

        return redirect()->to(site_url('productos'))->with('success', '¡El lote de material fue publicado exitosamente en la red!');
    }

    /**
     * 4. VER DETALLES DE UN PRODUCTO
     */
    public function ver($id = null)
    {
        if (empty($id)) {
            throw PageNotFoundException::forPageNotFound('Producto no especificado.');
        }

        $producto = $this->productoModel->getProductosConUsuario($id);

        if (!$producto) {
            throw PageNotFoundException::forPageNotFound('El producto solicitado no existe o fue eliminado.');
        }

        return view('productos/ver', [
            'pageTitle' => esc($producto['nombre']) . ' | MateriaX',
            'producto'  => $producto,
        ]);
    }

    /**
     * 5. FORMULARIO PARA EDITAR UN PRODUCTO
     */
    public function editar($id = null)
    {
        if (empty($id)) {
            throw PageNotFoundException::forPageNotFound('Producto no especificado.');
        }

        $producto = $this->productoModel->find($id);

        if (!$producto) {
            throw PageNotFoundException::forPageNotFound('El producto solicitado no existe.');
        }

        return view('productos/editar', [
            'pageTitle' => 'Editar: ' . esc($producto['nombre']) . ' | MateriaX',
            'producto'  => $producto,
        ]);
    }

    /**
     * 6. ACTUALIZAR PRODUCTO
     */
    public function actualizar($id = null)
    {
        if (empty($id)) {
            throw PageNotFoundException::forPageNotFound('Producto no especificado.');
        }

        $producto = $this->productoModel->find($id);
        if (!$producto) {
            return redirect()->to(site_url('productos'))->with('error', 'El producto no existe.');
        }

        $rules = [
            'nombre'          => 'required|min_length[3]|max_length[150]',
            'tipo_polimero'   => 'required|max_length[50]',
            'cantidad_kg'     => 'required|numeric|greater_than[0]',
            'precio_unitario' => 'required|numeric|greater_than_equal_to[0]',
            'ubicacion'       => 'required|min_length[2]|max_length[100]',
            'estado'          => 'required|in_list[Disponible,Reservado,Vendido]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nombre'          => trim($this->request->getPost('nombre')),
            'tipo_polimero'   => trim($this->request->getPost('tipo_polimero')),
            'cantidad_kg'     => (float) $this->request->getPost('cantidad_kg'),
            'precio_unitario' => (float) $this->request->getPost('precio_unitario'),
            'ubicacion'       => trim($this->request->getPost('ubicacion')),
            'descripcion'     => trim($this->request->getPost('descripcion') ?? ''),
            'estado'          => trim($this->request->getPost('estado')),
        ];

        $this->productoModel->update($id, $data);

        return redirect()->to(site_url('productos'))->with('success', '¡El producto fue actualizado con éxito!');
    }

    /**
     * 6.5 CONFIRMAR ELIMINACIÓN DE PRODUCTO (Flujo 100% PHP sin JavaScript)
     */
    public function confirmarEliminar($id = null)
    {
        if (empty($id)) {
            return redirect()->to(site_url('productos'))->with('error', 'ID de producto inválido.');
        }

        $producto = $this->productoModel->select('productos.*, usuarios.nombre as empresa_nombre')
                                        ->join('usuarios', 'usuarios.id = productos.user_id', 'left')
                                        ->find($id);

        if (!$producto) {
            return redirect()->to(site_url('productos'))->with('error', 'El lote de material no existe o ya fue eliminado.');
        }

        return view('productos/confirmar_eliminar', [
            'pageTitle' => 'Confirmar Eliminación de Lote #' . $id . ' | MateriaX',
            'producto'  => $producto,
        ]);
    }

    /**
     * 7. ELIMINAR PRODUCTO
     */
    public function eliminar($id = null)
    {
        if (empty($id)) {
            return redirect()->to(site_url('productos'))->with('error', 'ID de producto inválido.');
        }

        $producto = $this->productoModel->find($id);
        if (!$producto) {
            return redirect()->to(site_url('productos'))->with('error', 'El producto no existe o ya fue eliminado.');
        }

        $this->productoModel->delete($id);

        return redirect()->to(site_url('productos'))->with('success', 'El lote de material fue eliminado correctamente.');
    }
}
