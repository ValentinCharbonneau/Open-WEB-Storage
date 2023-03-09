<?php

/**
 * @ Created on 20/02/2023 09:02
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Controller\Group;

use App\DTO\EntityDTO\GroupDTO;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\GEDService\GEDServiceInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

#[Route('/dir', name: 'group_create', methods: ['POST'])]
class GroupCreateController extends AbstractController
{
    public function __invoke(
        GEDServiceInterface $GEDService,
        SerializerInterface $serializer,
        RequestStack $requestStack
    ) {
        $inputContext = (new ObjectNormalizerContextBuilder())->withGroups(['write:group']);
        $groupDTO = $serializer->deserialize($requestStack->getCurrentRequest()->getContent(), GroupDTO::class, 'json', $inputContext->toArray());

        try {
            $outputContext = (new ObjectNormalizerContextBuilder())->withGroups(['read:group']);
            return new JsonResponse($serializer->normalize($GEDService->createGroup($groupDTO), 'json', $outputContext->toArray()), 201);
        } catch (ValidationFailedException $e) {
            $return = [
                "code" => 422,
                "violations" => $serializer->normalize($e->getViolations(), 'json')
            ];
            if (isset($groupDTO->path)) {
                $return["directory"] = $groupDTO->path;
            }
            return new JsonResponse($return, 422);
        }
    }
}
