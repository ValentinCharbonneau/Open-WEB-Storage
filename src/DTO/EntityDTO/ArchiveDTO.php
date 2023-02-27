<?php

declare(strict_types=1);

namespace App\DTO\EntityDTO;

use Symfony\Component\Serializer\Annotation\Groups;

class ArchiveDTO
{
    #[Groups(['read:archive'])]
    public ?string $uuid = null;

    #[Groups(['read:archive'])]
    public ?string $path = null;

    #[Groups(['read:archive'])]
    public ?array $metadata = null;

    #[Groups(['file:archive'])]
    public ?string $content = null;
}
