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

use App\DTO\EntityDTO\ArchiveDTO;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\GEDService\GEDServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

#[Route('/%base_ged_version%/archive/base64/{uuid}', name: 'archive_read_base64', methods: ['GET'])]
class ArchiveReadbase64Controller extends AbstractController
{
    public function __invoke(
        GEDServiceInterface $GEDService,
        SerializerInterface $serializer,
        string $uuid
    ) {
        try {
            $content = $GEDService->readBase64Archive($uuid);
            $archiveDTO = new ArchiveDTO();
            $archiveDTO->content = $content;
            $outputContext = (new ObjectNormalizerContextBuilder())->withGroups(['file:archive']);
            return new JsonResponse($serializer->normalize($archiveDTO, 'json', $outputContext->toArray()));
        } catch (ResourceNotFoundException $e) {
            return new JsonResponse(["code" => 404, "message" => "Resource '$uuid' not found."], 404);
        }
    }
}
