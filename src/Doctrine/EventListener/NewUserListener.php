<?php

/**
 * @ Created on 10/02/2023 11:00
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Doctrine\EventListener;

use App\Doctrine\Entity\User;
use App\Services\Encryptor\EncryptorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class NewUserListener.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class NewUserListener
{
    public function __construct(
        private EncryptorInterface $encryptor,
        private ParameterBagInterface $bag
    ) {
    }

    public function postPersist(User $user): void
    {
        $this->encryptor->generateKeyPair($user->getUuid());
        mkdir($this->bag->get("doc_dir") . "/" . $user->getUuid());
        mkdir($this->bag->get("archive_dir") . "/" . $user->getUuid());
    }
}
