<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\RepositoryInterface;
use App\Config\Database;
use App\Entities\Participante;
use App\Entities\Estudiante;
use App\Entities\MentorTecnico;
use PDO;
use PDOException;

class ParticipanteRepository implements RepositoryInterface
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
                SELECT p.*, 
                       e.grado, e.institucion, e.tiempo_disponible_semanal,
                       mt.especialidad, mt.experiencia_anos, mt.disponibilidad_horaria
                FROM participantes p
                LEFT JOIN estudiantes e ON p.id = e.participante_id
                LEFT JOIN mentores_tecnicos mt ON p.id = mt.participante_id
                ORDER BY p.created_at DESC
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $participantes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $participantes[] = $this->createParticipanteFromRow($row);
            }
            
            return $participantes;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener participantes: " . $e->getMessage());
        }
    }

    public function findById(int $id): ?object
    {
        try {
            $query = "
                SELECT p.*, 
                       e.grado, e.institucion, e.tiempo_disponible_semanal,
                       mt.especialidad, mt.experiencia_anos, mt.disponibilidad_horaria
                FROM participantes p
                LEFT JOIN estudiantes e ON p.id = e.participante_id
                LEFT JOIN mentores_tecnicos mt ON p.id = mt.participante_id
                WHERE p.id = $id
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $row ? $this->createParticipanteFromRow($row) : null;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener participante: " . $e->getMessage());
        }
    }

    public function save(object $entity): bool
    {
        if ($entity->getId() === null) {
            return $this->createParticipante($entity);
        } else {
            return $this->updateParticipante($entity);
        }
    }

    public function create(object $entity): bool
    {
        return $this->createParticipante($entity);
    }

    public function update(object $entity): bool
    {
        return $this->updateParticipante($entity);
    }

    public function delete(int $id): bool
    {
        try {
            $this->connection->beginTransaction();
            
            // Eliminar de tabla específica primero
            $participante = $this->findById($id);
            if (!$participante) {
                return false;
            }
            
            if ($participante instanceof Estudiante) {
                $stmt = $this->connection->prepare("DELETE FROM estudiantes WHERE participante_id = $id");
                $stmt->execute();
            } elseif ($participante instanceof MentorTecnico) {
                $stmt = $this->connection->prepare("DELETE FROM mentores_tecnicos WHERE participante_id = $id");
                $stmt->execute();
            }
            
            // Eliminar de tabla base
            $stmt = $this->connection->prepare("DELETE FROM participantes WHERE id = $id");
            $result = $stmt->execute();
            
            $this->connection->commit();
            return $result && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw new \Exception("Error al eliminar participante: " . $e->getMessage());
        }
    }

    private function createParticipante(Participante $participante): bool
    {
        try {
            $this->connection->beginTransaction();
            
            // Escapar valores
            $nombre = $this->connection->quote($participante->getNombre());
            $email = $this->connection->quote($participante->getEmail());
            $telefono = $this->connection->quote($participante->getTelefono());
            $tipo = $participante instanceof Estudiante ? 'estudiante' : 'mentor_tecnico';
            
            // Insertar en tabla base
            $query = "
                INSERT INTO participantes (nombre, email, telefono, tipo) 
                VALUES ($nombre, $email, $telefono, '$tipo')
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            $participanteId = (int) $this->connection->lastInsertId();
            $participante->setId($participanteId);
            
            // Insertar en tabla específica
            if ($participante instanceof Estudiante) {
                $this->createEstudiante($participante, $participanteId);
            } elseif ($participante instanceof MentorTecnico) {
                $this->createMentorTecnico($participante, $participanteId);
            }
            
            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw new \Exception("Error al crear participante: " . $e->getMessage());
        }
    }

    private function updateParticipante(Participante $participante): bool
    {
        try {
            $this->connection->beginTransaction();
            
            // Escapar valores
            $id = $participante->getId();
            $nombre = $this->connection->quote($participante->getNombre());
            $email = $this->connection->quote($participante->getEmail());
            $telefono = $this->connection->quote($participante->getTelefono());
            
            // Actualizar tabla base
            $query = "
                UPDATE participantes 
                SET nombre = $nombre, email = $email, telefono = $telefono, 
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = $id
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            // Actualizar tabla específica
            if ($participante instanceof Estudiante) {
                $this->updateEstudiante($participante);
            } elseif ($participante instanceof MentorTecnico) {
                $this->updateMentorTecnico($participante);
            }
            
            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw new \Exception("Error al actualizar participante: " . $e->getMessage());
        }
    }

    private function createEstudiante(Estudiante $estudiante, int $participanteId): void
    {
        $grado = $this->connection->quote($estudiante->getGrado());
        $institucion = $this->connection->quote($estudiante->getInstitucion());
        $tiempoDisponible = $estudiante->getTiempoDisponibleSemanal();
        
        $query = "
            INSERT INTO estudiantes (participante_id, grado, institucion, tiempo_disponible_semanal)
            VALUES ($participanteId, $grado, $institucion, $tiempoDisponible)
        ";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
    }

    private function createMentorTecnico(MentorTecnico $mentor, int $participanteId): void
    {
        $especialidad = $this->connection->quote($mentor->getEspecialidad());
        $experienciaAnos = $mentor->getExperienciaAnos();
        $disponibilidadHoraria = $this->connection->quote($mentor->getDisponibilidadHoraria());
        
        $query = "
            INSERT INTO mentores_tecnicos (participante_id, especialidad, experiencia_anos, disponibilidad_horaria)
            VALUES ($participanteId, $especialidad, $experienciaAnos, $disponibilidadHoraria)
        ";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
    }

    private function updateEstudiante(Estudiante $estudiante): void
    {
        $participanteId = $estudiante->getId();
        $grado = $this->connection->quote($estudiante->getGrado());
        $institucion = $this->connection->quote($estudiante->getInstitucion());
        $tiempoDisponible = $estudiante->getTiempoDisponibleSemanal();
        
        $query = "
            UPDATE estudiantes 
            SET grado = $grado, institucion = $institucion, tiempo_disponible_semanal = $tiempoDisponible
            WHERE participante_id = $participanteId
        ";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
    }

    private function updateMentorTecnico(MentorTecnico $mentor): void
    {
        $participanteId = $mentor->getId();
        $especialidad = $this->connection->quote($mentor->getEspecialidad());
        $experienciaAnos = $mentor->getExperienciaAnos();
        $disponibilidadHoraria = $this->connection->quote($mentor->getDisponibilidadHoraria());
        
        $query = "
            UPDATE mentores_tecnicos 
            SET especialidad = $especialidad, experiencia_anos = $experienciaAnos, disponibilidad_horaria = $disponibilidadHoraria
            WHERE participante_id = $participanteId
        ";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
    }

    private function createParticipanteFromRow(array $row): Participante
    {
        if ($row['tipo'] === 'estudiante') {
            $estudiante = new Estudiante(
                $row['nombre'],
                $row['email'],
                $row['telefono'] ?? '',
                $row['grado'] ?? '',
                $row['institucion'] ?? '',
                (string)($row['tiempo_disponible_semanal'] ?? '0')
            );
            $estudiante->setId((int) $row['id']);
            return $estudiante;
        } else {
            $mentor = new MentorTecnico(
                $row['nombre'],
                $row['email'],
                $row['telefono'] ?? '',
                $row['especialidad'] ?? '',
                (string)($row['experiencia_anos'] ?? '0'),
                $row['disponibilidad_horaria'] ?? ''
            );
            $mentor->setId((int) $row['id']);
            return $mentor;
        }
    }

    // Métodos específicos
    public function findByTipo(string $tipo): array
    {
        try {
            $tipoEscapado = $this->connection->quote($tipo);
            $query = "
                SELECT p.*, 
                       e.grado, e.institucion, e.tiempo_disponible_semanal,
                       mt.especialidad, mt.experiencia_anos, mt.disponibilidad_horaria
                FROM participantes p
                LEFT JOIN estudiantes e ON p.id = e.participante_id
                LEFT JOIN mentores_tecnicos mt ON p.id = mt.participante_id
                WHERE p.tipo = $tipoEscapado
                ORDER BY p.created_at DESC
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $participantes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $participantes[] = $this->createParticipanteFromRow($row);
            }
            
            return $participantes;
        } catch (PDOException $e) {
            throw new \Exception("Error al obtener participantes por tipo: " . $e->getMessage());
        }
    }

    public function findByEmail(string $email): ?Participante
    {
        try {
            $emailEscapado = $this->connection->quote($email);
            $query = "
                SELECT p.*, 
                       e.grado, e.institucion, e.tiempo_disponible_semanal,
                       mt.especialidad, mt.experiencia_anos, mt.disponibilidad_horaria
                FROM participantes p
                LEFT JOIN estudiantes e ON p.id = e.participante_id
                LEFT JOIN mentores_tecnicos mt ON p.id = mt.participante_id
                WHERE p.email = $emailEscapado
            ";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $row ? $this->createParticipanteFromRow($row) : null;
        } catch (PDOException $e) {
            throw new \Exception("Error al buscar participante por email: " . $e->getMessage());
        }
    }
}