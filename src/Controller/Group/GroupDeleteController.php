<?php

/**
 * @ Created on 20/02/2023 09:02
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Omar Kennouche <topdeveloppement@gmail.com>
 * @ Licence For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Controller\Group;

use App\Doctrine\Entity\Group;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\FileSystem\FileSystemInterface;
use App\Services\GEDService\GEDServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

#[Route('/%base_ged_version%/dir/{uuid}', name: 'group_delete', methods: ['DELETE'])]
class GroupDeleteController extends AbstractController
{
    public function __invoke(
        GEDServiceInterface $GEDService,
        FileSystemInterface $fileSystem,
        string $uuid
    ) {
        try {
            $GEDService->deleteGroup($uuid);
            return new JsonResponse(["code" => 204, "message" => "Resource was removed"], 204);
        } catch (ResourceNotFoundException $e) {
            return new JsonResponse(["code" => 404, "message" => "Resource '$uuid' not found."], 404);
        } catch (\Exception $e) {
            if ($e->getMessage() == "Directory must be empty to be removed.") {
                return new JsonResponse(["code" => 422,
                    "directories" => $fileSystem->fullTransform($fileSystem->get($uuid, Group::class))->path,
                    "message" => $e->getMessage()], 422);
            } else {
                throw $e;
            }
        }
    }
}
