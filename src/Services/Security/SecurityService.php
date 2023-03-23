<?php

/**
 * @ Created on 12/03/2023 18:29
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Services\Security;

use App\Doctrine\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SecurityServiceService.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class SecurityService implements SecurityServiceInterface
{
    private ?User $user;

    public function __construct(
        private Security $security,
        private RequestStack $requestStack
    ) {
        $this->user = $this->security->getUser();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        if (empty($this->requestStack->getCurrentRequest()) || empty($this->user)) {
            $this->user = $user;
        }

        return $this;
    }
}
