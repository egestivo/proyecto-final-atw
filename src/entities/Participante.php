<?php
declare(strict_types=1);

namespace App\entities;

/**
 * Clase Abstracta: Participante
 * Representa a cualquier persona que participa en un EduHack.
 * Es abstracta porque no existe un "participante puro", solo tipos específicos.
 */
abstract class Participante {
    protected int $id;
    protected string $nombre;
    protected string $email;
    protected ?string $telefono;
    protected string $tipo;
    protected \DateTime $createdAt;
    protected \DateTime $updatedAt;

    public function __construct(
        string $nombre,
        string $email,
        ?string $telefono = null,
        string $tipo = ''
    ) {
        $this->nombre = $nombre;
        $this->email = $email;
        $this->telefono = $telefono;
        $this->tipo = $tipo;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getTelefono(): ?string {
        return $this->telefono;
    }

    public function getTipo(): string {
        return $this->tipo;
    }

    public function getCreatedAt(): \DateTime {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime {
        return $this->updatedAt;
    }

    // Setters
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
        $this->actualizarTimestamp();
    }

    public function setEmail(string $email): void {
        $this->email = $email;
        $this->actualizarTimestamp();
    }

    public function setTelefono(?string $telefono): void {
        $this->telefono = $telefono;
        $this->actualizarTimestamp();
    }

    public function setCreatedAt(\DateTime $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void {
        $this->updatedAt = $updatedAt;
    }

    // Métodos de utilidad
    protected function actualizarTimestamp(): void {
        $this->updatedAt = new \DateTime();
    }

    /**
     * Método abstracto que debe ser implementado por las clases hijas
     * para definir sus habilidades específicas
     */
    abstract public function getHabilidades(): array;

    /**
     * Método abstracto para obtener información específica del tipo de participante
     */
    abstract public function getInformacionEspecifica(): array;

    /**
     * Convierte la entidad a array para serialización
     */
    public function toArray(): array {
        return [
            'id' => $this->id ?? null,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'tipo' => $this->tipo,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'habilidades' => $this->getHabilidades(),
            'informacion_especifica' => $this->getInformacionEspecifica()
        ];
    }

    /**
     * Valida que los datos básicos del participante sean correctos
     */
    public function validar(): array {
        $errores = [];

        if (empty($this->nombre)) {
            $errores[] = 'El nombre es obligatorio';
        }

        if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email debe ser válido';
        }

        return $errores;
    }
}
