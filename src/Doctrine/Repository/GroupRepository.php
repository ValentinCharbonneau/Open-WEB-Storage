<?php

/**
 * @ Created on 08/02/2023 13:37
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Omar Kennouche <topdeveloppement@gmail.com>
 * @ Licence For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Group;
use App\Doctrine\Entity\Media;
use App\DTO\EntityDecrypt\GroupDecrypt;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Encryptor\EncryptorInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupRepository extends ServiceEntityRepository
{
    private MediaRepository $mediaRepository;
    private EncryptorInterface $encryptor;

    public function __construct(ManagerRegistry $registry, MediaRepository $mediaRepository, EncryptorInterface $encryptor)
    {
        parent::__construct($registry, Group::class);

        $this->mediaRepository = $mediaRepository;
        $this->encryptor = $encryptor;
    }

    /**
     * Load all Group which has the same parent of $group
     */
    public function getSamePath(GroupDecrypt $group): array
    {
        $parameters = [];

        if (isset($group->parent)) {
            $parent = "g.parent = :parentId";
            $parameters[":parentId"] = $group->parent->getUuid();
        } else {
            $parent = "g.parent IS NULL";
        }

        if (isset($group->uuid)) {
            $current = "AND g.uuid <> :groupUuid";
            $parameters[":groupUuid"] = $group->uuid;
        } else {
            $current = "";
        }

        if (isset($group->name)) {
            $query = $this->createQueryBuilder('g')
                ->where($parent . " AND g.name = :groupName " . $current);
            $parameters[":groupName"] =  $this->encryptor->encryptData($group->name);
        } else {
            return [];
        }

        $query->setParameters($parameters);

        return $query->getQuery()->getResult();
    }

    /**
     * Load all Group compose $path, even last
     */
    public function getParents(array $path): ?array
    {
        if (count($path)) {
            $c = 0;
            $parameters = [];

            $query = $this->createQueryBuilder("g$c");
            $where = "g$c.name = :name$c";
            $parameters[":name$c"] = $path[0];
            $select = "g$c";

            if (count($path) > 1) {
                for ($i=1;$i<count($path);$i++) {
                    $c++;
                    $query->leftJoin(Group::class, "g$c", "WITH", "g" . $c-1 . ".uuid = g$c.parent AND g$c.name = :name$c");
                    $parameters[":name$c"] = $path[$i];
                    $select .= ", g$c";
                }
            }

            $query->setParameters($parameters);
            $query->where($where);
            $query->select($select);

            $result = $query->getQuery()->getResult();

            if (!count($result)) {
                $result = array_fill(0, count($path), null);
            }

            return $result;
        } else {
            return null;
        }
    }

    /**
     * Return all Group compose the path of Group which have $uuid
     */
    public function getPath(string $uuid, int $deep): array
    {
        $c = 0;

        $query = $this->createQueryBuilder("g$c");
        $where = "g0.uuid = :uuid";
        $select = "g$c";

        for ($i=1;$i<=$deep;$i++) {
            $c++;
            $query->leftJoin(Group::class, "g$c", "WITH", "g" . $c-1 . ".parent = g$c.uuid");
            $select .= ", g$c";
        }

        $query->setParameters([":uuid" => $uuid]);
        $query->where($where);
        $query->select($select);

        $result = $query->getQuery()->getResult();

        if (!count($result)) {
            $result = array_fill(0, $deep, null);
        }

        return array_reverse($result);
    }

    /**
     * Update deep field for all element which have a null parent and for all child of $parent
     */
    public function updateDeep(Group $parent, int $newDeep): void
    {
        $this->createQueryBuilder("g")
            ->update(Group::class, "g")
            ->set("g.deep", "0")
            ->where("g.parent IS NULL")
            ->getQuery()
            ->execute();

        $this->mediaRepository->createQueryBuilder("m")
            ->update(Media::class, "m")
            ->set("m.deep", "0")
            ->where("m.parent IS NULL")
            ->getQuery()
            ->execute();

        $parents = [$parent];

        while (count($parents)) {
            foreach ($parents as $parent) {
                $parent->setDeep($newDeep);
            }

            $childs = $this->mediaRepository->createQueryBuilder("m")
                        ->where("m.parent IN (:parents)")
                        ->setParameter(":parents", $parents)
                        ->getQuery()
                        ->getResult();

            foreach ($childs as $child) {
                $child->setDeep($newDeep + 1);
            }

            $newDeep++;

            $parents = $this->createQueryBuilder("g")
                ->where("g.parent IN (:parents)")
                ->setParameter(":parents", $parents)
                ->getQuery()
                ->getResult();
        }

        $this->getEntityManager()->flush();
    }
}
