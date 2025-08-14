<?php
declare(strict_types=1);

namespace App\entities;

/**
 * Clase Abstracta: RetoSolucionable
 * Representa cualquier desafío que puede ser resuelto en un EduHack.
 * Es abstracta porque los retos pueden variar en formato.
 */
abstract class RetoSolucionable {
    protected int $id;
    protected string $titulo;
    protected string $descripcion;
    protected string $dificultad; // 'basico', 'intermedio', 'avanzado'
    protected ?string $tecnologiasRequeridas;
    protected string $tipo;
    protected string $estado; // 'borrador', 'publicado', 'en_desarrollo', 'completado'
    protected int $hackathonId;
    protected \DateTime $createdAt;
    protected \DateTime $updatedAt;

    public function __construct(
        string $titulo,
        string $descripcion,
        string $dificultad,
        int $hackathonId,
        ?string $tecnologiasRequeridas = null,
        string $tipo = '',
        string $estado = 'borrador'
    ) {
        $this->titulo = $titulo;
        $this->descripcion = $descripcion;
        $this->dificultad = $dificultad;
        $this->hackathonId = $hackathonId;
        $this->tecnologiasRequeridas = $tecnologiasRequeridas;
        $this->tipo = $tipo;
        $this->estado = $estado;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getTitulo(): string { return $this->titulo; }
    public function getDescripcion(): string { return $this->descripcion; }
    public function getDificultad(): string { return $this->dificultad; }
    public function getTecnologiasRequeridas(): ?string { return $this->tecnologiasRequeridas; }
    public function getTipo(): string { return $this->tipo; }
    public function getEstado(): string { return $this->estado; }
    public function getHackathonId(): int { return $this->hackathonId; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setTitulo(string $titulo): void { $this->titulo = $titulo; $this->actualizarTimestamp(); }
    public function setDescripcion(string $descripcion): void { $this->descripcion = $descripcion; $this->actualizarTimestamp(); }
    public function setDificultad(string $dificultad): void { 
        if (!in_array($dificultad, ['basico', 'intermedio', 'avanzado'])) {
            throw new \InvalidArgumentException('Dificultad no válida');
        }
        $this->dificultad = $dificultad; 
        $this->actualizarTimestamp(); 
    }
    public function setTecnologiasRequeridas(?string $tecnologiasRequeridas): void { $this->tecnologiasRequeridas = $tecnologiasRequeridas; $this->actualizarTimestamp(); }
    public function setEstado(string $estado): void { 
        $estadosValidos = ['borrador', 'publicado', 'en_desarrollo', 'completado'];
        if (!in_array($estado, $estadosValidos)) {
            throw new \InvalidArgumentException('Estado no válido');
        }
        $this->estado = $estado; 
        $this->actualizarTimestamp(); 
    }
    public function setHackathonId(int $hackathonId): void { $this->hackathonId = $hackathonId; $this->actualizarTimestamp(); }
    public function setCreatedAt(\DateTime $createdAt): void { $this->createdAt = $createdAt; }
    public function setUpdatedAt(\DateTime $updatedAt): void { $this->updatedAt = $updatedAt; }

    // Métodos de utilidad
    protected function actualizarTimestamp(): void {
        $this->updatedAt = new \DateTime();
    }

    /**
     * Método abstracto que debe ser implementado por las clases hijas
     * para definir criterios específicos de evaluación
     */
    abstract public function getCriteriosEvaluacion(): array;

    /**
     * Método abstracto para obtener información específica del tipo de reto
     */
    abstract public function getInformacionEspecifica(): array;

    /**
     * Obtiene las tecnologías como array
     */
    public function getTecnologiasArray(): array {
        if (empty($this->tecnologiasRequeridas)) {
            return [];
        }
        return array_map('trim', explode(',', $this->tecnologiasRequeridas));
    }

    /**
     * Verifica si el reto está disponible para asignación
     */
    public function estaDisponible(): bool {
        return $this->estado === 'publicado';
    }

    /**
     * Obtiene el nivel de dificultad numérico
     */
    public function getNivelDificultad(): int {
        return match($this->dificultad) {
            'basico' => 1,
            'intermedio' => 2,
            'avanzado' => 3,
            default => 0
        };
    }

    /**
     * Convierte la entidad a array para serialización
     */
    public function toArray(): array {
        return [
            'id' => $this->id ?? null,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'dificultad' => $this->dificultad,
            'nivel_dificultad' => $this->getNivelDificultad(),
            'tecnologias_requeridas' => $this->tecnologiasRequeridas,
            'tecnologias_array' => $this->getTecnologiasArray(),
            'tipo' => $this->tipo,
            'estado' => $this->estado,
            'hackathon_id' => $this->hackathonId,
            'disponible' => $this->estaDisponible(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'criterios_evaluacion' => $this->getCriteriosEvaluacion(),
            'informacion_especifica' => $this->getInformacionEspecifica()
        ];
    }

    /**
     * Valida que los datos del reto sean correctos
     */
    public function validar(): array {
        $errores = [];

        if (empty($this->titulo)) {
            $errores[] = 'El título es obligatorio';
        }

        if (empty($this->descripcion)) {
            $errores[] = 'La descripción es obligatoria';
        }

        if (!in_array($this->dificultad, ['basico', 'intermedio', 'avanzado'])) {
            $errores[] = 'La dificultad debe ser: basico, intermedio o avanzado';
        }

        if ($this->hackathonId <= 0) {
            $errores[] = 'Debe especificar un hackathon válido';
        }

        return $errores;
    }
}