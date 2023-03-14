<?php

/**
 * @ Created on 28/02/2023 15:32
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Controller\User;

use App\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Security\SecurityServiceInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class DeleteUserController.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class DeleteMeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManagerInterface,
        private ParameterBagInterface $bag,
        private SecurityServiceInterface $security
    ) {
    }

    #[Route(name: 'delete_me', path: '/me', methods: ['DELETE'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser();

        unlink($this->bag->get("key_dir") . "/" . $user->getUuid() . ".key");
        rmdir($this->bag->get("doc_dir") . "/" . $user->getUuid());
        rmdir($this->bag->get("archive_dir") . "/" . $user->getUuid());

        $this->entityManagerInterface->remove($user);
        $this->entityManagerInterface->flush();

        return new JsonResponse([], 204);
    }
}
