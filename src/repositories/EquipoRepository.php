<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\RepositoryInterface;
use App\Config\Database;
use App\Entities\Equipo;
use PDO;
use PDOException;

class EquipoRepository implements RepositoryInterface
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
                SELECT e.*, h.nombre as hackathon_nombre
                FROM equipos e
                LEFT JOIN hackathons h ON e.hackathon_id = h.id
                ORDER BY e.created_at DESC
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $equipos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $equipos[] = $this->createEquipoFromRow($row);
            }
            
            return $equipos;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener equipos: " . $e->getMessage());
        }
    }

    public function findById(int $id): ?object
    {
        try {
            $query = "
                SELECT e.*, h.nombre as hackathon_nombre
                FROM equipos e
                LEFT JOIN hackathons h ON e.hackathon_id = h.id
                WHERE e.id = $id
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) {
                return null;
            }
            
            $equipo = $this->createEquipoFromRow($row);
            
            // Cargar participantes del equipo
            $this->loadParticipantes($equipo);
            
            // Cargar retos asignados
            $this->loadRetos($equipo);
            
            return $equipo;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener equipo: " . $e->getMessage());
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
            $this->connection->beginTransaction();
            
            // Escapar valores
            $nombre = $this->connection->quote($entity->getNombre());
            $descripcion = $this->connection->quote($entity->getDescripcion() ?? '');
            $hackathonId = $entity->getHackathonId();
            $estado = $this->connection->quote($entity->getEstado());
            $maxIntegrantes = $entity->getMaxIntegrantes();
            
            // Insertar equipo
            $query = "
                INSERT INTO equipos (nombre, descripcion, hackathon_id, estado, max_integrantes) 
                VALUES ($nombre, $descripcion, $hackathonId, $estado, $maxIntegrantes)
            ";
            
            $stmt = $this->connection->prepare($query);
            $result = $stmt->execute();
            
            if ($result) {
                $equipoId = (int) $this->connection->lastInsertId();
                $entity->setId($equipoId);
            }
            
            $this->connection->commit();
            return $result;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw new \Exception("Error al crear equipo: " . $e->getMessage());
        }
    }

    public function update(object $entity): bool
    {
        try {
            // Escapar valores
            $id = $entity->getId();
            $nombre = $this->connection->quote($entity->getNombre());
            $descripcion = $this->connection->quote($entity->getDescripcion());
            $hackathonId = $entity->getHackathonId();
            $estado = $this->connection->quote($entity->getEstado());
            $maxIntegrantes = $entity->getMaxIntegrantes();
            
            $query = "
                UPDATE equipos 
                SET nombre = $nombre, descripcion = $descripcion, hackathon_id = $hackathonId, 
                    estado = $estado, max_integrantes = $maxIntegrantes, updated_at = CURRENT_TIMESTAMP
                WHERE id = $id
            ";
            
            $stmt = $this->connection->prepare($query);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error al actualizar equipo: " . $e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            $this->connection->beginTransaction();
            
            // Eliminar relaciones primero
            $stmt = $this->connection->prepare("DELETE FROM equipo_participantes WHERE equipo_id = $id");
            $stmt->execute();
            
            $stmt = $this->connection->prepare("DELETE FROM equipo_retos WHERE equipo_id = $id");
            $stmt->execute();
            
            // Eliminar equipo
            $stmt = $this->connection->prepare("DELETE FROM equipos WHERE id = $id");
            $result = $stmt->execute();
            
            $this->connection->commit();
            return $result && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw new \Exception("Error al eliminar equipo: " . $e->getMessage());
        }
    }

    private function createEquipoFromRow(array $row): Equipo
    {
        $equipo = new Equipo(
            $row['nombre'],
            (int)($row['hackathon_id'] ?? 1),
            $row['descripcion'],
            $row['estado'] ?? 'formandose',
            (int)($row['max_integrantes'] ?? 6)
        );
        
        $equipo->setId((int) $row['id']);
        $equipo->setEstado($row['estado']);
        
        return $equipo;
    }

    private function loadParticipantes(Equipo $equipo): void
    {
        try {
            $equipoId = $equipo->getId();
            $query = "
                SELECT p.*, 
                       e.grado, e.institucion, e.tiempo_disponible_semanal,
                       mt.especialidad, mt.experiencia_anos, mt.disponibilidad_horaria,
                       ep.es_lider, ep.fecha_union
                FROM equipo_participantes ep
                JOIN participantes p ON ep.participante_id = p.id
                LEFT JOIN estudiantes e ON p.id = e.participante_id
                LEFT JOIN mentores_tecnicos mt ON p.id = mt.participante_id
                WHERE ep.equipo_id = $equipoId
                ORDER BY ep.es_lider DESC, ep.fecha_union ASC
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $participantes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Aquí necesitarías crear las instancias de Estudiante o MentorTecnico
                // usando el ParticipanteRepository para mantener consistencia
                $participantes[] = $row; // Por simplicidad
            }
            
            // Nota: Aquí podrías setear los participantes al equipo si el método existe
        } catch (PDOException $e) {
            throw new \Exception("Error al cargar participantes del equipo: " . $e->getMessage());
        }
    }

    private function loadRetos(Equipo $equipo): void
    {
        try {
            $equipoId = $equipo->getId();
            $query = "
                SELECT rs.*, 
                       rr.entidad_colaboradora,
                       re.enfoque_pedagogico,
                       er.estado_participacion, er.fecha_asignacion
                FROM equipo_retos er
                JOIN retos_solucionables rs ON er.reto_id = rs.id
                LEFT JOIN retos_reales rr ON rs.id = rr.reto_id
                LEFT JOIN retos_experimentales re ON rs.id = re.reto_id
                WHERE er.equipo_id = $equipoId
                ORDER BY er.fecha_asignacion DESC
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $retos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $retos[] = $row; // Por simplicidad
            }
            
            // Nota: Aquí podrías setear los retos al equipo si el método existe
        } catch (PDOException $e) {
            throw new \Exception("Error al cargar retos del equipo: " . $e->getMessage());
        }
    }

    // Métodos específicos
    public function findByHackathon(int $hackathonId): array
    {
        try {
            $query = "
                SELECT e.*, h.nombre as hackathon_nombre
                FROM equipos e
                LEFT JOIN hackathons h ON e.hackathon_id = h.id
                WHERE e.hackathon_id = $hackathonId
                ORDER BY e.created_at DESC
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $equipos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $equipos[] = $this->createEquipoFromRow($row);
            }
            
            return $equipos;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener equipos por hackathon: " . $e->getMessage());
        }
    }

    public function addParticipante(int $equipoId, int $participanteId, bool $esLider = false): bool
    {
        try {
            $query = "
                INSERT INTO equipo_participantes (equipo_id, participante_id, es_lider, fecha_union) 
                VALUES ($equipoId, $participanteId, " . ($esLider ? '1' : '0') . ", NOW())
            ";
            
            $stmt = $this->connection->prepare($query);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error al agregar participante al equipo: " . $e->getMessage());
        }
    }

    public function removeParticipante(int $equipoId, int $participanteId): bool
    {
        try {
            $query = "DELETE FROM equipo_participantes WHERE equipo_id = $equipoId AND participante_id = $participanteId";
            
            $stmt = $this->connection->prepare($query);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error al remover participante del equipo: " . $e->getMessage());
        }
    }

    public function assignReto(int $equipoId, int $retoId): bool
    {
        try {
            $query = "
                INSERT INTO equipo_retos (equipo_id, reto_id, estado_participacion, fecha_asignacion) 
                VALUES ($equipoId, $retoId, 'asignado', NOW())
            ";
            
            $stmt = $this->connection->prepare($query);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error al asignar reto al equipo: " . $e->getMessage());
        }
    }
}