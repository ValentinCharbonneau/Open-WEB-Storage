<?php

declare(strict_types=1);

namespace App\DTO\EntityDTO;

use Symfony\Component\Serializer\Annotation\Groups;

class MediaDTO
{
    #[Groups(['read:media'])]
    public ?string $uuid = null;

    #[Groups(['read:media', 'write:media'])]
    public ?string $path = null;

    #[Groups(['read:media', 'write:media'])]
    public ?array $metadata = null;

    #[Groups(['file:media', 'write:media'])]
    public ?string $content = null;
}
