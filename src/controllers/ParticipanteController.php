<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ParticipanteRepository;
use App\Entities\Estudiante;
use App\Entities\MentorTecnico;

class ParticipanteController {
    private ParticipanteRepository $participanteRepository;

    public function __construct() {
        $this->participanteRepository = new ParticipanteRepository();
    }

    public function handle(): void {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                $participante = $this->participanteRepository->findById((int) $_GET['id']);
                echo json_encode($participante ? $this->participanteToArray($participante) : null);
                return;
            } else {
                $list = array_map(
                    [$this,'participanteToArray'], 
                    $this->participanteRepository->findAll()
                );
                echo json_encode($list);
                return;
            }
        }

        $payload = json_decode(file_get_contents('php://input'), true);

        if ($method === 'POST') {
            if ($payload['tipo'] === 'estudiante') {
                $participante = new Estudiante(
                    $payload['nombre'],
                    $payload['email'],
                    $payload['telefono'] ?? '',
                    $payload['grado'] ?? '',
                    $payload['institucion'] ?? '',
                    (string)($payload['tiempoDisponibleSemanal'] ?? '0')
                );
            } else {
                $participante = new MentorTecnico(
                    $payload['nombre'],
                    $payload['email'],
                    $payload['telefono'] ?? '',
                    $payload['especialidad'] ?? '',
                    (string)($payload['experienciaAnos'] ?? '0'),
                    $payload['disponibilidadHoraria'] ?? ''
                );
            }

            echo json_encode([
                'success' => $this->participanteRepository->create($participante),
            ]);
            return;
        }

        if ($method === 'PUT') {
            $id = (int)($payload['id'] ?? 0);
            $existing = $this->participanteRepository->findById($id);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['error' => 'Participante not found']);
                return;
            }

            if(isset($payload['nombre'])) $existing->setNombre($payload['nombre']);
            if(isset($payload['email'])) $existing->setEmail($payload['email']);
            if(isset($payload['telefono'])) $existing->setTelefono($payload['telefono']);

            echo json_encode([
                'success' => $this->participanteRepository->update($existing),
            ]);
            return;
        }

        if ($method === 'DELETE') {
            echo json_encode(['success' => $this->participanteRepository->delete((int)$payload['id'] ?? 0)]);
            return;
        }
    }

    public function participanteToArray($participante): array {
        $result = [
            'id' => $participante->getId(),
            'nombre' => $participante->getNombre(),
            'email' => $participante->getEmail(),
            'telefono' => $participante->getTelefono(),
            'tipo' => $participante->getTipo()
        ];

        // Agregar campos específicos según el tipo
        if ($participante instanceof Estudiante) {
            $result['grado'] = $participante->getGrado();
            $result['institucion'] = $participante->getInstitucion();
            $result['tiempoDisponibleSemanal'] = $participante->getTiempoDisponibleSemanal();
        } elseif ($participante instanceof MentorTecnico) {
            $result['especialidad'] = $participante->getEspecialidad();
            $result['experienciaAnos'] = $participante->getExperienciaAnos();
            $result['disponibilidadHoraria'] = $participante->getDisponibilidadHoraria();
        }

        return $result;
    }
}
