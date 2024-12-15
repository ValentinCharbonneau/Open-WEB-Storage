<?php

namespace App\Services\Security;

use App\Doctrine\Entity\User;

/**
 * Interface SecurityInterface.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
interface SecurityServiceInterface
{
    /**
     * Return the authenticated user or null if not authenticated.
     *
     * @return User|null
     */
    function getUser(): ?User;

    /**
     * Set the authenticated user.
     *
     * @param User $user
     *
     * @return self
     */
    function setUser(User $user): self;
}
