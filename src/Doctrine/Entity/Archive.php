<?php

/**
 * @ Created on 14/02/2023 11:33
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Doctrine\Repository\ArchiveRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * Class Archive.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
#[
    ORM\Entity(repositoryClass: ArchiveRepository::class),
    ORM\Table(name: "ged_archive"),
]
class Archive
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $uuid;

    #[ORM\Column(type: Types::TEXT)]
    private string $path;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private string $metadata;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "owner_id", referencedColumnName: "uuid", nullable: false)]
    private User $owner;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $deletedAt;

    public function __construct(string $path, string $metadata, User $owner)
    {
        $this->deletedAt = new \DateTimeImmutable();

        $this->path = $path;
        $this->metadata = $metadata;
        $this->owner = $owner;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getMetadata(): string
    {
        return $this->metadata;
    }

    public function setMetadata(string $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getDeletedAt(): \DateTimeImmutable
    {
        return $this->deletedAt;
    }
}
