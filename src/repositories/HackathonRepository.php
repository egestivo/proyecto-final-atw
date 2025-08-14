<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\RepositoryInterface;
use App\Config\Database;
use App\Entities\Hackathon;
use PDO;
use PDOException;

class HackathonRepository implements RepositoryInterface
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
            $query = "SELECT * FROM hackathons ORDER BY created_at DESC";
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $hackathons = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hackathons[] = $this->createHackathonFromRow($row);
            }
            
            return $hackathons;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener hackathons: " . $e->getMessage());
        }
    }

    public function findById(int $id): ?object
    {
        try {
            $query = "SELECT * FROM hackathons WHERE id = $id";
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $row ? $this->createHackathonFromRow($row) : null;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener hackathon: " . $e->getMessage());
        }
    }

    public function save(object $entity): bool
    {
        if ($entity->getId() === null) {
            return $this->create($entity);
        } else {
            return $this->update($entity);
        }
    }

    public function create(object $entity): bool
    {
        try {
            // Escapar valores
            $nombre = $this->connection->quote($entity->getNombre());
            $descripcion = $this->connection->quote($entity->getDescripcion());
            $fechaInicio = $this->connection->quote($entity->getFechaInicio());
            $fechaFin = $this->connection->quote($entity->getFechaFin());
            $lugar = $this->connection->quote($entity->getLugar());
            $estado = $this->connection->quote($entity->getEstado());
            
            $query = "
                INSERT INTO hackathons (nombre, descripcion, fecha_inicio, fecha_fin, lugar, estado) 
                VALUES ($nombre, $descripcion, $fechaInicio, $fechaFin, $lugar, $estado)
            ";
            
            $stmt = $this->connection->prepare($query);
            $result = $stmt->execute();
            
            if ($result) {
                $entity->setId((int) $this->connection->lastInsertId());
            }
            
            return $result;
        } catch (PDOException $e) {
            throw new \Exception("Error al crear hackathon: " . $e->getMessage());
        }
    }

    public function update(object $entity): bool
    {
        try {
            // Escapar valores
            $id = $entity->getId();
            $nombre = $this->connection->quote($entity->getNombre());
            $descripcion = $this->connection->quote($entity->getDescripcion());
            $fechaInicio = $this->connection->quote($entity->getFechaInicio());
            $fechaFin = $this->connection->quote($entity->getFechaFin());
            $lugar = $this->connection->quote($entity->getLugar());
            $estado = $this->connection->quote($entity->getEstado());
            
            $query = "
                UPDATE hackathons 
                SET nombre = $nombre, descripcion = $descripcion, fecha_inicio = $fechaInicio, 
                    fecha_fin = $fechaFin, lugar = $lugar, estado = $estado, 
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = $id
            ";
            
            $stmt = $this->connection->prepare($query);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error al actualizar hackathon: " . $e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            $query = "DELETE FROM hackathons WHERE id = $id";
            $stmt = $this->connection->prepare($query);
            $result = $stmt->execute();
            
            return $result && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new \Exception("Error al eliminar hackathon: " . $e->getMessage());
        }
    }

    private function createHackathonFromRow(array $row): Hackathon
    {
        $fechaInicio = new \DateTime($row['fecha_inicio']);
        $fechaFin = new \DateTime($row['fecha_fin']);
        
        $hackathon = new Hackathon(
            $row['nombre'],
            $fechaInicio,
            $fechaFin,
            $row['descripcion'],
            $row['lugar'],
            $row['estado'] ?? 'planificacion'
        );
        
        $hackathon->setId((int) $row['id']);
        
        return $hackathon;
    }

    // Métodos específicos
    public function findByEstado(string $estado): array
    {
        try {
            $estadoEscapado = $this->connection->quote($estado);
            $query = "SELECT * FROM hackathons WHERE estado = $estadoEscapado ORDER BY fecha_inicio DESC";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $hackathons = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hackathons[] = $this->createHackathonFromRow($row);
            }
            
            return $hackathons;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener hackathons por estado: " . $e->getMessage());
        }
    }

    public function findActivos(): array
    {
        return $this->findByEstado('activo');
    }

    public function findProximos(): array
    {
        try {
            $query = "
                SELECT * FROM hackathons 
                WHERE fecha_inicio > NOW() AND estado = 'planificado'
                ORDER BY fecha_inicio ASC
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $hackathons = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hackathons[] = $this->createHackathonFromRow($row);
            }
            
            return $hackathons;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener próximos hackathons: " . $e->getMessage());
        }
    }
}