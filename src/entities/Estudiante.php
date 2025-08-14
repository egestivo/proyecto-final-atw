<?php
declare(strict_types=1);

namespace App\Entities;

/**
 * Clase Estudiante
 * Hereda de Participante - Representa estudiantes que participan en EduHacks
 */
class Estudiante extends Participante 
{
    private string $grado;
    private string $institucion;
    private string $tiempoDisponibleSemanal; // Horas disponibles por semana

    public function __construct(
        string $nombre,
        string $email,
        string $telefono = '',
        string $grado = '',
        string $institucion = '',
        string $tiempoDisponibleSemanal = '0'
    ) {
        parent::__construct($nombre, $email, $telefono);
        $this->grado = $grado;
        $this->institucion = $institucion;
        $this->tiempoDisponibleSemanal = $tiempoDisponibleSemanal;
    }

    // Getters específicos
    public function getGrado(): string 
    {
        return $this->grado;
    }

    public function getInstitucion(): string 
    {
        return $this->institucion;
    }

    public function getTiempoDisponibleSemanal(): string 
    {
        return $this->tiempoDisponibleSemanal;
    }

    // Setters específicos
    public function setGrado(string $grado): void 
    {
        $this->grado = $grado;
        $this->actualizarTimestamp();
    }

    public function setInstitucion(string $institucion): void 
    {
        $this->institucion = $institucion;
        $this->actualizarTimestamp();
    }

    public function setTiempoDisponibleSemanal(string $tiempo): void 
    {
        $this->tiempoDisponibleSemanal = $tiempo;
        $this->actualizarTimestamp();
    }

    /**
     * Implementación del método abstracto getHabilidades
     */
    public function getHabilidades(): array 
    {
        // Para simplificar, retornamos habilidades básicas basadas en el grado
        $habilidades = ['Trabajo en equipo', 'Resolución de problemas'];
        
        if (stripos($this->grado, 'ingeniería') !== false || stripos($this->grado, 'software') !== false) {
            $habilidades[] = 'Programación';
            $habilidades[] = 'Desarrollo de software';
        }
        
        if (stripos($this->grado, 'diseño') !== false) {
            $habilidades[] = 'Diseño';
            $habilidades[] = 'Creatividad';
        }
        
        return $habilidades;
    }

    /**
     * Implementación del método abstracto getInformacionEspecifica
     */
    public function getInformacionEspecifica(): array 
    {
        return [
            'tipo' => 'estudiante',
            'grado' => $this->grado,
            'institucion' => $this->institucion,
            'tiempo_disponible_semanal' => $this->tiempoDisponibleSemanal,
            'nivel_experiencia' => $this->calcularNivelExperiencia()
        ];
    }

    /**
     * Calcula el nivel de experiencia basado en el grado académico
     */
    private function calcularNivelExperiencia(): string 
    {
        $grado_lower = strtolower($this->grado);
        
        if (strpos($grado_lower, '1') !== false || strpos($grado_lower, '2') !== false) {
            return 'principiante';
        } elseif (strpos($grado_lower, '3') !== false || strpos($grado_lower, '4') !== false) {
            return 'intermedio';
        } else {
            return 'avanzado';
        }
    }

    /**
     * Convierte la entidad a array para respuestas JSON
     */
    public function toArray(): array 
    {
        return array_merge(parent::toArray(), [
            'grado' => $this->grado,
            'institucion' => $this->institucion,
            'tiempo_disponible_semanal' => $this->tiempoDisponibleSemanal,
            'habilidades' => $this->getHabilidades(),
            'nivel_experiencia' => $this->calcularNivelExperiencia()
        ]);
    }

    /**
     * Valida que los datos del estudiante sean correctos
     */
    public function validar(): array 
    {
        $errores = parent::validar();
        
        if (empty($this->grado)) {
            $errores[] = 'El grado académico es obligatorio';
        }
        
        if (empty($this->institucion)) {
            $errores[] = 'La institución es obligatoria';
        }
        
        if (empty($this->tiempoDisponibleSemanal) || !is_numeric($this->tiempoDisponibleSemanal)) {
            $errores[] = 'El tiempo disponible semanal debe ser un número válido';
        }
        
        return $errores;
    }
}
