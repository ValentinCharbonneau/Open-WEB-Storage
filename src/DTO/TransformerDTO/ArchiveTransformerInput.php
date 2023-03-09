<?php

/**
 * @ Created on 21/02/2023 09:04
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\DTO\TransformerDTO;

use App\DTO\EntityDecrypt\ArchiveDecrypt;

/**
 * Class ArchiveTransformerInput.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class ArchiveTransformerInput
{
    public function transform($object)
    {
        $archive = new ArchiveDecrypt();
        $archive->path = $object->path;
        $archive->metadata = json_encode($object->metadata);

        return $archive;
    }
}
