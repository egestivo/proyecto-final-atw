<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ParticipanteRepository;
use App\Entities\Estudiante;
use App\Entities\MentorTecnico;

class ParticipanteController
{
    private ParticipanteRepository $repository;

    public function __construct()
    {
        $this->repository = new ParticipanteRepository();
    }

    public function index(): void
    {
        try {
            $participantes = $this->repository->findAll();
            $this->jsonResponse($participantes);
        } catch (\Exception $e) {
            $this->errorResponse('Error al obtener participantes: ' . $e->getMessage(), 500);
        }
    }

    public function show(int $id): void
    {
        try {
            $participante = $this->repository->findById($id);
            
            if (!$participante) {
                $this->errorResponse('Participante no encontrado', 404);
                return;
            }
            
            $this->jsonResponse($participante);
        } catch (\Exception $e) {
            $this->errorResponse('Error al obtener participante: ' . $e->getMessage(), 500);
        }
    }

    public function store(): void
    {
        try {
            $data = $this->getJsonInput();
            
            if (!$this->validateParticipanteData($data)) {
                $this->errorResponse('Datos inválidos', 400);
                return;
            }
            
            // Crear participante según tipo
            if ($data['tipo'] === 'estudiante') {
                $participante = new Estudiante(
                    $data['nombre'],
                    $data['email'],
                    $data['telefono'],
                    $data['grado'] ?? '',
                    $data['institucion'] ?? '',
                    $data['tiempo_disponible_semanal'] ?? '0'
                );
            } else {
                $participante = new MentorTecnico(
                    $data['nombre'],
                    $data['email'],
                    $data['telefono'],
                    $data['especialidad'] ?? '',
                    $data['experiencia_anos'] ?? '0',
                    $data['disponibilidad_horaria'] ?? ''
                );
            }
            
            if ($this->repository->create($participante)) {
                $this->jsonResponse($participante, 201);
            } else {
                $this->errorResponse('Error al crear participante', 500);
            }
        } catch (\Exception $e) {
            $this->errorResponse('Error al crear participante: ' . $e->getMessage(), 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $participante = $this->repository->findById($id);
            
            if (!$participante) {
                $this->errorResponse('Participante no encontrado', 404);
                return;
            }
            
            $data = $this->getJsonInput();
            
            // Actualizar propiedades básicas
            if (isset($data['nombre'])) $participante->setNombre($data['nombre']);
            if (isset($data['email'])) $participante->setEmail($data['email']);
            if (isset($data['telefono'])) $participante->setTelefono($data['telefono']);
            
            // Actualizar propiedades específicas
            if ($participante instanceof Estudiante) {
                if (isset($data['grado'])) $participante->setGrado($data['grado']);
                if (isset($data['institucion'])) $participante->setInstitucion($data['institucion']);
                if (isset($data['tiempo_disponible_semanal'])) $participante->setTiempoDisponibleSemanal($data['tiempo_disponible_semanal']);
            } elseif ($participante instanceof MentorTecnico) {
                if (isset($data['especialidad'])) $participante->setEspecialidad($data['especialidad']);
                if (isset($data['experiencia_anos'])) $participante->setExperienciaAnos($data['experiencia_anos']);
                if (isset($data['disponibilidad_horaria'])) $participante->setDisponibilidadHoraria($data['disponibilidad_horaria']);
            }
            
            if ($this->repository->update($participante)) {
                $this->jsonResponse($participante);
            } else {
                $this->errorResponse('Error al actualizar participante', 500);
            }
        } catch (\Exception $e) {
            $this->errorResponse('Error al actualizar participante: ' . $e->getMessage(), 500);
        }
    }

    public function delete(int $id): void
    {
        try {
            if ($this->repository->delete($id)) {
                $this->jsonResponse(['message' => 'Participante eliminado correctamente']);
            } else {
                $this->errorResponse('Participante no encontrado', 404);
            }
        } catch (\Exception $e) {
            $this->errorResponse('Error al eliminar participante: ' . $e->getMessage(), 500);
        }
    }

    public function byTipo(string $tipo): void
    {
        try {
            if (!in_array($tipo, ['estudiante', 'mentor_tecnico'])) {
                $this->errorResponse('Tipo inválido', 400);
                return;
            }
            
            $participantes = $this->repository->findByTipo($tipo);
            $this->jsonResponse($participantes);
        } catch (\Exception $e) {
            $this->errorResponse('Error al obtener participantes por tipo: ' . $e->getMessage(), 500);
        }
    }

    public function byEmail(string $email): void
    {
        try {
            $participante = $this->repository->findByEmail($email);
            
            if (!$participante) {
                $this->errorResponse('Participante no encontrado', 404);
                return;
            }
            
            $this->jsonResponse($participante);
        } catch (\Exception $e) {
            $this->errorResponse('Error al buscar participante: ' . $e->getMessage(), 500);
        }
    }

    private function validateParticipanteData(array $data): bool
    {
        return isset($data['nombre']) && 
               isset($data['email']) && 
               isset($data['telefono']) &&
               isset($data['tipo']) &&
               in_array($data['tipo'], ['estudiante', 'mentor_tecnico']);
    }

    private function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    private function jsonResponse($data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        
        if (is_object($data)) {
            echo json_encode($this->objectToArray($data));
        } elseif (is_array($data) && !empty($data) && is_object($data[0])) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->objectToArray($item);
            }
            echo json_encode($result);
        } else {
            echo json_encode($data);
        }
    }

    private function errorResponse(string $message, int $statusCode = 400): void
    {
        $this->jsonResponse(['error' => $message], $statusCode);
    }

    private function objectToArray($obj): array
    {
        if (is_object($obj)) {
            $result = [
                'id' => $obj->getId(),
                'nombre' => $obj->getNombre(),
                'email' => $obj->getEmail(),
                'telefono' => $obj->getTelefono()
            ];
            
            // Propiedades específicas
            if ($obj instanceof Estudiante) {
                $result['tipo'] = 'estudiante';
                $result['grado'] = $obj->getGrado();
                $result['institucion'] = $obj->getInstitucion();
                $result['tiempo_disponible_semanal'] = $obj->getTiempoDisponibleSemanal();
            } elseif ($obj instanceof MentorTecnico) {
                $result['tipo'] = 'mentor_tecnico';
                $result['especialidad'] = $obj->getEspecialidad();
                $result['experiencia_anos'] = $obj->getExperienciaAnos();
                $result['disponibilidad_horaria'] = $obj->getDisponibilidadHoraria();
            }
            
            return $result;
        }
        
        return (array) $obj;
    }
}
