<?php

/**
 * @ Created on 08/02/2023 15:00
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\DTO\TransformerDTO;

use App\DTO\EntityDTO\GroupDTO;
use App\Services\Encryptor\Encryptor;
use App\Doctrine\Repository\GroupRepository;

/**
 * Class GroupTransformerOutput.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class GroupTransformerOutput
{
    public function __construct(
        private Encryptor $encryptor,
        private GroupRepository $groupRepository
    ) {
    }

    public function transform($object): GroupDTO
    {
        $dto = new GroupDTO();
        $dto->uuid = $object->uuid;

        $parents = $this->groupRepository->getPath($object->uuid, $object->deep);
        $path = "/";
        foreach ($parents as $parent) {
            $path .= $this->encryptor->decryptData($parent->getName()) . "/";
        }

        $dto->path = $path;

        return $dto;
    }
}
