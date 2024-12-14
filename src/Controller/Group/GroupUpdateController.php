<?php

/**
 * @ Created on 20/02/2023 09:02
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Controller\Group;

use App\Doctrine\Entity\Group;
use App\DTO\EntityDTO\GroupDTO;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\FileSystem\FileSystemInterface;
use App\Services\GEDService\GEDServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/dir/{uuid}', name: 'group_update', methods: ['PUT'])]
class GroupUpdateController extends AbstractController
{
    public function __invoke(
        string $uuid,
        RequestStack $requestStack,
        GEDServiceInterface $gedService,
        FileSystemInterface $fileSystem,
        SerializerInterface $serializer,
        NormalizerInterface $normalizer,
    ) {
        try {
            $outputContext = (new ObjectNormalizerContextBuilder())->withGroups(['read:group']);
            $group = $serializer->deserialize($requestStack->getCurrentRequest()->getContent(), GroupDTO::class, 'json');
            $group->uuid = $uuid;
            return new JsonResponse($normalizer->normalize($gedService->updateGroup($group), 'json', $outputContext->toArray()), 201);
        } catch (ValidationFailedException $e) {
            /**
             * @var Group $group
             */
            $group = $fileSystem->get($uuid, Group::class);
            return new JsonResponse([
                "code" => 422,
                "directory" => "/" . $fileSystem->fullTransform($group)->path,
                "violations" => $normalizer->normalize($e->getViolations(), 'json')
            ], 422);
        } catch (ResourceNotFoundException $e) {
            return new JsonResponse(["code" => 404, "message" => "Resource '$uuid' not found."], 404);
        }
    }
}
