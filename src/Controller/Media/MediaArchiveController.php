<?php

/**
 * @ Created on 20/02/2023 14:33
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Controller\Media;

use Symfony\Component\Routing\Annotation\Route;
use App\Services\GEDService\GEDServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

#[Route('/file/archive/{uuid}', name: 'media_archive', methods: ['POST'])]
class MediaArchiveController extends AbstractController
{
    public function __invoke(
        GEDServiceInterface $GEDService,
        SerializerInterface $serializer,
        string $uuid
    ) {
        try {
            $outputContext = (new ObjectNormalizerContextBuilder())->withGroups(['read:archive']);
            return new JsonResponse($serializer->normalize($GEDService->archiveMedia($uuid), 'json', $outputContext->toArray()));
        } catch (ResourceNotFoundException $e) {
            return new JsonResponse(["code" => 404, "message" => "Resource '$uuid' not found."], 404);
        }
    }
}
