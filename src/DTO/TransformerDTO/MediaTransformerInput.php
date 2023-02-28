<?php

/**
 * @ Created on 10/02/2023 15:00
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\DTO\TransformerDTO;

use App\DTO\EntityDecrypt\MediaDecrypt;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Class MediaTransformerInput.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class MediaTransformerInput
{
    public function __construct(
        private Security $security,
        private SluggerInterface $slugger,
    ) {
    }

    public function transform($object, $parents, $parent)
    {
        $media = new MediaDecrypt();

        $fileName = $parents[count($parents) - 1];
        if (isset($fileName)) {
            $fileName = explode(".", $fileName);
            $media->parent = $parent;
            $media->name = strval($this->slugger->slug($fileName[0]));
        } else {
            $fileName = [null];
        }

        if (count($fileName) == 2) {
            $media->type = strval($this->slugger->slug($fileName[count($fileName) - 1]));
        }
        $media->owner = $this->security->getUser();
        $media->metadata = json_encode($object->metadata);
        $media->deep = count($parents) - 1;

        return $media;
    }
}
