<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\EquipoRepository;
use App\Entities\Equipo;

class EquipoController {
    private EquipoRepository $equipoRepository;

    public function __construct() {
        $this->equipoRepository = new EquipoRepository();
    }

    public function handle(): void {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                $equipo = $this->equipoRepository->findById((int) $_GET['id']);
                echo json_encode($equipo ? $this->equipoToArray($equipo) : null);
                return;
            } else {
                $list = array_map(
                    [$this,'equipoToArray'], 
                    $this->equipoRepository->findAll()
                );
                echo json_encode($list);
                return;
            }
        }

        $payload = json_decode(file_get_contents('php://input'), true);

        if ($method === 'POST') {
            // Convertir hackathonId string a int (o usar un mapeo)
            $hackathonIdInt = 1; // Por defecto
            if (isset($payload['hackathonId'])) {
                if (is_numeric($payload['hackathonId'])) {
                    $hackathonIdInt = (int)$payload['hackathonId'];
                } else {
                    // Mapeo de strings a IDs
                    $hackathonMap = [
                        'eduhack2025' => 1,
                        'eduhack2024' => 2,
                    ];
                    $hackathonIdInt = $hackathonMap[$payload['hackathonId']] ?? 1;
                }
            }

            // Crear equipo simple según el documento: ID, nombre, hackathon, lista de participantes
            $equipo = new Equipo(
                $payload['nombre'],
                $hackathonIdInt
            );

            $success = $this->equipoRepository->create($equipo);

            echo json_encode([
                'success' => $success,
                'equipo_id' => $equipo->getId(),
                'participantes_a_agregar' => count($payload['participanteIds'] ?? [])
            ]);
            return;
        }

        if ($method === 'PUT') {
            $id = (int)($payload['id'] ?? 0);
            $existing = $this->equipoRepository->findById($id);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['error' => 'Equipo not found']);
                return;
            }

            if(isset($payload['nombre'])) $existing->setNombre($payload['nombre']);
            if(isset($payload['descripcion'])) $existing->setDescripcion($payload['descripcion']);

            echo json_encode([
                'success' => $this->equipoRepository->update($existing),
            ]);
            return;
        }

        if ($method === 'DELETE') {
            echo json_encode(['success' => $this->equipoRepository->delete((int)$payload['id'] ?? 0)]);
            return;
        }
    }

    public function equipoToArray(Equipo $equipo): array {
        return [
            'id' => $equipo->getId(),
            'nombre' => $equipo->getNombre(),
            'descripcion' => $equipo->getDescripcion(),
            'hackathonId' => $equipo->getHackathonId(),
            'estado' => $equipo->getEstado(),
            'maxIntegrantes' => $equipo->getMaxIntegrantes(),
            'fechaFormacion' => $equipo->getFechaFormacion()->format('Y-m-d H:i:s')
        ];
    }
}