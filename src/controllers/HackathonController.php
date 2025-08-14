<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\HackathonRepository;
use App\Entities\Hackathon;

class HackathonController {
    private HackathonRepository $hackathonRepository;

    public function __construct() {
        $this->hackathonRepository = new HackathonRepository();
    }

    public function handle(): void {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                $hackathon = $this->hackathonRepository->findById((int) $_GET['id']);
                echo json_encode($hackathon ? $this->hackathonToArray($hackathon) : null);
                return;
            } else {
                $list = array_map(
                    [$this,'hackathonToArray'], 
                    $this->hackathonRepository->findAll()
                );
                echo json_encode($list);
                return;
            }
        }

        $payload = json_decode(file_get_contents('php://input'), true);

        if ($method === 'POST') {
            $fechaInicio = new \DateTime($payload['fechaInicio']);
            $fechaFin = new \DateTime($payload['fechaFin']);
            
            $hackathon = new Hackathon(
                $payload['nombre'],
                $fechaInicio,
                $fechaFin,
                $payload['descripcion'] ?? null,
                $payload['lugar'] ?? null
            );

            echo json_encode([
                'success' => $this->hackathonRepository->create($hackathon),
            ]);
            return;
        }

        if ($method === 'PUT') {
            $id = (int)($payload['id'] ?? 0);
            $existing = $this->hackathonRepository->findById($id);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['error' => 'Hackathon not found']);
                return;
            }

            if(isset($payload['nombre'])) $existing->setNombre($payload['nombre']);
            if(isset($payload['descripcion'])) $existing->setDescripcion($payload['descripcion']);
            if(isset($payload['fechaInicio'])) $existing->setFechaInicio(new \DateTime($payload['fechaInicio']));
            if(isset($payload['fechaFin'])) $existing->setFechaFin(new \DateTime($payload['fechaFin']));
            if(isset($payload['lugar'])) $existing->setLugar($payload['lugar']);
            if(isset($payload['estado'])) $existing->setEstado($payload['estado']);

            echo json_encode([
                'success' => $this->hackathonRepository->update($existing),
            ]);
            return;
        }

        if ($method === 'DELETE') {
            echo json_encode(['success' => $this->hackathonRepository->delete((int)$payload['id'] ?? 0)]);
            return;
        }
    }

    public function hackathonToArray(Hackathon $hackathon): array {
        return [
            'id' => $hackathon->getId(),
            'nombre' => $hackathon->getNombre(),
            'descripcion' => $hackathon->getDescripcion(),
            'fechaInicio' => $hackathon->getFechaInicio()->format('Y-m-d'),
            'fechaFin' => $hackathon->getFechaFin()->format('Y-m-d'),
            'lugar' => $hackathon->getLugar(),
            'estado' => $hackathon->getEstado()
        ];
    }
}