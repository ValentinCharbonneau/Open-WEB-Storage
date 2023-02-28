<?php

declare(strict_types=1);

namespace App\DTO\EntityDTO;

use Symfony\Component\Serializer\Annotation\Groups;

class GroupDTO
{
    public ?string $uuid = null;

    public ?string $path = null;
}
