<?php

/**
 * @ Created on 08/02/2023 9:20
 * @ This file is part of the NetagriWeb project.
 * @ Contact (c) Omar Kennouche <topdeveloppement@gmail.com>
 * @ Licence For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Services\Encryptor;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class Encryptor.
 *
 * @author Valentin Charbonneau
 */
class Encryptor implements EncryptorInterface
{
    private ?string $user;
    private ?string $key;
    private ?string $iv;
    private string $algo;
    private ParameterBagInterface $bag;

    public function __construct(
        ParameterBagInterface $bag
    ) {
        $this->user = null;
        $this->key = null;
        $this->iv = null;
        $this->algo = "aes-256-ctr";
        $this->bag = $bag;
    }

    /**
     * Generate keys for a user
     */
    public function generateKeyPair(string $uuid, bool $force = false): void
    {
        $this->user = $uuid;
        $this->key = base64_encode(openssl_random_pseudo_bytes(68));
        $this->iv = openssl_random_pseudo_bytes(16);

        $fileContent = "-----BEGIN KEY-----\n" . $this->key . "\n-----END KEY-----\n-----BEGIN VECTOR-----\n" . base64_encode($this->iv) . "\n-----END VECTOR-----";

        $location = $this->bag->get("key_dir") . "/" . $this->user . ".key";

        if (!file_exists($location) || $force) {
            file_put_contents($location, $fileContent);
        } else {
            throw new FileException("Keys of user '". $this->user . "' already exists");
        }
    }

    /**
     * Load keys for a user and store it on attributes
     */
    public function loadKeyPair(string $uuid): void
    {
        if ($this->user != $uuid) {
            $this->user = $uuid;

            $location = $this->bag->get("key_dir") . "/" . $this->user . ".key";

            if (file_exists($location)) {
                $contentFile = file_get_contents($location);
            } else {
                throw new FileNotFoundException("Not found keys of user '". $this->user . "'");
            }

            preg_match('/-----BEGIN KEY-----\R(.*)\R-----END KEY-----/i', $contentFile, $keys);
            preg_match('/-----BEGIN VECTOR-----\R(.*)\R-----END VECTOR-----/i', $contentFile, $ivs);
            $this->key = $keys[1];
            $this->iv = base64_decode($ivs[1]);
        }
    }

    /**
     * Encrypt data
     * Keys must be loaded
     */
    public function encryptData(string $data): string
    {
        if (!empty($this->user)) {
            return openssl_encrypt($data, $this->algo, $this->key, 0, $this->iv);
        } else {
            throw new \Exception("No one user was loaded in Encryptor");
        }
    }

    /**
     * Decrypt data
     * Keys must be loaded
     */
    public function decryptData(string $data): string
    {
        if (!empty($this->user)) {
            return openssl_decrypt($data, $this->algo, $this->key, 0, $this->iv);
        } else {
            throw new \Exception("No one user was loaded in Encryptor");
        }
    }
}
