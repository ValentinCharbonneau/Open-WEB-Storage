<?php

/**
 * @ Created on 20/02/2023 14:33
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Omar Kennouche <topdeveloppement@gmail.com>
 * @ Licence For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Controller\Media;

use App\Doctrine\Entity\Group;
use App\DTO\EntityDTO\MediaDTO;
use App\Services\GEDService\GEDServiceInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\FileSystem\FileSystemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[Route('/%base_ged_version%/file', name: 'media_create', methods: ['POST'])]
class MediaCreateController extends AbstractController
{
    public function __invoke(
        GEDServiceInterface $GEDService,
        FileSystemInterface $fileSystem,
        SerializerInterface $serializer,
        RequestStack $requestStack
    ) {
        $inputContext = (new ObjectNormalizerContextBuilder())->withGroups(['write:media']);
        $mediaDTO = $serializer->deserialize($requestStack->getCurrentRequest()->getContent(), MediaDTO::class, 'json', $inputContext->toArray());

        try {
            $outputContext = (new ObjectNormalizerContextBuilder())->withGroups(['read:media']);
            return new JsonResponse($serializer->normalize($GEDService->createMedia($mediaDTO), 'json', $outputContext->toArray()));
        } catch (ValidationFailedException $e) {
            $return = [
                "code" => 422,
                "violations" => $serializer->normalize($e->getViolations(), 'json')
            ];
            if (isset($groupDTO->path)) {
                $return["directory"] = $groupDTO->path;
            }
            return new JsonResponse($return, 422);
        } catch (\Exception $e) {
            if ($e->getMessage() == "Content is required.") {
                $return = [
                    "code" => 422,
                    "violations" => $e->getMessage()
                ];
                if (isset($groupDTO->path)) {
                    $return["directory"] = $groupDTO->path;
                }
                return new JsonResponse($return, 422);
            } else {
                throw $e;
            }
        }
    }
}
