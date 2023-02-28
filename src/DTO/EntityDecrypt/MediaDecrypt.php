<?php

namespace App\DTO\EntityDecrypt;

use App\Doctrine\Entity\Group;
use App\Validator as GEDValidator;
use App\Doctrine\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

#[GEDValidator\FileName]
class MediaDecrypt
{
    #[Assert\AtLeastOneOf([
        new Assert\Uuid(),
        new Assert\IsNull()
    ])]
    public ?string $uuid = null;

    #[Assert\Length(max: 255, maxMessage: "Max length of name is 255 characters")]
    #[Assert\NotBlank]
    public string $name;

    #[Assert\Length(max: 8, maxMessage: "Max length of type is 8 characters")]
    #[Assert\NotBlank]
    public string $type;

    public User $owner;

    public int $deep;

    #[Assert\AtLeastOneOf([
        new Assert\Json(),
        new Assert\IsNull()
    ])]
    public ?string $metadata = null;

    public ?Group $parent;
}
