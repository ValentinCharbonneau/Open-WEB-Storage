<?php

/**
 * @ Created on 28/02/2023 10:00
 * @ This file is part of the Open WEB Storage project.
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
 * Class UpdateUserController.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class UpdateUserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManagerInterface,
        private UserRepository $userRepository,
        private SerializerInterface $serializerInterface,
        private RequestStack $requestStack,
        private Security $security,
        private UserPasswordHasherInterface $passwordHasher,
        private UserValidatorInterface $userValidatorInterface,
    ) {
    }

    #[Route(name: 'admin_update_user', path: '/users/{email}', methods: ['PUT'])]
    public function __invoke(string $email): JsonResponse
    {
        if (!in_array("ROLE_ADMIN", $this->security->getUser()->getRoles())) {
            return new JsonResponse(["code" => 403, "message" => "Unauthorized action"], 403);
        }

        $user = $this->userRepository->findBy(["email" => $email]);

        if (!count($user)) {
            return new JsonResponse(["code" => 404, "message" => "Not found user '$email'"], 404);
        }

        $user = $user[0];

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
        if (array_key_exists("roles", $requestContent)) {
            $user->setRoles($requestContent["roles"]);
        }

        $this->userValidatorInterface->validate($user);

        if ($this->userValidatorInterface->isViolating()) {
            $result = ["code" => 422, "message" => $this->userValidatorInterface->getViolations()];
            return new JsonResponse($result, 422);
        }

        $this->entityManagerInterface->flush();

        $contextBuilder = (new ObjectNormalizerContextBuilder())->withGroups('admin:read:user')->toArray();

        return new JsonResponse($this->serializerInterface->normalize($user, 'json', $contextBuilder), 201);
    }
}