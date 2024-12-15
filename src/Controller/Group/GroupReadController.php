<?php

/**
 * @ Created on 20/02/2023 09:02
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Controller\Group;

use Symfony\Component\Routing\Annotation\Route;
use App\Services\GEDService\GEDServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

#[Route('/dir/{uuid}', name: 'group_read', methods: ['GET'])]
class GroupReadController extends AbstractController
{
    public function __invoke(
        string $uuid,
        GEDServiceInterface $gedService,
        NormalizerInterface $normalizer,
    ) {
        try {
            $outputContext = (new ObjectNormalizerContextBuilder())->withGroups(['read:group']);
            return new JsonResponse($normalizer->normalize($gedService->readGroup($uuid), 'json', $outputContext->toArray()));
        } catch (ResourceNotFoundException $_) {
            return new JsonResponse(["code" => 404, "message" => "Resource '$uuid' not found."], 404);
        }
    }
}
