<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\RetoRepository;
use App\Entities\RetoReal;
use App\Entities\RetoExperimental;

class RetoController {
    private RetoRepository $retoRepository;

    public function __construct() {
        $this->retoRepository = new RetoRepository();
    }

    public function handle(): void {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                $reto = $this->retoRepository->findById((int) $_GET['id']);
                echo json_encode($reto ? $this->retoToArray($reto) : null);
                return;
            } else {
                $list = array_map(
                    [$this,'retoToArray'], 
                    $this->retoRepository->findAll()
                );
                echo json_encode($list);
                return;
            }
        }

        $payload = json_decode(file_get_contents('php://input'), true);

        if ($method === 'POST') {
            $tipo = $payload['tipo'] ?? 'real';
            
            if ($tipo === 'experimental') {
                $reto = new RetoExperimental(
                    $payload['titulo'],
                    $payload['descripcion'],
                    $payload['nivelDificultad'] ?? 'medio',
                    $payload['tecnologiasRequeridas'] ?? '',
                    (string)($payload['hackathonId'] ?? '1'),
                    $payload['enfoquePedagogico'] ?? 'STEM'
                );
            } else {
                $reto = new RetoReal(
                    $payload['titulo'],
                    $payload['descripcion'],
                    $payload['nivelDificultad'] ?? 'medio',
                    $payload['tecnologiasRequeridas'] ?? '',
                    (string)($payload['hackathonId'] ?? '1'),
                    $payload['entidadColaboradora'] ?? ''
                );
            }

            echo json_encode([
                'success' => $this->retoRepository->create($reto),
            ]);
            return;
        }

        if ($method === 'PUT') {
            $id = (int)($payload['id'] ?? 0);
            $existing = $this->retoRepository->findById($id);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['error' => 'Reto not found']);
                return;
            }

            if(isset($payload['titulo'])) $existing->setTitulo($payload['titulo']);
            if(isset($payload['descripcion'])) $existing->setDescripcion($payload['descripcion']);
            if(isset($payload['nivelDificultad'])) $existing->setNivelDificultad($payload['nivelDificultad']);

            echo json_encode([
                'success' => $this->retoRepository->update($existing),
            ]);
            return;
        }

        if ($method === 'DELETE') {
            echo json_encode(['success' => $this->retoRepository->delete((int)$payload['id'] ?? 0)]);
            return;
        }
    }

    public function retoToArray($reto): array {
        return [
            'id' => $reto->getId(),
            'titulo' => $reto->getTitulo(),
            'descripcion' => $reto->getDescripcion(),
            'nivelDificultad' => $reto->getNivelDificultad(),
            'hackathonId' => $reto->getHackathonId(),
            'tipo' => $reto->getTipo()
        ];
    }
}