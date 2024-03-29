<?php

/**
 * @ Created on 10/02/2023 08:57
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Validator;

use App\Doctrine\Entity\Group;
use App\Doctrine\Entity\Media;
use App\DTO\EntityDecrypt\GroupDecrypt;
use App\DTO\EntityDecrypt\MediaDecrypt;
use Symfony\Component\Validator\Constraint;
use App\Doctrine\Repository\GroupRepository;
use App\Doctrine\Repository\MediaRepository;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class FileNameValidator.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class FileNameValidator extends ConstraintValidator
{
    public function __construct(
        private GroupRepository $groupRepository,
        private MediaRepository $mediaRepository
    ) {
    }

    /**
     * Verify if it doesn't exist another file or folder with the same path
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof FileName) {
            throw new UnexpectedTypeException($constraint, FileName::class);
        }
        if (!$value instanceof GroupDecrypt && !$value instanceof MediaDecrypt) {
            throw new UnexpectedTypeException($value, Group::class);
        }

        if ($value instanceof GroupDecrypt) {
            if (count($this->groupRepository->getSamePath($value))) {
                $this->context->buildViolation($constraint->getMessage())
                    ->atPath("path")
                    ->addViolation();
            }
        } elseif ($value instanceof MediaDecrypt) {
            if (count($this->mediaRepository->getSamePath($value))) {
                $this->context->buildViolation($constraint->getMessage())
                    ->atPath("path")
                    ->addViolation();
            }
        }
    }
}
