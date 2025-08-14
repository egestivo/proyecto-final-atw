<?php
declare(strict_types=1);

namespace App\entities;

/**
 * Clase Hackathon
 * Representa un evento hackathon donde participan equipos y se resuelven retos
 */
class Hackathon {
    private int $id;
    private string $nombre;
    private ?string $descripcion;
    private \DateTime $fechaInicio;
    private \DateTime $fechaFin;
    private ?string $lugar;
    private string $estado; // 'planificacion', 'activo', 'finalizado'
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        string $nombre,
        \DateTime $fechaInicio,
        \DateTime $fechaFin,
        ?string $descripcion = null,
        ?string $lugar = null,
        string $estado = 'planificacion'
    ) {
        $this->nombre = $nombre;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->descripcion = $descripcion;
        $this->lugar = $lugar;
        $this->estado = $estado;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

        $this->validarFechas();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getFechaInicio(): \DateTime { return $this->fechaInicio; }
    public function getFechaFin(): \DateTime { return $this->fechaFin; }
    public function getLugar(): ?string { return $this->lugar; }
    public function getEstado(): string { return $this->estado; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    
    public function setNombre(string $nombre): void { 
        $this->nombre = $nombre; 
        $this->actualizarTimestamp(); 
    }
    
    public function setDescripcion(?string $descripcion): void { 
        $this->descripcion = $descripcion; 
        $this->actualizarTimestamp(); 
    }
    
    public function setFechaInicio(\DateTime $fechaInicio): void { 
        $this->fechaInicio = $fechaInicio; 
        $this->validarFechas();
        $this->actualizarTimestamp(); 
    }
    
    public function setFechaFin(\DateTime $fechaFin): void { 
        $this->fechaFin = $fechaFin; 
        $this->validarFechas();
        $this->actualizarTimestamp(); 
    }
    
    public function setLugar(?string $lugar): void { 
        $this->lugar = $lugar; 
        $this->actualizarTimestamp(); 
    }
    
    public function setEstado(string $estado): void { 
        $estadosValidos = ['planificacion', 'activo', 'finalizado'];
        if (!in_array($estado, $estadosValidos)) {
            throw new \InvalidArgumentException('Estado no válido');
        }
        $this->estado = $estado; 
        $this->actualizarTimestamp(); 
    }

    public function setCreatedAt(\DateTime $createdAt): void { $this->createdAt = $createdAt; }
    public function setUpdatedAt(\DateTime $updatedAt): void { $this->updatedAt = $updatedAt; }

    // Métodos de utilidad
    private function actualizarTimestamp(): void {
        $this->updatedAt = new \DateTime();
    }

    private function validarFechas(): void {
        if ($this->fechaInicio >= $this->fechaFin) {
            throw new \InvalidArgumentException('La fecha de inicio debe ser anterior a la fecha de fin');
        }
    }

    /**
     * Calcula la duración del hackathon en días
     */
    public function getDuracionDias(): int {
        return $this->fechaInicio->diff($this->fechaFin)->days + 1;
    }

    /**
     * Calcula la duración del hackathon en horas
     */
    public function getDuracionHoras(): int {
        $diff = $this->fechaInicio->diff($this->fechaFin);
        return ($diff->days * 24) + $diff->h;
    }

    /**
     * Verifica si el hackathon está actualmente en curso
     */
    public function estaEnCurso(): bool {
        $ahora = new \DateTime();
        return $ahora >= $this->fechaInicio && $ahora <= $this->fechaFin;
    }

    /**
     * Verifica si el hackathon ya terminó
     */
    public function haTerminado(): bool {
        return new \DateTime() > $this->fechaFin;
    }

    /**
     * Verifica si el hackathon aún no ha comenzado
     */
    public function noHaComenzado(): bool {
        return new \DateTime() < $this->fechaInicio;
    }

    /**
     * Obtiene el estado calculado basado en las fechas
     */
    public function getEstadoCalculado(): string {
        if ($this->haTerminado()) {
            return 'finalizado';
        } elseif ($this->estaEnCurso()) {
            return 'activo';
        } else {
            return 'planificacion';
        }
    }

    /**
     * Calcula días restantes hasta el inicio o fin
     */
    public function getDiasRestantes(): array {
        $ahora = new \DateTime();
        
        if ($this->noHaComenzado()) {
            $diff = $ahora->diff($this->fechaInicio);
            return [
                'tipo' => 'para_inicio',
                'dias' => $diff->days,
                'mensaje' => "Faltan {$diff->days} días para el inicio"
            ];
        } elseif ($this->estaEnCurso()) {
            $diff = $ahora->diff($this->fechaFin);
            return [
                'tipo' => 'para_fin',
                'dias' => $diff->days,
                'horas' => $diff->h,
                'mensaje' => "Quedan {$diff->days} días y {$diff->h} horas"
            ];
        } else {
            return [
                'tipo' => 'finalizado',
                'dias' => 0,
                'mensaje' => 'El hackathon ha finalizado'
            ];
        }
    }

    /**
     * Determina el tipo de hackathon basado en duración y nombre
     */
    public function getTipoHackathon(): string {
        $duracion = $this->getDuracionHoras();
        $nombre = strtolower($this->nombre);

        if ($duracion <= 24) {
            return 'sprint'; // 24 horas o menos
        } elseif ($duracion <= 48) {
            return 'tradicional'; // 48 horas típico
        } elseif ($duracion <= 72) {
            return 'extendido'; // 3 días
        } else {
            return 'maratón'; // Más de 3 días
        }
    }

    /**
     * Verifica si permite inscripciones
     */
    public function permiteInscripciones(): bool {
        return $this->estado === 'planificacion' && $this->noHaComenzado();
    }

    /**
     * Obtiene información de modalidad basada en el lugar
     */
    public function getModalidad(): array {
        if (empty($this->lugar)) {
            return ['tipo' => 'no_especificada', 'descripcion' => 'Modalidad no especificada'];
        }

        $lugar = strtolower($this->lugar);
        
        if (strpos($lugar, 'online') !== false || strpos($lugar, 'virtual') !== false) {
            return ['tipo' => 'virtual', 'descripcion' => 'Completamente virtual'];
        } elseif (strpos($lugar, '+') !== false || strpos($lugar, 'híbrido') !== false) {
            return ['tipo' => 'hibrido', 'descripcion' => 'Híbrido (presencial + virtual)'];
        } else {
            return ['tipo' => 'presencial', 'descripcion' => 'Completamente presencial'];
        }
    }

    /**
     * Convierte la entidad a array para serialización
     */
    public function toArray(): array {
        return [
            'id' => $this->id ?? null,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'fecha_inicio' => $this->fechaInicio->format('Y-m-d H:i:s'),
            'fecha_fin' => $this->fechaFin->format('Y-m-d H:i:s'),
            'lugar' => $this->lugar,
            'estado' => $this->estado,
            'estado_calculado' => $this->getEstadoCalculado(),
            'duracion_dias' => $this->getDuracionDias(),
            'duracion_horas' => $this->getDuracionHoras(),
            'tipo_hackathon' => $this->getTipoHackathon(),
            'modalidad' => $this->getModalidad(),
            'en_curso' => $this->estaEnCurso(),
            'ha_terminado' => $this->haTerminado(),
            'no_ha_comenzado' => $this->noHaComenzado(),
            'permite_inscripciones' => $this->permiteInscripciones(),
            'dias_restantes' => $this->getDiasRestantes(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Valida que los datos del hackathon sean correctos
     */
    public function validar(): array {
        $errores = [];

        if (empty($this->nombre)) {
            $errores[] = 'El nombre es obligatorio';
        }

        if ($this->fechaInicio >= $this->fechaFin) {
            $errores[] = 'La fecha de inicio debe ser anterior a la fecha de fin';
        }

        $estadosValidos = ['planificacion', 'activo', 'finalizado'];
        if (!in_array($this->estado, $estadosValidos)) {
            $errores[] = 'El estado debe ser: ' . implode(', ', $estadosValidos);
        }

        // Validar que la duración sea razonable (entre 4 horas y 1 semana)
        $horas = $this->getDuracionHoras();
        if ($horas < 4) {
            $errores[] = 'La duración mínima debe ser de 4 horas';
        } elseif ($horas > 168) { // 7 días
            $errores[] = 'La duración máxima recomendada es de 7 días';
        }

        return $errores;
    }
}
