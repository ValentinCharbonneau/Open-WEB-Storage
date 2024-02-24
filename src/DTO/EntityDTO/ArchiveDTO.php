<?php

declare(strict_types=1);

namespace App\DTO\EntityDTO;

class ArchiveDTO
{
    public ?string $uuid = null;

    public ?string $path = null;

    public ?array $metadata = null;

    public ?string $content = null;
}
