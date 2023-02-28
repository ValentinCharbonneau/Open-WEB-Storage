<?php

declare(strict_types=1);

namespace App\DTO\EntityDTO;

use Symfony\Component\Serializer\Annotation\Groups;

class ArchiveDTO
{
    public ?string $uuid = null;

    public ?string $path = null;

    public ?array $metadata = null;

    public ?string $content = null;
}
