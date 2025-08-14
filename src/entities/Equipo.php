<?php
declare(strict_types=1);

namespace App\entities;

/**
 * Clase Equipo
 * Representa un equipo formado durante un EduHack específico
 */
class Equipo {
    private int $id;
    private string $nombre;
    private ?string $descripcion;
    private int $hackathonId;
    private \DateTime $fechaFormacion;
    private string $estado; // 'formandose', 'completo', 'activo', 'finalizado'
    private int $maxIntegrantes;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    // Arrays para manejar relaciones
    private array $participantes = [];
    private array $retos = [];

    public function __construct(
        string $nombre,
        int $hackathonId,
        ?string $descripcion = null,
        string $estado = 'formandose',
        int $maxIntegrantes = 6
    ) {
        $this->nombre = $nombre;
        $this->hackathonId = $hackathonId;
        $this->descripcion = $descripcion;
        $this->estado = $estado;
        $this->maxIntegrantes = $maxIntegrantes;
        $this->fechaFormacion = new \DateTime();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getHackathonId(): int { return $this->hackathonId; }
    public function getFechaFormacion(): \DateTime { return $this->fechaFormacion; }
    public function getEstado(): string { return $this->estado; }
    public function getMaxIntegrantes(): int { return $this->maxIntegrantes; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function getParticipantes(): array { return $this->participantes; }
    public function getRetos(): array { return $this->retos; }

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
    
    public function setHackathonId(int $hackathonId): void { 
        $this->hackathonId = $hackathonId; 
        $this->actualizarTimestamp(); 
    }
    
    public function setFechaFormacion(\DateTime $fechaFormacion): void { 
        $this->fechaFormacion = $fechaFormacion; 
    }
    
    public function setEstado(string $estado): void { 
        $estadosValidos = ['formandose', 'completo', 'activo', 'finalizado'];
        if (!in_array($estado, $estadosValidos)) {
            throw new \InvalidArgumentException('Estado no válido');
        }
        $this->estado = $estado; 
        $this->actualizarTimestamp(); 
    }
    
    public function setMaxIntegrantes(int $maxIntegrantes): void { 
        if ($maxIntegrantes < 2) {
            throw new \InvalidArgumentException('Un equipo debe tener al menos 2 integrantes máximos');
        }
        $this->maxIntegrantes = $maxIntegrantes; 
        $this->actualizarTimestamp(); 
    }

    public function setCreatedAt(\DateTime $createdAt): void { $this->createdAt = $createdAt; }
    public function setUpdatedAt(\DateTime $updatedAt): void { $this->updatedAt = $updatedAt; }
    public function setParticipantes(array $participantes): void { $this->participantes = $participantes; }
    public function setRetos(array $retos): void { $this->retos = $retos; }

    // Métodos de utilidad
    private function actualizarTimestamp(): void {
        $this->updatedAt = new \DateTime();
    }

    /**
     * Obtiene el número actual de integrantes
     */
    public function getNumeroIntegrantes(): int {
        return count($this->participantes);
    }

    /**
     * Verifica si el equipo está completo
     */
    public function estaCompleto(): bool {
        return $this->getNumeroIntegrantes() >= $this->maxIntegrantes;
    }

    /**
     * Verifica si el equipo puede aceptar más integrantes
     */
    public function puedeAceptarIntegrantes(): bool {
        return $this->getNumeroIntegrantes() < $this->maxIntegrantes && 
               in_array($this->estado, ['formandose', 'completo']);
    }

    /**
     * Verifica si el equipo tiene al menos un mentor
     */
    public function tieneMentor(): bool {
        foreach ($this->participantes as $participante) {
            if (isset($participante['rol_en_equipo']) && $participante['rol_en_equipo'] === 'mentor') {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica si el equipo tiene un líder designado
     */
    public function tieneLider(): bool {
        foreach ($this->participantes as $participante) {
            if (isset($participante['rol_en_equipo']) && $participante['rol_en_equipo'] === 'lider') {
                return true;
            }
        }
        return false;
    }

    /**
     * Obtiene la distribución de roles en el equipo
     */
    public function getDistribucionRoles(): array {
        $roles = [];
        foreach ($this->participantes as $participante) {
            $rol = $participante['rol_en_equipo'] ?? 'sin_rol';
            $roles[$rol] = ($roles[$rol] ?? 0) + 1;
        }
        return $roles;
    }

    /**
     * Obtiene la diversidad de habilidades del equipo
     */
    public function getHabilidadesEquipo(): array {
        $habilidades = [];
        foreach ($this->participantes as $participante) {
            if (isset($participante['habilidades']) && is_array($participante['habilidades'])) {
                $habilidades = array_merge($habilidades, $participante['habilidades']);
            }
        }
        return array_unique($habilidades);
    }

    /**
     * Calcula el nivel promedio de experiencia del equipo
     */
    public function getNivelExperienciaPromedio(): string {
        if (empty($this->participantes)) {
            return 'sin_determinar';
        }

        $niveles = ['principiante' => 1, 'junior' => 2, 'intermedio' => 3, 'semi-senior' => 4, 'senior' => 5, 'avanzado' => 5];
        $sumaLevels = 0;
        $count = 0;

        foreach ($this->participantes as $participante) {
            $nivel = $participante['nivel_experiencia'] ?? $participante['nivel_senioridad'] ?? 'intermedio';
            if (isset($niveles[$nivel])) {
                $sumaLevels += $niveles[$nivel];
                $count++;
            }
        }

        if ($count === 0) return 'intermedio';

        $promedio = $sumaLevels / $count;
        
        if ($promedio >= 4.5) return 'avanzado';
        if ($promedio >= 3.5) return 'intermedio';
        if ($promedio >= 2.5) return 'junior';
        return 'principiante';
    }

    /**
     * Verifica si el equipo está balanceado (tiene variedad de roles)
     */
    public function estaBalanceado(): bool {
        $roles = $this->getDistribucionRoles();
        
        // Un equipo balanceado debe tener:
        // - Al menos un líder
        // - Al menos un desarrollador
        // - Idealmente un mentor (si es posible)
        
        $tieneRolesEsenciales = isset($roles['lider']) && 
                               (isset($roles['desarrollador']) || isset($roles['disenador']) || isset($roles['analista']));
        
        $tieneVariedad = count($roles) >= 2; // Al menos 2 tipos de roles diferentes
        
        return $tieneRolesEsenciales && $tieneVariedad;
    }

    /**
     * Calcula la compatibilidad del equipo con un reto específico
     */
    public function calcularCompatibilidadConReto(RetoSolucionable $reto): float {
        if (empty($this->participantes)) {
            return 0.0;
        }

        $tecnologiasReto = array_map('strtolower', $reto->getTecnologiasArray());
        $habilidadesEquipo = array_map('strtolower', $this->getHabilidadesEquipo());
        
        if (empty($tecnologiasReto)) {
            return 0.5; // Compatibilidad neutral
        }

        $coincidencias = array_intersect($habilidadesEquipo, $tecnologiasReto);
        $compatibilidadBase = count($coincidencias) / count($tecnologiasReto);
        
        // Bonus por tener mentor
        $bonusMentor = $this->tieneMentor() ? 0.1 : 0;
        
        // Bonus por estar balanceado
        $bonusBalance = $this->estaBalanceado() ? 0.1 : 0;
        
        // Bonus/penalty por nivel de experiencia vs dificultad del reto
        $nivelEquipo = $this->getNivelExperienciaPromedio();
        $dificultadReto = $reto->getDificultad();
        
        $bonusNivel = 0;
        if (($nivelEquipo === 'avanzado' && $dificultadReto === 'avanzado') ||
            ($nivelEquipo === 'intermedio' && $dificultadReto === 'intermedio') ||
            ($nivelEquipo === 'principiante' && $dificultadReto === 'basico')) {
            $bonusNivel = 0.1;
        }

        return min($compatibilidadBase + $bonusMentor + $bonusBalance + $bonusNivel, 1.0);
    }

    /**
     * Obtiene el número de retos activos del equipo
     */
    public function getNumeroRetosActivos(): int {
        $activos = 0;
        foreach ($this->retos as $reto) {
            if (in_array($reto['estado'] ?? '', ['asignado', 'en_desarrollo'])) {
                $activos++;
            }
        }
        return $activos;
    }

    /**
     * Verifica si el equipo puede tomar más retos
     */
    public function puedeTomarMasRetos(): bool {
        $maxRetos = $this->tieneMentor() ? 3 : 2; // Con mentor pueden manejar más retos
        return $this->getNumeroRetosActivos() < $maxRetos && 
               in_array($this->estado, ['completo', 'activo']);
    }

    /**
     * Calcula el progreso promedio de todos los retos del equipo
     */
    public function getProgresoPromedio(): float {
        if (empty($this->retos)) {
            return 0.0;
        }

        $sumaProgreso = 0;
        $count = 0;

        foreach ($this->retos as $reto) {
            if (isset($reto['progreso'])) {
                $sumaProgreso += (int)$reto['progreso'];
                $count++;
            }
        }

        return $count > 0 ? $sumaProgreso / $count : 0.0;
    }

    /**
     * Convierte la entidad a array para serialización
     */
    public function toArray(): array {
        return [
            'id' => $this->id ?? null,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'hackathon_id' => $this->hackathonId,
            'fecha_formacion' => $this->fechaFormacion->format('Y-m-d H:i:s'),
            'estado' => $this->estado,
            'max_integrantes' => $this->maxIntegrantes,
            'numero_integrantes' => $this->getNumeroIntegrantes(),
            'esta_completo' => $this->estaCompleto(),
            'puede_aceptar_integrantes' => $this->puedeAceptarIntegrantes(),
            'tiene_mentor' => $this->tieneMentor(),
            'tiene_lider' => $this->tieneLider(),
            'esta_balanceado' => $this->estaBalanceado(),
            'distribucion_roles' => $this->getDistribucionRoles(),
            'habilidades_equipo' => $this->getHabilidadesEquipo(),
            'nivel_experiencia_promedio' => $this->getNivelExperienciaPromedio(),
            'numero_retos_activos' => $this->getNumeroRetosActivos(),
            'puede_tomar_mas_retos' => $this->puedeTomarMasRetos(),
            'progreso_promedio' => $this->getProgresoPromedio(),
            'participantes' => $this->participantes,
            'retos' => $this->retos,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Valida que los datos del equipo sean correctos
     */
    public function validar(): array {
        $errores = [];

        if (empty($this->nombre)) {
            $errores[] = 'El nombre del equipo es obligatorio';
        }

        if ($this->hackathonId <= 0) {
            $errores[] = 'Debe especificar un hackathon válido';
        }

        if ($this->maxIntegrantes < 2) {
            $errores[] = 'Un equipo debe permitir al menos 2 integrantes';
        }

        if ($this->maxIntegrantes > 10) {
            $errores[] = 'Se recomienda un máximo de 10 integrantes por equipo';
        }

        $estadosValidos = ['formandose', 'completo', 'activo', 'finalizado'];
        if (!in_array($this->estado, $estadosValidos)) {
            $errores[] = 'El estado debe ser: ' . implode(', ', $estadosValidos);
        }

        return $errores;
    }
}
