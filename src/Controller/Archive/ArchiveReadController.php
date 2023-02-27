<?php

/**
 * @ Created on 21/02/2023 09:43
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Omar Kennouche <topdeveloppement@gmail.com>
 * @ Licence For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Controller\Archive;

use Symfony\Component\Routing\Annotation\Route;
use App\Services\GEDService\GEDServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

#[Route('/%base_ged_version%/archive/{uuid}', name: 'archive_read', methods: ['GET'])]
class ArchiveReadController extends AbstractController
{
    public function __invoke(
        GEDServiceInterface $GEDService,
        SerializerInterface $serializer,
        string $uuid
    ) {
        try {
            $outputContext = (new ObjectNormalizerContextBuilder())->withGroups(['read:archive']);
            return new JsonResponse($serializer->normalize($GEDService->readArchive($uuid), 'json', $outputContext->toArray()));
        } catch (ResourceNotFoundException $e) {
            return new JsonResponse(["code" => 404, "message" => "Resource '$uuid' not found."], 404);
        }
    }
}
