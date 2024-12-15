<?php

/**
 * @ Created on 28/02/2023 10:00
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Controller\User;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Services\Security\SecurityServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

/**
 * Class GetMeController.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class GetMeController extends AbstractController
{
    public function __construct(
        private SecurityServiceInterface $security,
        private NormalizerInterface $normalizerInterface,
    ) {
    }

    #[Route(name: 'get_me', path: '/me', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser();

        $contextBuilder = (new ObjectNormalizerContextBuilder())->withGroups('read:user')->toArray();

        return new JsonResponse($this->normalizerInterface->normalize($user, 'json', $contextBuilder));
    }
}
