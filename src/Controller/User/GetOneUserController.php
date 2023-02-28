<?php

/**
 * @ Created on 28/02/2023 13:46
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

 declare(strict_types=1);

namespace App\Controller\User;

use App\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Doctrine\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use App\Services\UserValidator\UserValidatorInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

/**
 * Class GetOneUserController.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class GetOneUserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManagerInterface,
        private UserRepository $userRepository,
        private SerializerInterface $serializerInterface,
        private RequestStack $requestStack,
        private UserPasswordHasherInterface $passwordHasher,
        private UserValidatorInterface $userValidatorInterface,
        private Security $security
    ) {
    }

    #[Route(name: 'admin-readone-user', path: '/users/{email}', methods: ['GET'])]
    public function __invoke(string $email): JsonResponse
    {
        if (!in_array("ROLE_ADMIN", $this->security->getUser()->getRoles())) {
            return new JsonResponse(["code" => 403, "message" => "Unauthorized action"], 403);
        }

        $user = $this->userRepository->findOneBy(["email" => $email]);

        if (empty($user)) {
            return new JsonResponse(["code" => 404, "message" => "Not found user '$email'"], 404);
        }

        $contextBuilder = (new ObjectNormalizerContextBuilder())->withGroups('admin:read:user')->toArray();

        return new JsonResponse($this->serializerInterface->normalize($user, 'json', $contextBuilder));
    }
}