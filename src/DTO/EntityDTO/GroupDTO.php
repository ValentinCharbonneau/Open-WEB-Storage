<?php

declare(strict_types=1);

namespace App\DTO\EntityDTO;

use Symfony\Component\Serializer\Annotation\Groups;

class GroupDTO
{
    #[Groups(['read:group'])]
    public ?string $uuid = null;

    #[Groups(['read:group', 'write:group'])]
    public ?string $path = null;
}
