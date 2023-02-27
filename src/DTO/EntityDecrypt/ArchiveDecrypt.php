<?php

namespace App\DTO\EntityDecrypt;

use Infrastructure\Doctrine\Entity\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class ArchiveDecrypt
{
    #[Assert\AtLeastOneOf([
        new Assert\Uuid(),
        new Assert\IsNull()
    ])]
    public ?string $uuid;

    #[Assert\NotBlank]
    public string $path;

    #[Assert\AtLeastOneOf([
        new Assert\Json(),
        new Assert\IsNull()
    ])]
    public ?string $metadata = null;

    public User $owner;
}
