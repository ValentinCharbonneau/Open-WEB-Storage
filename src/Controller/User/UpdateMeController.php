<?php

/**
 * @ Created on 28/02/2023 10:00
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Controller\User;

use Doctrine\ORM\EntityManagerInterface;
use App\Doctrine\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Services\Security\SecurityServiceInterface;
use App\Services\UserValidator\UserValidatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

/**
 * Class UpdateMeController.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class UpdateMeController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack,
        private UserRepository $userRepository,
        private SecurityServiceInterface $security,
        private NormalizerInterface $normalizerInterface,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManagerInterface,
        private UserValidatorInterface $userValidatorInterface,
    ) {
    }

    #[Route(name: 'update_me', path: '/me', methods: ['PUT'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser();
        $requestContent = json_decode($this->requestStack->getCurrentRequest()->getContent(), true);

        if (array_key_exists("email", $requestContent)) {
            $user->setEmail($requestContent["email"]);
        }
        if (array_key_exists("password", $requestContent)) {
            $user->setPlainPassword($requestContent["password"]);
            $user->setPassword($this->passwordHasher->hashPassword($user, $requestContent["password"]));
        } else {
            $user->setPlainPassword("P@ss0rd_9");
        }

        $this->userValidatorInterface->validate($user);

        if ($this->userValidatorInterface->isViolating()) {
            $result = ["code" => 422, "message" => $this->userValidatorInterface->getViolations()];
            return new JsonResponse($result, 422);
        }

        $this->entityManagerInterface->flush();

        $contextBuilder = (new ObjectNormalizerContextBuilder())->withGroups('read:user')->toArray();

        return new JsonResponse($this->normalizerInterface->normalize($user, 'json', $contextBuilder));
    }
}
