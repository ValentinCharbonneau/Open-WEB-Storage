<?php

/**
 * @ Created on 28/02/2023 10:00
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

 declare(strict_types=1);

namespace App\Controller\Security;

use App\Doctrine\Entity\User;
use App\DTO\Security\Authenticator;
use App\Doctrine\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use App\Services\UserJwtGenerator\UserJwtGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AuthenticatorRefreshJwtController.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class AuthenticatorRefreshJwtController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private SerializerInterface $serializerInterface,
        private JWTTokenManagerInterface  $jWTTokenManagerInterface,
        private TokenStorageInterface $tokenStorageInterface,
        private UserJwtGeneratorInterface $userJwtGeneratorInterface
    ) {
    }

    #[Route(name: 'refresh-token', path: '/authenticator/jwt-refresh', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $decodedJwtToken = $this->jWTTokenManagerInterface->decode($this->tokenStorageInterface->getToken());

        $user = $this->userRepository->findOneBy(["email" => $decodedJwtToken["username"]]);

        $this->userJwtGeneratorInterface->generate($user);

        $authenticator = new Authenticator();
        $authenticator->setToken($this->userJwtGeneratorInterface->getToken());
        $authenticator->setExpire($this->userJwtGeneratorInterface->getExpire());

        $contextBuilder = (new ObjectNormalizerContextBuilder())->withGroups('auth:jwt')->toArray();

        return new JsonResponse($this->serializerInterface->normalize($authenticator, 'json', $contextBuilder));
    }
}
