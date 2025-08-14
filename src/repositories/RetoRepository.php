<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\RepositoryInterface;
use App\Config\Database;
use App\Entities\RetoSolucionable;
use App\Entities\RetoReal;
use App\Entities\RetoExperimental;
use PDO;
use PDOException;

class RetoRepository implements RepositoryInterface
{
    private PDO $connection;

    public function __construct()
    {
        $database = new Database();
        $this->connection = $database->getConnection();
    }

    public function findAll(): array
    {
        try {
            $query = "
                SELECT rs.*, 
                       rr.entidad_colaboradora,
                       re.enfoque_pedagogico
                FROM retos_solucionables rs
                LEFT JOIN retos_reales rr ON rs.id = rr.reto_id
                LEFT JOIN retos_experimentales re ON rs.id = re.reto_id
                ORDER BY rs.created_at DESC
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $retos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $retos[] = $this->createRetoFromRow($row);
            }
            
            return $retos;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener retos: " . $e->getMessage());
        }
    }

    public function findById(int $id): ?object
    {
        try {
            $query = "
                SELECT rs.*, 
                       rr.entidad_colaboradora,
                       re.enfoque_pedagogico
                FROM retos_solucionables rs
                LEFT JOIN retos_reales rr ON rs.id = rr.reto_id
                LEFT JOIN retos_experimentales re ON rs.id = re.reto_id
                WHERE rs.id = $id
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $row ? $this->createRetoFromRow($row) : null;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener reto: " . $e->getMessage());
        }
    }

    public function save(object $entity): bool
    {
        if ($entity->getId() === null) {
            return $this->createReto($entity);
        } else {
            return $this->updateReto($entity);
        }
    }

    public function create(object $entity): bool
    {
        return $this->createReto($entity);
    }

    public function update(object $entity): bool
    {
        return $this->updateReto($entity);
    }

    public function delete(int $id): bool
    {
        try {
            $this->connection->beginTransaction();
            
            // Eliminar de tabla específica primero
            $reto = $this->findById($id);
            if (!$reto) {
                return false;
            }
            
            if ($reto instanceof RetoReal) {
                $stmt = $this->connection->prepare("DELETE FROM retos_reales WHERE reto_id = $id");
                $stmt->execute();
            } elseif ($reto instanceof RetoExperimental) {
                $stmt = $this->connection->prepare("DELETE FROM retos_experimentales WHERE reto_id = $id");
                $stmt->execute();
            }
            
            // Eliminar de tabla base
            $stmt = $this->connection->prepare("DELETE FROM retos_solucionables WHERE id = $id");
            $result = $stmt->execute();
            
            $this->connection->commit();
            return $result && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw new \Exception("Error al eliminar reto: " . $e->getMessage());
        }
    }

    private function createReto(RetoSolucionable $reto): bool
    {
        try {
            $this->connection->beginTransaction();
            
            // Escapar valores para SQL
            $titulo = $this->connection->quote($reto->getTitulo());
            $descripcion = $this->connection->quote($reto->getDescripcion());
            $dificultad = $this->connection->quote($reto->getDificultad());
            $tecnologias = $this->connection->quote($reto->getTecnologiasRequeridas());
            $hackathonId = $reto->getHackathonId();
            $estado = $this->connection->quote($reto->getEstado());
            $tipo = $reto instanceof RetoReal ? 'reto_real' : 'reto_experimental';
            
            // Insertar en tabla base
            $query = "
                INSERT INTO retos_solucionables (titulo, descripcion, dificultad, tecnologias_requeridas, tipo, estado, hackathon_id) 
                VALUES ($titulo, $descripcion, $dificultad, $tecnologias, '$tipo', $estado, $hackathonId)
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            $retoId = (int) $this->connection->lastInsertId();
            $reto->setId($retoId);
            
            // Insertar en tabla específica
            if ($reto instanceof RetoReal) {
                $this->createRetoReal($reto, $retoId);
            } elseif ($reto instanceof RetoExperimental) {
                $this->createRetoExperimental($reto, $retoId);
            }
            
            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw new \Exception("Error al crear reto: " . $e->getMessage());
        }
    }

    private function updateReto(RetoSolucionable $reto): bool
    {
        try {
            $this->connection->beginTransaction();
            
            // Escapar valores para SQL
            $id = $reto->getId();
            $titulo = $this->connection->quote($reto->getTitulo());
            $descripcion = $this->connection->quote($reto->getDescripcion());
            $dificultad = $this->connection->quote($reto->getDificultad());
            $tecnologias = $this->connection->quote($reto->getTecnologiasRequeridas());
            $estado = $this->connection->quote($reto->getEstado());
            $hackathonId = $reto->getHackathonId();
            
            // Actualizar tabla base
            $query = "
                UPDATE retos_solucionables 
                SET titulo = $titulo, descripcion = $descripcion, dificultad = $dificultad, 
                    tecnologias_requeridas = $tecnologias, estado = $estado, 
                    hackathon_id = $hackathonId, updated_at = CURRENT_TIMESTAMP
                WHERE id = $id
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            // Actualizar tabla específica
            if ($reto instanceof RetoReal) {
                $this->updateRetoReal($reto);
            } elseif ($reto instanceof RetoExperimental) {
                $this->updateRetoExperimental($reto);
            }
            
            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw new \Exception("Error al actualizar reto: " . $e->getMessage());
        }
    }

    private function createRetoReal(RetoReal $reto, int $retoId): void
    {
        $entidadColaboradora = $this->connection->quote($reto->getEntidadColaboradora());
        $query = "INSERT INTO retos_reales (reto_id, entidad_colaboradora) VALUES ($retoId, $entidadColaboradora)";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
    }

    private function createRetoExperimental(RetoExperimental $reto, int $retoId): void
    {
        $enfoquePedagogico = $this->connection->quote($reto->getEnfoquePedagogico());
        $query = "INSERT INTO retos_experimentales (reto_id, enfoque_pedagogico) VALUES ($retoId, $enfoquePedagogico)";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
    }

    private function updateRetoReal(RetoReal $reto): void
    {
        $retoId = $reto->getId();
        $entidadColaboradora = $this->connection->quote($reto->getEntidadColaboradora());
        $query = "UPDATE retos_reales SET entidad_colaboradora = $entidadColaboradora WHERE reto_id = $retoId";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
    }

    private function updateRetoExperimental(RetoExperimental $reto): void
    {
        $retoId = $reto->getId();
        $enfoquePedagogico = $this->connection->quote($reto->getEnfoquePedagogico());
        $query = "UPDATE retos_experimentales SET enfoque_pedagogico = $enfoquePedagogico WHERE reto_id = $retoId";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
    }

    private function createRetoFromRow(array $row): RetoSolucionable
    {
        if ($row['tipo'] === 'reto_real') {
            $reto = new RetoReal(
                $row['titulo'],
                $row['descripcion'],
                $row['dificultad'],
                $row['tecnologias_requeridas'] ?? '',
                (string)($row['hackathon_id'] ?? '1'),
                $row['entidad_colaboradora'] ?? ''
            );
            $reto->setId((int) $row['id']);
            $reto->setEstado($row['estado']);
            
            return $reto;
        } else {
            $reto = new RetoExperimental(
                $row['titulo'],
                $row['descripcion'],
                $row['dificultad'],
                $row['tecnologias_requeridas'] ?? '',
                (string)($row['hackathon_id'] ?? '1'),
                $row['enfoque_pedagogico'] ?? 'STEM'
            );
            $reto->setId((int) $row['id']);
            $reto->setEstado($row['estado']);
            
            return $reto;
        }
    }

    public function findByTipo(string $tipo): array
    {
        try {
            $tipoEscapado = $this->connection->quote($tipo);
            $query = "
                SELECT rs.*, 
                       rr.entidad_colaboradora,
                       re.enfoque_pedagogico
                FROM retos_solucionables rs
                LEFT JOIN retos_reales rr ON rs.id = rr.reto_id
                LEFT JOIN retos_experimentales re ON rs.id = re.reto_id
                WHERE rs.tipo = $tipoEscapado
                ORDER BY rs.created_at DESC
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $retos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $retos[] = $this->createRetoFromRow($row);
            }
            
            return $retos;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener retos por tipo: " . $e->getMessage());
        }
    }

    public function findByHackathon(int $hackathonId): array
    {
        try {
            $query = "
                SELECT rs.*, 
                       rr.entidad_colaboradora,
                       re.enfoque_pedagogico
                FROM retos_solucionables rs
                LEFT JOIN retos_reales rr ON rs.id = rr.reto_id
                LEFT JOIN retos_experimentales re ON rs.id = re.reto_id
                WHERE rs.hackathon_id = $hackathonId
                ORDER BY rs.created_at DESC
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $retos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $retos[] = $this->createRetoFromRow($row);
            }
            
            return $retos;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener retos por hackathon: " . $e->getMessage());
        }
    }
}