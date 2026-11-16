<?php

namespace App\Controllers;

use App\Models\{PlantaModel, UserModel};
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Plantas extends BaseController
{
    protected PlantaModel $plantaModel;
    protected UserModel $userModel;

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

    public function index()
    {
        return view('plantas/index', [
            'pageTitle' => 'Sedes y Puntos de Retiro Logístico | MateriaX',
            'plantas'   => $this->plantaModel->getPlantasPorEmpresa($this->userId()),
        ]);
    }

    public function crear()
    {
        return view('plantas/crear', ['pageTitle' => 'Registrar Nueva Planta Industrial | MateriaX']);
    }

    public function guardar()
    {
        if (!$this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data               = $this->getFormData();
        $data['empresa_id'] = $this->userId();
        $data['activa']     = 1;

        if (!$this->plantaModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'No se pudo guardar la planta. Verifica los datos ingresados.');
        }

        return redirect()->to(site_url('plantas'))->with('success', '¡Planta industrial registrada correctamente!');
    }

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

    public function actualizar($id = null)
    {
        $planta = $this->validarPropiedadPlanta($id);
        if ($planta instanceof RedirectResponse) {
            return $planta;
        }
        if (!$this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if (!$this->plantaModel->update((int) $id, $this->getFormData())) {
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al actualizar los datos de la planta.');
        }

        return redirect()->to(site_url('plantas'))->with('success', 'Datos de la planta actualizados con éxito.');
    }

    public function cambiarEstado($id = null)
    {
        $planta = $this->validarPropiedadPlanta($id);
        if ($planta instanceof RedirectResponse) {
            return $planta;
        }

        $nuevoEstado = $planta['activa'] ? 0 : 1;
        $this->plantaModel->update((int) $id, ['activa' => $nuevoEstado]);

        return redirect()->to(site_url('plantas'))->with(
            'success',
            $nuevoEstado ? 'La planta fue reactivada.' : 'La planta fue desactivada para nuevas publicaciones.'
        );
    }

    protected function getFormData(): array
    {
        return [
            'nombre_planta'            => trim((string) $this->request->getPost('nombre_planta')),
            'direccion'                => trim((string) $this->request->getPost('direccion')),
            'localidad'                => trim((string) $this->request->getPost('localidad')),
            'provincia'                => trim((string) ($this->request->getPost('provincia') ?: 'Córdoba')),
            'coordenadas_gps'          => trim((string) $this->request->getPost('coordenadas_gps')),
            'posee_bascula'            => $this->request->getPost('posee_bascula') ? 1 : 0,
            'capacidad_carga_camiones' => trim((string) ($this->request->getPost('capacidad_carga_camiones') ?: 'Hasta 45 Ton')),
            'contacto_responsable'     => trim((string) $this->request->getPost('contacto_responsable')),
            'telefono_planta'          => trim((string) $this->request->getPost('telefono_planta')),
        ];
    }

    protected function validarPropiedadPlanta($id): array|RedirectResponse
    {
        if (empty($id) || !is_numeric($id)) {
            throw PageNotFoundException::forPageNotFound('Identificador de planta inválido.');
        }
        $planta = $this->plantaModel->find((int) $id);
        if (!$planta) {
            throw PageNotFoundException::forPageNotFound('La planta solicitada no existe.');
        }
        if (!$this->isAdmin() && (int) $planta['empresa_id'] !== $this->userId()) {
            return redirect()->to(site_url('plantas'))->with('error', 'Acceso denegado: esta planta pertenece a otra empresa.');
        }
        return $planta;
    }
}
