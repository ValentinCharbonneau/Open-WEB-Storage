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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

/**
 * Class GetAllUserController.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class GetAllUserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManagerInterface,
        private UserRepository $userRepository,
        private SerializerInterface $serializerInterface,
        private RequestStack $requestStack,
        private UserPasswordHasherInterface $passwordHasher,
        private UserValidatorInterface $userValidatorInterface,
        private Security $security,
        private ParameterBagInterface $bag
    ) {
    }

    #[Route(name: 'admin-readall-user', path: '/users', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        if (!in_array("ROLE_ADMIN", $this->security->getUser()->getRoles())) {
            return new JsonResponse(["code" => 403, "message" => "Unauthorized action"], 403);
        }

        if (!empty($this->requestStack->getCurrentRequest()->query->get("page"))) {
            try {
                $page = intval($this->requestStack->getCurrentRequest()->query->get("page"));
                if ($page < 1) {
                    $page = 1;
                }
            } catch (\Exception $e) {
                $page = 1;
            }
        } else {
            $page = 1;
        }

        $users = $this->userRepository->findBy([],
                        limit: $this->bag->get("ged_pagination"),
                        offset: ($page - 1) * $this->bag->get("ged_pagination")
                    );

        $contextBuilder = (new ObjectNormalizerContextBuilder())->withGroups('admin:read:user')->toArray();
        foreach ($users as &$user) {
            $user = $this->serializerInterface->normalize($user, 'json', $contextBuilder);
        }

        return new JsonResponse($users);
    }
}
