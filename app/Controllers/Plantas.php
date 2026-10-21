<?php

namespace App\Controllers;

use App\Models\PlantaModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Plantas extends BaseController
{
    protected PlantaModel $plantaModel;
    protected UserModel $userModel;
    protected $helpers = ['form', 'url'];

    protected array $rules = [
        'nombre_planta'            => 'required|min_length[3]|max_length[100]',
        'direccion'                => 'required|min_length[3]|max_length[150]',
        'localidad'                => 'required|min_length[2]|max_length[100]',
        'provincia'                => 'required|max_length[100]',
        'posee_bascula'            => 'permit_empty|in_list[0,1]',
        'capacidad_carga_camiones' => 'permit_empty|max_length[50]',
        'contacto_responsable'     => 'permit_empty|max_length[100]',
        'telefono_planta'          => 'permit_empty|max_length[30]',
        'coordenadas_gps'          => 'permit_empty|max_length[50]',
    ];

    public function __construct()
    {
        $this->plantaModel = new PlantaModel();
        $this->userModel   = new UserModel();
    }

    /**
     * Listado de plantas y puntos de acopio de la empresa logueada
     */
    public function index()
    {
        $userId = (int) session()->get('user_id');
        $plantas = $this->plantaModel->getPlantasPorEmpresa($userId);

        return view('plantas/index', [
            'pageTitle' => 'Sedes y Puntos de Retiro Logístico | MateriaX',
            'plantas'   => $plantas,
        ]);
    }

    /**
     * Formulario de alta para una nueva planta
     */
    public function crear()
    {
        return view('plantas/crear', [
            'pageTitle' => 'Registrar Nueva Planta Industrial | MateriaX',
        ]);
    }

    /**
     * Procesa y guarda la nueva planta
     */
    public function guardar()
    {
        if (!$this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = (int) session()->get('user_id');

        $data = [
            'empresa_id'               => $userId,
            'nombre_planta'            => trim($this->request->getPost('nombre_planta')),
            'direccion'                => trim($this->request->getPost('direccion')),
            'localidad'                => trim($this->request->getPost('localidad')),
            'provincia'                => trim($this->request->getPost('provincia') ?: 'Córdoba'),
            'coordenadas_gps'          => trim($this->request->getPost('coordenadas_gps')),
            'posee_bascula'            => $this->request->getPost('posee_bascula') ? 1 : 0,
            'capacidad_carga_camiones' => trim($this->request->getPost('capacidad_carga_camiones') ?: 'Hasta 45 Ton'),
            'contacto_responsable'     => trim($this->request->getPost('contacto_responsable')),
            'telefono_planta'          => trim($this->request->getPost('telefono_planta')),
            'activa'                   => 1,
        ];

        if (!$this->plantaModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'No se pudo guardar la planta. Verifica los datos ingresados.');
        }

        return redirect()->to(site_url('plantas'))->with('success', '¡Planta industrial registrada correctamente!');
    }

    /**
     * Formulario de edición de una planta existente
     */
    public function editar($id = null)
    {
        $planta = $this->validarPropiedadPlanta($id);
        if ($planta instanceof RedirectResponse) {
            return $planta;
        }

        return view('plantas/editar', [
            'pageTitle' => 'Editar Sede Fabril: ' . esc($planta['nombre_planta']) . ' | MateriaX',
            'planta'    => $planta,
        ]);
    }

    /**
     * Procesa la actualización de la planta
     */
    public function actualizar($id = null)
    {
        $planta = $this->validarPropiedadPlanta($id);
        if ($planta instanceof RedirectResponse) {
            return $planta;
        }

        if (!$this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nombre_planta'            => trim($this->request->getPost('nombre_planta')),
            'direccion'                => trim($this->request->getPost('direccion')),
            'localidad'                => trim($this->request->getPost('localidad')),
            'provincia'                => trim($this->request->getPost('provincia') ?: 'Córdoba'),
            'coordenadas_gps'          => trim($this->request->getPost('coordenadas_gps')),
            'posee_bascula'            => $this->request->getPost('posee_bascula') ? 1 : 0,
            'capacidad_carga_camiones' => trim($this->request->getPost('capacidad_carga_camiones') ?: 'Hasta 45 Ton'),
            'contacto_responsable'     => trim($this->request->getPost('contacto_responsable')),
            'telefono_planta'          => trim($this->request->getPost('telefono_planta')),
        ];

        if (!$this->plantaModel->update((int) $id, $data)) {
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al actualizar los datos de la planta.');
        }

        return redirect()->to(site_url('plantas'))->with('success', 'Datos de la planta actualizados con éxito.');
    }

    /**
     * Cambiar estado activo/inactivo (baja lógica)
     */
    public function cambiarEstado($id = null)
    {
        $planta = $this->validarPropiedadPlanta($id);
        if ($planta instanceof RedirectResponse) {
            return $planta;
        }

        $nuevoEstado = $planta['activa'] ? 0 : 1;
        $this->plantaModel->update((int) $id, ['activa' => $nuevoEstado]);

        $mensaje = $nuevoEstado ? 'La planta fue reactivada.' : 'La planta fue desactivada para nuevas publicaciones.';
        return redirect()->to(site_url('plantas'))->with('success', $mensaje);
    }

    /**
     * Método de seguridad: valida que la planta exista y pertenezca al usuario en sesión
     */
    protected function validarPropiedadPlanta($id): array|RedirectResponse
    {
        if (empty($id) || !is_numeric($id)) {
            throw PageNotFoundException::forPageNotFound('Identificador de planta inválido.');
        }

        $userId = (int) session()->get('user_id');
        $planta = $this->plantaModel->find((int) $id);

        if (!$planta) {
            throw PageNotFoundException::forPageNotFound('La planta solicitada no existe.');
        }

        // Si es admin, tiene permiso de lectura/auditoría
        if (session()->get('rol') === 'admin') {
            return $planta;
        }

        // Si es empresa, DEBE ser el dueño
        if ((int) $planta['empresa_id'] !== $userId) {
            return redirect()->to(site_url('plantas'))->with('error', 'Acceso denegado: esta planta pertenece a otra empresa.');
        }

        return $planta;
    }
}
