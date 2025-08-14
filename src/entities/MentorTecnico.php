<?php
declare(strict_types=1);

namespace App\Entities;

/**
 * Clase MentorTecnico
 * Hereda de Participante - Representa mentores técnicos que guían equipos en EduHacks
 */
class MentorTecnico extends Participante 
{
    private string $especialidad;
    private string $experienciaAnos;
    private string $disponibilidadHoraria;

    public function __construct(
        string $nombre,
        string $email,
        string $telefono = '',
        string $especialidad = '',
        string $experienciaAnos = '0',
        string $disponibilidadHoraria = ''
    ) {
        parent::__construct($nombre, $email, $telefono);
        $this->especialidad = $especialidad;
        $this->experienciaAnos = $experienciaAnos;
        $this->disponibilidadHoraria = $disponibilidadHoraria;
    }

    // Getters específicos
    public function getEspecialidad(): string 
    {
        return $this->especialidad;
    }

    public function getExperienciaAnos(): string 
    {
        return $this->experienciaAnos;
    }

    public function getDisponibilidadHoraria(): string 
    {
        return $this->disponibilidadHoraria;
    }

    // Setters específicos
    public function setEspecialidad(string $especialidad): void 
    {
        $this->especialidad = $especialidad;
        $this->actualizarTimestamp();
    }

    public function setExperienciaAnos(string $experiencia): void 
    {
        $this->experienciaAnos = $experiencia;
        $this->actualizarTimestamp();
    }

    public function setDisponibilidadHoraria(string $disponibilidad): void 
    {
        $this->disponibilidadHoraria = $disponibilidad;
        $this->actualizarTimestamp();
    }

    /**
     * Implementación del método abstracto getHabilidades
     */
    public function getHabilidades(): array 
    {
        $habilidades = ['Mentoría', 'Liderazgo técnico', 'Resolución de problemas'];
        
        // Agregar habilidades basadas en la especialidad
        if (stripos($this->especialidad, 'desarrollo') !== false || stripos($this->especialidad, 'software') !== false) {
            $habilidades[] = 'Desarrollo de software';
            $habilidades[] = 'Arquitectura de software';
        }
        
        if (stripos($this->especialidad, 'ia') !== false || stripos($this->especialidad, 'inteligencia') !== false) {
            $habilidades[] = 'Inteligencia Artificial';
            $habilidades[] = 'Machine Learning';
        }
        
        if (stripos($this->especialidad, 'iot') !== false) {
            $habilidades[] = 'Internet de las Cosas';
            $habilidades[] = 'Sistemas embebidos';
        }
        
        return $habilidades;
    }

    /**
     * Implementación del método abstracto getInformacionEspecifica
     */
    public function getInformacionEspecifica(): array 
    {
        return [
            'tipo' => 'mentor_tecnico',
            'especialidad' => $this->especialidad,
            'experiencia_anos' => $this->experienciaAnos,
            'disponibilidad_horaria' => $this->disponibilidadHoraria,
            'nivel_seniority' => $this->calcularNivelSeniority()
        ];
    }

    /**
     * Calcula el nivel de seniority basado en años de experiencia
     */
    private function calcularNivelSeniority(): string 
    {
        $anos = (int) $this->experienciaAnos;
        
        if ($anos >= 10) {
            return 'senior';
        } elseif ($anos >= 5) {
            return 'semi-senior';
        } else {
            return 'junior';
        }
    }

    /**
     * Convierte la entidad a array para respuestas JSON
     */
    public function toArray(): array 
    {
        return array_merge(parent::toArray(), [
            'especialidad' => $this->especialidad,
            'experiencia_anos' => $this->experienciaAnos,
            'disponibilidad_horaria' => $this->disponibilidadHoraria,
            'habilidades' => $this->getHabilidades(),
            'nivel_seniority' => $this->calcularNivelSeniority()
        ]);
    }

    /**
     * Valida que los datos del mentor sean correctos
     */
    public function validar(): array 
    {
        $errores = parent::validar();
        
        if (empty($this->especialidad)) {
            $errores[] = 'La especialidad es obligatoria';
        }
        
        if (empty($this->experienciaAnos) || !is_numeric($this->experienciaAnos)) {
            $errores[] = 'Los años de experiencia deben ser un número válido';
        }
        
        if (empty($this->disponibilidadHoraria)) {
            $errores[] = 'La disponibilidad horaria es obligatoria';
        }
        
        return $errores;
    }
}
