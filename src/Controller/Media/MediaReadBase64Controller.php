<?php

/**
 * @ Created on 20/02/2023 14:33
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Controller\Media;

use App\DTO\EntityDTO\MediaDTO;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\GEDService\GEDServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

#[Route('/file/base64/{uuid}', name: 'media_read_base64', methods: ['GET'])]
class MediaReadBase64Controller extends AbstractController
{
    public function __invoke(
        GEDServiceInterface $GEDService,
        SerializerInterface $serializer,
        string $uuid
    ) {
        try {
            $content = $GEDService->readBase64Media($uuid);
            $mediaDTO = new MediaDTO();
            $mediaDTO->content = $content;
            $outputContext = (new ObjectNormalizerContextBuilder())->withGroups(['file:media']);
            return new JsonResponse($serializer->normalize($mediaDTO, 'json', $outputContext->toArray()));
        } catch (ResourceNotFoundException $e) {
            return new JsonResponse(["code" => 404, "message" => "Resource '$uuid' not found."], 404);
        }
    }
}
