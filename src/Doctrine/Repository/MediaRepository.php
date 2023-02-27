<?php

/**
 * @ Created on 10/02/2023 13:34
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Omar Kennouche <topdeveloppement@gmail.com>
 * @ Licence For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Group;
use App\Doctrine\Entity\Media;
use App\DTO\EntityDecrypt\MediaDecrypt;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Encryptor\EncryptorInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Infrastructure\Doctrine\Entity\User\User;

/**
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    private EncryptorInterface $encryptor;

    public function __construct(ManagerRegistry $registry, EncryptorInterface $encryptor)
    {
        parent::__construct($registry, Media::class);

        $this->encryptor = $encryptor;
    }

    /**
     * Load all Media which has the same parent of $media
     */
    public function getSamePath(MediaDecrypt $media): array
    {
        $parameters = [];

        if (isset($media->parent)) {
            $parent = "m.parent = :parentId";
            $parameters[":parentId"] = $media->parent->getUuid();
        } else {
            $parent = "m.parent IS NULL";
        }

        if (isset($media->uuid)) {
            $current = "AND m.uuid <> :mediaUuid";
            $parameters[":mediaUuid"] = $media->uuid;
        } else {
            $current = "";
        }

        if (isset($media->name) && isset($media->type)) {
            $query = $this->createQueryBuilder('m')
                ->where($parent . " AND m.name = :mediaName AND m.type = :mediaType " . $current);
            $parameters[":mediaName"] = $this->encryptor->encryptData($media->name);
            $parameters[":mediaType"] = $this->encryptor->encryptData($media->type);
        } else {
            return [];
        }

        $query->setParameters($parameters);

        return $query->getQuery()->getResult();
    }

    /**
     * Find a Media which have $owner, $name, $type and $parent which can be null
     */
    public function findMedia(User $owner, string $name, string $type, ?Group $parent = null): ?Media
    {
        $whereParent = isset($parent) ? "m.parent = :parent" : "m.parent IS NULL";
        $parentId = $parent?->getUuid();

        $query = $this->createQueryBuilder("m")
            ->where("m.owner = :owner AND m.name = :name AND m.type = :type AND " . $whereParent);

        if (isset($parent)) {
            $query->setParameters([":owner" => $owner->getUuid(), ":name" => $name, ":type" => $type, ":parent" => $parentId]);
        } else {
            $query->setParameters([":owner" => $owner->getUuid(), ":name" => $name, ":type" => $type]);
        }

        $result = $query->getQuery()->getResult();

        if (count($result) > 0) {
            return $result[0];
        } else {
            return null;
        }
    }
}
