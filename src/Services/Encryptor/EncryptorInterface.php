<?php

/**
 * @ Created on 08/02/2023 9:20
 * @ This file is part of the NetagriWeb project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Services\Encryptor;

/**
 * Interface EncryptorInterface.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
interface EncryptorInterface
{
    /**
     * Generate keys for a user
     */
    function generateKeyPair(string $uuid, bool $force = false): void;

    /**
     * Load keys for a user and store it on attributes
     */
    function loadKeyPair(string $uuid): void;

    /**
     * Encrypt data
     * Keys must be loaded
     *
     * @throws \Exception
     */
    function encryptData(string $data): string;

    /**
     * Decrypt data
     * Keys must be loaded
     *
     * @throws \Exception
     */
    function decryptData(string $data): string;
}
