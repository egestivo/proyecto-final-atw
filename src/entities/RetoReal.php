<?php
declare(strict_types=1);

namespace App\Entities;

/**
 * Clase RetoReal
 * Hereda de RetoSolucionable - Retos propuestos por empresas u ONGs
 */
class RetoReal extends RetoSolucionable 
{
    private string $entidadColaboradora;

    public function __construct(
        string $titulo,
        string $descripcion,
        string $dificultad,
        string $tecnologiasRequeridas = '',
        string $hackathonId = '1',
        string $entidadColaboradora = ''
    ) {
        parent::__construct($titulo, $descripcion, $dificultad, (int) $hackathonId, $tecnologiasRequeridas, 'reto_real');
        $this->entidadColaboradora = $entidadColaboradora;
    }

    // Getters específicos
    public function getEntidadColaboradora(): string 
    {
        return $this->entidadColaboradora;
    }

    // Setters específicos
    public function setEntidadColaboradora(string $entidadColaboradora): void 
    {
        $this->entidadColaboradora = $entidadColaboradora;
        $this->actualizarTimestamp();
    }

    /**
     * Implementación del método abstracto getCriteriosEvaluacion
     */
    public function getCriteriosEvaluacion(): array 
    {
        return [
            'viabilidad_tecnica' => 'Factibilidad técnica de la solución',
            'impacto_social' => 'Impacto esperado en la sociedad',
            'innovacion' => 'Nivel de innovación propuesto',
            'factibilidad_empresa' => 'Viabilidad de implementación en la entidad colaboradora'
        ];
    }

    /**
     * Implementación del método abstracto getInformacionEspecifica
     */
    public function getInformacionEspecifica(): array 
    {
        return [
            'tipo' => 'reto_real',
            'entidad_colaboradora' => $this->entidadColaboradora,
            'origen' => 'empresa_ong',
            'enfoque' => 'solucion_practica'
        ];
    }

    /**
     * Implementación del método abstracto evaluarSolucion
     */
    public function evaluarSolucion(array $criterios): array 
    {
        $evaluacion = [
            'viabilidad_tecnica' => $criterios['viabilidad_tecnica'] ?? 0,
            'impacto_social' => $criterios['impacto_social'] ?? 0,
            'innovacion' => $criterios['innovacion'] ?? 0,
            'factibilidad_empresa' => $criterios['factibilidad_empresa'] ?? 0
        ];
        
        $promedio = array_sum($evaluacion) / count($evaluacion);
        
        return [
            'criterios' => $evaluacion,
            'puntuacion_total' => $promedio,
            'aprobado' => $promedio >= 7.0,
            'comentarios' => "Reto real propuesto por: {$this->entidadColaboradora}"
        ];
    }

    /**
     * Implementación del método abstracto getMetricas
     */
    public function getMetricas(): array 
    {
        return [
            'tipo_reto' => 'real',
            'entidad_colaboradora' => $this->entidadColaboradora,
            'origen' => 'empresa_ong',
            'enfoque' => 'solucion_practica',
            'aplicabilidad' => 'inmediata'
        ];
    }

    /**
     * Convierte la entidad a array para respuestas JSON
     */
    public function toArray(): array 
    {
        return array_merge(parent::toArray(), [
            'entidad_colaboradora' => $this->entidadColaboradora,
            'tipo_especifico' => 'reto_real',
            'origen' => 'empresa_ong'
        ]);
    }

    /**
     * Valida que los datos del reto real sean correctos
     */
    public function validar(): array 
    {
        $errores = parent::validar();
        
        if (empty($this->entidadColaboradora)) {
            $errores[] = 'La entidad colaboradora es obligatoria';
        }
        
        return $errores;
    }
}
