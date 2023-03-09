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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

#[Route('/dir/', name: 'group_read_all', methods: ['GET'])]
class GroupReadAllController extends AbstractController
{
    public function __invoke(
        GEDServiceInterface $GEDService,
        SerializerInterface $serializer,
        RequestStack $requestStack
    ) {
        if (!empty($requestStack->getCurrentRequest()->query->get("page"))) {
            try {
                $page = intval($requestStack->getCurrentRequest()->query->get("page"));
                if ($page < 1) {
                    $page = 1;
                }
            } catch (\Exception $e) {
                $page = 1;
            }
        } else {
            $page = 1;
        }

        $outputContext = (new ObjectNormalizerContextBuilder())->withGroups(['read:group']);
        $result = $GEDService->readAllGroup($page);
        foreach ($result as &$group) {
            $group = $serializer->normalize($group, 'json', $outputContext->toArray());
        }

        return new JsonResponse($result);
    }
}
