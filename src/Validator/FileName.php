<?php

/**
 * @ Created on 10/02/2023 08:57
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Omar Kennouche <topdeveloppement@gmail.com>
 * @ Licence For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class FileName.
 *
 * @author Valentin Charbonneau
 */
#[\Attribute]
class FileName extends Constraint
{
    private string $message;

    public function __construct(?string $message = null, array $groups = null, mixed $payload = null)
    {
        parent::__construct([], $groups, $payload);

        $this->message = $message == null ? "This file already exist." : $message;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }


    public function getMessage(): string
    {
        return $this->message;
    }
}
