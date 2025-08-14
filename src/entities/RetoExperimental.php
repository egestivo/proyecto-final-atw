<?php
declare(strict_types=1);

namespace App\Entities;

/**
 * Clase RetoExperimental
 * Hereda de RetoSolucionable - Retos diseñados por docentes para explorar nuevas tecnologías
 */
class RetoExperimental extends RetoSolucionable 
{
    private string $enfoquePedagogico; // STEM, STEAM, ABP, Design_Thinking, Otro

    public function __construct(
        string $titulo,
        string $descripcion,
        string $dificultad,
        string $tecnologiasRequeridas = '',
        string $hackathonId = '1',
        string $enfoquePedagogico = 'STEM',
    ) {
        parent::__construct($titulo, $descripcion, $dificultad, (int) $hackathonId, $tecnologiasRequeridas, 'reto_experimental');
        $this->enfoquePedagogico = $enfoquePedagogico;
    }

    // Getters específicos
    public function getEnfoquePedagogico(): string 
    {
        return $this->enfoquePedagogico;
    }

    // Setters específicos
    public function setEnfoquePedagogico(string $enfoquePedagogico): void 
    {
        $this->enfoquePedagogico = $enfoquePedagogico;
        $this->actualizarTimestamp();
    }

    /**
     * Implementación del método abstracto getCriteriosEvaluacion
     */
    public function getCriteriosEvaluacion(): array 
    {
        return [
            'cumplimiento_objetivos' => 'Cumplimiento de objetivos de aprendizaje',
            'uso_tecnologias' => 'Uso apropiado de nuevas tecnologías',
            'innovacion_pedagogica' => 'Innovación en el enfoque pedagógico',
            'aplicabilidad_educativa' => 'Aplicabilidad en contextos educativos'
        ];
    }

    /**
     * Implementación del método abstracto getInformacionEspecifica
     */
    public function getInformacionEspecifica(): array 
    {
        return [
            'tipo' => 'reto_experimental',
            'enfoque_pedagogico' => $this->enfoquePedagogico,
            'origen' => 'docente',
            'finalidad' => 'exploracion_tecnologica'
        ];
    }

    /**
     * Implementación del método abstracto evaluarSolucion
     */
    public function evaluarSolucion(array $criterios): array 
    {
        $evaluacion = [
            'cumplimiento_objetivos' => $criterios['cumplimiento_objetivos'] ?? 0,
            'uso_tecnologias' => $criterios['uso_tecnologias'] ?? 0,
            'innovacion_pedagogica' => $criterios['innovacion_pedagogica'] ?? 0,
            'aplicabilidad_educativa' => $criterios['aplicabilidad_educativa'] ?? 0
        ];
        
        $promedio = array_sum($evaluacion) / count($evaluacion);
        
        return [
            'criterios' => $evaluacion,
            'puntuacion_total' => $promedio,
            'aprobado' => $promedio >= 7.0,
            'comentarios' => "Reto experimental con enfoque: {$this->enfoquePedagogico}"
        ];
    }

    /**
     * Implementación del método abstracto getMetricas
     */
    public function getMetricas(): array 
    {
        return [
            'tipo_reto' => 'experimental',
            'enfoque_pedagogico' => $this->enfoquePedagogico,
            'origen' => 'docente',
            'enfoque' => 'exploracion_tecnologica',
            'aplicabilidad' => 'educativa'
        ];
    }

    /**
     * Convierte la entidad a array para respuestas JSON
     */
    public function toArray(): array 
    {
        return array_merge(parent::toArray(), [
            'enfoque_pedagogico' => $this->enfoquePedagogico,
            'tipo_especifico' => 'reto_experimental',
            'origen' => 'docente'
        ]);
    }

    /**
     * Valida que los datos del reto experimental sean correctos
     */
    public function validar(): array 
    {
        $errores = parent::validar();
        
        if (empty($this->enfoquePedagogico)) {
            $errores[] = 'El enfoque pedagógico es obligatorio';
        }
        
        $enfoquesValidos = ['STEM', 'STEAM', 'ABP', 'Design_Thinking', 'Otro'];
        if (!in_array($this->enfoquePedagogico, $enfoquesValidos)) {
            $errores[] = 'El enfoque pedagógico debe ser uno de: ' . implode(', ', $enfoquesValidos);
        }
        
        return $errores;
    }
}
