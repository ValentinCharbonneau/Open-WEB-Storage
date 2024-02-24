<?php

/**
 * @ Created on 28/02/2023 10:00
 * @ Updated on 24/02/2024 19:04
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\DTO\Security;

/**
 * Class Authenticator.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class Authenticator
{
    private ?string $token;

    private ?int $expire;

    private ?string $email;

    private ?string $password;

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getExpire(): ?int
    {
        return $this->expire;
    }

    public function setExpire(?int $expire): self
    {
        $this->expire = $expire;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
}
