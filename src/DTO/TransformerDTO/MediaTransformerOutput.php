<?php

/**
 * @ Created on 10/02/2023 15:00
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\DTO\TransformerDTO;

use App\DTO\EntityDTO\MediaDTO;
use App\Services\Encryptor\Encryptor;
use App\Doctrine\Repository\GroupRepository;

/**
 * Class MediaTransformerOutput.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
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
