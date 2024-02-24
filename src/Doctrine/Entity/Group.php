<?php

/**
 * @ Created on 08/02/2023 13:37
 * @ Updated on 24/02/2024 19:02
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Doctrine\Repository\GroupRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * Class Group.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
#[
    ORM\Entity(repositoryClass: GroupRepository::class),
    ORM\Table(name: "ged_group"),
]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $uuid;

    #[ORM\Column(type: Types::STRING, length: 344)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "owner_id", referencedColumnName: "uuid", nullable: false)]
    private User $owner;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: "parent_id", referencedColumnName: "uuid")]
    private ?Group $parent;

    #[ORM\Column(type: Types::INTEGER, options: ["unsigned" => true])]
    private int $deep;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $updatedAt;

    public function __construct()
    {
        $this->uuid = null;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(user $owner): self
    {
        if (empty($this->owner)) {
            $this->owner = $owner;
        }

        return $this;
    }

    public function getParent(): ?Group
    {
        return $this->parent;
    }

    public function setParent(?Group $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getDeep(): int
    {
        return $this->deep;
    }

    public function setDeep(int $deep): self
    {
        $this->deep = $deep;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
