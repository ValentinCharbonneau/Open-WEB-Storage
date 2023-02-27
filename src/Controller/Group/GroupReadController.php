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
use App\Services\GEDService\GEDServiceInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\FileSystem\FileSystemInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

#[Route('/%base_ged_version%/dir/{uuid}', name: 'group_read', methods: ['GET'])]
class GroupReadController extends AbstractController
{
    public function __invoke(
        GEDServiceInterface $GEDService,
        FileSystemInterface $fileSystem,
        SerializerInterface $serializer,
        string $uuid
    ) {
        try {
            $outputContext = (new ObjectNormalizerContextBuilder())->withGroups(['read:group']);
            return new JsonResponse($serializer->normalize($GEDService->readGroup($uuid), 'json', $outputContext->toArray()));
        } catch (ResourceNotFoundException $e) {
            return new JsonResponse(["code" => 404, "message" => "Resource '$uuid' not found."], 404);
        }
    }
}
