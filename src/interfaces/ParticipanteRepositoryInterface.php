<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Entities\Participante;

/**
 * Interfaz para el repositorio de Participantes
 */
interface ParticipanteRepositoryInterface extends RepositoryInterface
{
    /**
     * Buscar participantes por tipo (estudiante/mentor_tecnico)
     */
    public function findByType(string $tipo): array;

    /**
     * Buscar participantes por email
     */
    public function findByEmail(string $email): ?Participante;

    /**
     * Buscar participantes disponibles (sin equipo asignado en un hackathon)
     */
    public function findDisponibles(int $hackathonId): array;
}
