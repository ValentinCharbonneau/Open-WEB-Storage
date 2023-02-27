<?php

/**
 * @ Created on 10/02/2023 11:00
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Omar Kennouche <topdeveloppement@gmail.com>
 * @ Licence For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Doctrine\EventListener;

use Infrastructure\Doctrine\Entity\User\User;
use App\Services\Encryptor\EncryptorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class RemoveGroupListener.
 *
 * @author Valentin Charbonneau
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
