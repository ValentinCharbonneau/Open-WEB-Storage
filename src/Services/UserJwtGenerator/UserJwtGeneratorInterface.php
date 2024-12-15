<?php

/**
 * @ Created on 28/02/2023 10:00
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Services\UserJwtGenerator;

use App\Doctrine\Entity\User;

/**
 * Interface UserJwtGeneratorInterface.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
interface UserJwtGeneratorInterface
{
    /**
     * Generate a bearer token for the user
     *
     * @param User $user
     * @return void
     */
    function generate(User $user): void;

    /**
     * Return the token
     *
     * @return string|null
     */
    function getToken(): ?string;

    /**
     * Return the expiration time
     *
     * @return int|null
     */
    function getExpire(): ?int;
}
