<?php

/**
 * @ Created on 14/02/2023 11:22
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Services\FileSystem;

use App\Doctrine\Entity\Group;
use App\Doctrine\Entity\Media;
use App\DTO\EntityDTO\GroupDTO;
use App\DTO\EntityDTO\MediaDTO;
use App\Doctrine\Entity\Archive;
use App\DTO\EntityDTO\ArchiveDTO;
use App\DTO\EntityDecrypt\GroupDecrypt;
use App\DTO\EntityDecrypt\MediaDecrypt;
use App\DTO\EntityDecrypt\ArchiveDecrypt;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Interface FileSystemInterface.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
interface FileSystemInterface
{
    /**
     * Clean $path, remove multiple '/' and '\' and replace '\' to '/'
     *
     * @return string
     */
    function cleanPath(string $path): string;

    /**
     * Explode $path and return an array contains name of all elements in $path
     *
     * @return array
     */
    function explodePath(string $path): array;

    /**
     * Load or create all elements il $path, except the last, we create or load only parents
     *
     * @return array
     */
    function buildParents(string $path): ?Group;

    /**
     * Return entity correspond to $uuid.
     * $className specify the entity we need to laod, so must be Media::class or Group::class or Archive::class
     *
     * @return array
     */
    function get(string $uuid, string $className): null|Media|Group|Archive;

    /**
     * Use validator to return violations of $entity
     *
     * @param MediaDecrypt|GroupDecrypt|ArchiveDecrypt $entity
     * @return ConstraintViolationListInterface
     */
    function valid(MediaDecrypt|GroupDecrypt|ArchiveDecrypt $entity): ConstraintViolationListInterface;

    /**
     * Transform a Decrypt entity to an Entity
     * Transform MediaDecrypt to Media
     * Transform GroupDecrypt to Group
     * Transform ArchiveDecrypt to Archive
     *
     * @param MediaDecrypt|GroupDecrypt|ArchiveDecrypt $decryptEntity
     * @return Media|Group|Archive
     */
    function encrypt(MediaDecrypt|GroupDecrypt|ArchiveDecrypt $decryptEntity): Media|Group|Archive;

    /**
     * Transform an ecrypt entity to a decrypt entity
     * Transform Media to MediaDecrypt
     * Transform Group to GroupDecrypt
     * Transform Archive to ArchiveDecrypt
     *
     * @param Media|Group|Archive $encryptEntity
     * @return MediaDecrypt|GroupDecrypt|ArchiveDecrypt
     */
    function decrypt(Media|Group|Archive $encryptEntity): MediaDecrypt|GroupDecrypt|ArchiveDecrypt;

    /**
     * Transform a DTO to an Entity directly, so decrypt or encrypt and transform
     * Transform Media to MediaDTO or MediaDTO to Media
     * Transform Group to GroupDTO or GroupDTO to Group
     * Transform Archive to ArchiveTO or ArchiveDTO to Archive
     *
     * @param Media|MediaDTO|Group|GroupDTO|Archive|ArchiveDTO $entity
     * @return Media|MediaDTO|Group|GroupDTO|Archive|ArchiveDTO
     */
    function fullTransform(Media|MediaDTO|Group|GroupDTO|Archive|ArchiveDTO $entity): Media|MediaDTO|Group|GroupDTO|Archive|ArchiveDTO;

    /**
     * Use transformers classes to transform DTO to Decrypt Entity
     * Transform MediaDTO to DecryptMedia or DecryptMedia to MediaDTO
     * Transform GroupDTO to DecryptGroup or DecryptGroup to GroupDTO
     * Transform ArchiveDTO to DecryptArchive or DecryptArchive to ArchiveDTO
     *
     * @param MediaDecrypt|MediaDTO|GroupDecrypt|GroupDTO|ArchiveDecrypt|ArchiveDTO $entity
     * @return MediaDecrypt|MediaDTO|GroupDecrypt|GroupDTO|ArchiveDecrypt|ArchiveDTO
     */
    function transform(MediaDecrypt|MediaDTO|GroupDecrypt|GroupDTO|ArchiveDecrypt|ArchiveDTO $entity): MediaDecrypt|MediaDTO|GroupDecrypt|GroupDTO|ArchiveDecrypt|ArchiveDTO;

    /**
     * Save $entity and $file
     *
     * @param Media|Group|Archive $entity
     * @param string|null $file
     */
    function save(Media|Group|Archive &$entity, ?string $file = null): void;

    /**
     * Update $entity and $file
     *
     * @param Media|Group|Archive $entity
     * @param string|null $file
     */
    function update(Media|Group|Archive &$entity, ?string $file = null): void;

    /**
     * Remove $entity and its file if it exists
     *
     * @param Media|Group|Archive $entity
     */
    function remove(Media|Group|Archive $entity): void;

    /**
     * Explore file system and return content at the $path emplacement
     *
     * @param string|null $path
     * @return array
     */
    function explore(?string $path = null): array;
}
