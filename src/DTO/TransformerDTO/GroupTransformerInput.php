<?php

/**
 * @ Created on 08/02/2023 15:00
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\DTO\TransformerDTO;

use App\DTO\EntityDecrypt\GroupDecrypt;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Class GroupTransformerInput.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class GroupTransformerInput
{
    public function __construct(
        private Security $security,
        private SluggerInterface $slugger
    ) {
    }

    public function transform($object, $parents, $parent): GroupDecrypt
    {
        $group = new GroupDecrypt();

        $group->parent = $parent;
        $group->deep = count($parents) - 1;
        if (isset($parents[count($parents) - 1])) {
            $group->name = strval($this->slugger->slug($parents[count($parents) - 1]));
        }
        $group->owner = $this->security->getUser();

        return $group;
    }
}
