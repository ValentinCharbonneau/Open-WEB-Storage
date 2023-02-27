<?php

/**
 * @ Created on 21/02/2023 09:04
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Omar Kennouche <topdeveloppement@gmail.com>
 * @ Licence For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\DTO\TransformerDTO;

use App\DTO\EntityDTO\ArchiveDTO;

/**
 * Class ArchiveTransformerOutput.
 *
 * @author Valentin Charbonneau
 */
class ArchiveTransformerOutput
{
    public function transform($object)
    {
        $dto = new ArchiveDTO();
        $dto->uuid = $object->uuid;
        $dto->path = $object->path;
        $dto->metadata = json_decode($object->metadata, true);

        return $dto;
    }
}
