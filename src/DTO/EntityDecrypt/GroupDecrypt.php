<?php

namespace App\DTO\EntityDecrypt;

use App\Doctrine\Entity\Group;
use App\Validator as GEDValidator;
use App\Doctrine\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

#[GEDValidator\FileName]
class GroupDecrypt
{
    #[Assert\AtLeastOneOf([
        new Assert\Uuid(),
        new Assert\IsNull()
    ])]
    public ?string $uuid = null;

    #[Assert\Length(max: 255, maxMessage: "Max length of name is 255 characters")]
    #[Assert\NotBlank]
    public string $name;

    public User $owner;

    public int $deep;

    public ?Group $parent;
}
