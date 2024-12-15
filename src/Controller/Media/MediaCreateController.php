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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

#[Route('/file', name: 'media_create', methods: ['POST'])]
class MediaCreateController extends AbstractController
{
    public function __invoke(
        RequestStack $requestStack,
        GEDServiceInterface $gedService,
        SerializerInterface $serializer,
        NormalizerInterface $normalizer,
    ) {
        $inputContext = (new ObjectNormalizerContextBuilder())->withGroups(['write:media']);
        $mediaDTO = $serializer->deserialize($requestStack->getCurrentRequest()->getContent(), MediaDTO::class, 'json', $inputContext->toArray());

        try {
            $outputContext = (new ObjectNormalizerContextBuilder())->withGroups(['read:media']);
            return new JsonResponse($normalizer->normalize($gedService->createMedia($mediaDTO), 'json', $outputContext->toArray()), 201);
        } catch (ValidationFailedException $e) {
            $return = [
                "code" => 422,
                "violations" => $normalizer->normalize($e->getViolations(), 'json')
            ];
            if (isset($mediaDTO->path)) {
                $return["directory"] = $mediaDTO->path;
            }
            return new JsonResponse($return, 422);
        } catch (\Exception $e) {
            if ($e->getMessage() == "Content is required.") {
                $return = [
                    "code" => 422,
                    "violations" => $e->getMessage()
                ];
                if (isset($mediaDTO->path)) {
                    $return["directory"] = $mediaDTO->path;
                }
                return new JsonResponse($return, 422);
            } else {
                throw $e;
            }
        }
    }
}
