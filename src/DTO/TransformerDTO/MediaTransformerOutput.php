<?php

/**
 * @ Created on 10/02/2023 15:00
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Omar Kennouche <topdeveloppement@gmail.com>
 * @ Licence For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\DTO\TransformerDTO;

use App\DTO\EntityDTO\MediaDTO;
use App\Services\Encryptor\Encryptor;
use App\Doctrine\Repository\GroupRepository;

/**
 * Class MediaTransformerOutput.
 *
 * @author Valentin Charbonneau
 */
class MediaTransformerOutput
{
    public function __construct(
        private Encryptor $encryptor,
        private GroupRepository $groupRepository
    ) {
    }

    public function transform($object)
    {
        $dto = new MediaDTO();
        $dto->uuid = $object->uuid;

        $dto->metadata = json_decode($object->metadata, true);

        $parents = empty($object->parent) ? null : $this->groupRepository->getPath($object->parent->getUuid(), $object->parent->getDeep());
        $path = "/";
        if (!empty($parents)) {
            foreach ($parents as $parent) {
                $path .= $this->encryptor->decryptData($parent->getName()) . "/";
            }
        }
        $path .= $object->name . "." . $object->type;

        $dto->path = $path;

        return $dto;
    }
}
