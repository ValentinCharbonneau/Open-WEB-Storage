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
use App\Services\Encryptor\Encryptor;
use App\DTO\EntityDecrypt\GroupDecrypt;
use App\DTO\EntityDecrypt\MediaDecrypt;
use Doctrine\ORM\EntityManagerInterface;
use App\DTO\EntityDecrypt\ArchiveDecrypt;
use App\Doctrine\Repository\GroupRepository;
use App\Doctrine\Repository\MediaRepository;
use App\DTO\TransformerDTO\GroupTransformerInput;
use App\DTO\TransformerDTO\MediaTransformerInput;
use App\DTO\TransformerDTO\MediaTransformerOutput;
use App\DTO\TransformerDTO\GroupTransformerOutput;
use App\Services\Security\SecurityServiceInterface;
use App\DTO\TransformerDTO\ArchiveTransformerInput;
use App\DTO\TransformerDTO\ArchiveTransformerOutput;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

/**
 * Class FileSystem.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class FileSystem implements FileSystemInterface
{
    private ?array $pathToPersist;

    public function __construct(
        private ArchiveTransformerOutput $archiveTransformerOutput,
        private ArchiveTransformerInput $archiveTransformerInput,
        private MediaTransformerOutput $mediaTransformerOutput,
        private GroupTransformerOutput $groupTransformerOutput,
        private GroupTransformerInput $groupTransformerInput,
        private MediaTransformerInput $mediaTransformerInput,
        private EntityManagerInterface $entityManager,
        private SecurityServiceInterface $security,
        private GroupRepository $groupRepository,
        private MediaRepository $mediaRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private ParameterBagInterface $bag,
        private SluggerInterface $slugger,
        private Encryptor $encryptor,
    ) {
        $this->pathToPersist = null;
    }

    /**
     * Clean $path, remove multiple '/' and '\' and replace '\' to '/'
     */
    public function cleanPath(string $path): string
    {
        $path = preg_replace('/\\\\/i', "/", $path);
        $path = preg_replace('/\/+/i', "/", $path);
        $path = preg_replace('/^\//i', "", $path);
        return preg_replace('/\/$/i', "", $path);
    }

    /**
     * Explode $path and return an array contains name of all elements in $path
     */
    public function explodePath(string $path): array
    {
        return explode("/", $this->cleanPath($path));
    }

    /**
     * Load or create all elements il $path, except the last, we create or load only parents
     */
    public function buildParents(string $path): ?Group
    {
        # Explode path and remove last file
        # The last file is file we must create, and in this function we load or create all Group in the path of this file
        $parentsNames = $this->explodePath($path);
        array_pop($parentsNames);

        if (!count($parentsNames)) {
            return null;
        }

        # Encrypt all path to ask database
        $c = 0;
        $parentsNamesEncrypt = [];
        foreach ($parentsNames as $parent) {
            $parentsNamesEncrypt[$c] = $this->encryptor->encryptData(strval($this->slugger->slug($parent)));
            $c++;
        }

        # Load all Groups in path $parentsNamesEncrypt, or null for Groups didn't exist
        $loadedParents = $this->groupRepository->getParents($parentsNamesEncrypt);

        # Foreach element in path, if Group doesn't exist we create it, else we set deep
        for ($i=0;$i<count($parentsNames);$i++) {
            if ($loadedParents[$i] == null) {
                $group = new Group();
                $group->setParent($loadedParents[$i == 0 ? 0 : $i - 1]);
                $group->setDeep($i);
                $group->setOwner($this->security->getUser());
                $group->setName($this->encryptor->encryptData($parentsNames[$i]));

                $loadedParents[$i] = $group;
            } else {
                $loadedParents[$i]->setDeep($i);
            }
        }

        # We store different parents in attribute to persist it when we persist or update the file
        $this->pathToPersist = $loadedParents;

        # We return the last parent
        # It's parent of the last element of the path, so th element was created or updated
        return $loadedParents[count($loadedParents) - 1];
    }

    /**
     * Return entity correspond to $uuid.
     * $className specify the entity we need to laod, so must be Media::class or Group::class or Archive::class
     */
    public function get(string $uuid, string $className): null|Media|Group|Archive
    {
        if ($className != Media::class && $className != Group::class && $className != Archive::class) {
            throw new UnprocessableEntityHttpException("GED service FileSystem : \$className must be Media::class, Group::class or Archive::class");
        }

        return $this->entityManager->getRepository($className)->findOneBy(["uuid" => $uuid, "owner" => $this->security->getUser()]);
    }

    /**
     * Use validator to return violations of $entity
     */
    public function valid(MediaDecrypt|GroupDecrypt|ArchiveDecrypt $entity): ConstraintViolationListInterface
    {
        return $this->validator->validate($entity);
    }

    /**
     * Transform a Decrypt entity to an Entity
     * Transform MediaDecrypt to Media
     * Transform GroupDecrypt to Group
     * Transform ArchiveDecrypt to Archive
     */
    public function encrypt(MediaDecrypt|GroupDecrypt|ArchiveDecrypt $decryptEntity): Media|Group|Archive
    {
        switch (get_class($decryptEntity)) {
            case MediaDecrypt::class:
                $encryptEntity = new Media();
                $encryptEntity->setName($this->encryptor->encryptData($decryptEntity->name));
                $encryptEntity->setType($this->encryptor->encryptData($decryptEntity->type));
                $encryptEntity->setMetadata($this->encryptor->encryptData(json_encode($decryptEntity->metadata)));
                $encryptEntity->setDeep($decryptEntity->deep);
                $encryptEntity->setOwner($decryptEntity->owner);
                $encryptEntity->setParent($decryptEntity->parent);
                return $encryptEntity;
            case GroupDecrypt::class:
                $encryptEntity = new Group();
                $encryptEntity->setName($this->encryptor->encryptData($decryptEntity->name));
                $encryptEntity->setDeep($decryptEntity->deep);
                $encryptEntity->setOwner($decryptEntity->owner);
                $encryptEntity->setParent($decryptEntity->parent);
                return $encryptEntity;
            case ArchiveDecrypt::class:
                $encryptEntity = new Archive(
                    $this->encryptor->encryptData($decryptEntity->path),
                    $this->encryptor->encryptData(json_encode($decryptEntity->metadata)),
                    $decryptEntity->owner
                );
                return $encryptEntity;
        }
    }

    /**
     * Transform an ecrypt entity to a decrypt entity
     * Transform Media to MediaDecrypt
     * Transform Group to GroupDecrypt
     * Transform Archive to ArchiveDecrypt
     */
    public function decrypt(Media|Group|Archive $encryptEntity): MediaDecrypt|GroupDecrypt|ArchiveDecrypt
    {
        switch (get_class($encryptEntity)) {
            case Media::class:
                $decryptEntity = new MediaDecrypt();
                $decryptEntity->uuid = $encryptEntity->getUuid();
                $decryptEntity->name = $this->encryptor->decryptData($encryptEntity->getName());
                $decryptEntity->type = $this->encryptor->decryptData($encryptEntity->getType());
                $decryptEntity->metadata = json_decode($this->encryptor->decryptData($encryptEntity->getMetadata()));
                $decryptEntity->deep = $encryptEntity->getDeep();
                $decryptEntity->owner = $encryptEntity->getOwner();
                $decryptEntity->parent = $encryptEntity->getParent();
                return $decryptEntity;
                # Because of Group has a recursive relation, Doctrine can not load completely all Group, and it can be a Proxy
            case "Proxies\__CG__\App\Doctrine\Entity\Group":
            case Group::class:
                $decryptEntity = new GroupDecrypt();
                $decryptEntity->uuid = $encryptEntity->getUuid();
                $decryptEntity->name = $this->encryptor->decryptData($encryptEntity->getName());
                $decryptEntity->deep = $encryptEntity->getDeep();
                $decryptEntity->owner = $encryptEntity->getOwner();
                $decryptEntity->parent = $encryptEntity->getParent();
                return $decryptEntity;
            case Archive::class:
                $decryptEntity = new ArchiveDecrypt();
                $decryptEntity->uuid = $encryptEntity->getUuid();
                $decryptEntity->path = $this->encryptor->decryptData($encryptEntity->getPath());
                $decryptEntity->metadata = json_decode($this->encryptor->decryptData($encryptEntity->getMetadata()));
                $decryptEntity->owner = $encryptEntity->getOwner();

                return $decryptEntity;
        }
    }

    /**
     * Transform a DTO to an Entity directly, so decrypt or encrypt and transform
     * Transform Media to MediaDTO or MediaDTO to Media
     * Transform Group to GroupDTO or GroupDTO to Group
     * Transform Archive to ArchiveTO or ArchiveDTO to Archive
     */
    public function fullTransform(Media|MediaDTO|Group|GroupDTO|Archive|ArchiveDTO $entity): Media|MediaDTO|Group|GroupDTO|Archive|ArchiveDTO
    {
        switch (get_class($entity)) {
            case Media::class:
                $decrypt = $this->decrypt($entity);
                return $this->mediaTransformerOutput->transform($decrypt);
            case MediaDTO::class:
                $decrypt = $this->mediaTransformerInput->transform($entity, $this->explodePath($entity->path), $this->buildParents($entity->path));
                return $this->encrypt($decrypt);
                # Because of Group has a recursive relation, Doctrine can not load completely all Group, and it can be a Proxy
            case "Proxies\__CG__\App\Doctrine\Entity\Group":
            case Group::class:
                $decrypt = $this->decrypt($entity);
                return $this->groupTransformerOutput->transform($decrypt);
            case GroupDTO::class:
                $decrypt = $this->groupTransformerInput->transform($entity, $this->explodePath($entity->path), $this->buildParents($entity->path));
                return $this->encrypt($decrypt);
            case Archive::class:
                $decrypt = $this->decrypt($entity);
                return $this->archiveTransformerOutput->transform($decrypt);
            case ArchiveDTO::class:
                $decrypt = $this->archiveTransformerInput->transform($entity);
                return $this->encrypt($decrypt);
        }

        return $entity;
    }

    /**
     * Use transformers classes to transform DTO to Decrypt Entity
     * Transform MediaDTO to DecryptMedia or DecryptMedia to MediaDTO
     * Transform GroupDTO to DecryptGroup or DecryptGroup to GroupDTO
     * Transform ArchiveDTO to DecryptArchive or DecryptArchive to ArchiveDTO
     */
    public function transform(MediaDecrypt|MediaDTO|GroupDecrypt|GroupDTO|ArchiveDecrypt|ArchiveDTO $entity): MediaDecrypt|MediaDTO|GroupDecrypt|GroupDTO|ArchiveDecrypt|ArchiveDTO
    {
        switch (get_class($entity)) {
            case MediaDTO::class:
                return $this->mediaTransformerInput->transform(
                    $entity,
                    isset($entity->path) ? $this->explodePath($entity->path) : [null],
                    isset($entity->path) ? $this->buildParents($entity->path) : null
                );
            case MediaDecrypt::class:
                return $this->mediaTransformerOutput->transform($entity);
            case GroupDTO::class:
                return $this->groupTransformerInput->transform(
                    $entity,
                    isset($entity->path) ? $this->explodePath($entity->path) : [null],
                    isset($entity->path) ? $this->buildParents($entity->path) : null
                );
            case GroupDecrypt::class:
                return $this->groupTransformerOutput->transform($entity);
            case ArchiveDTO::class:
                return $this->archiveTransformerInput->transform($entity);
            case ArchiveDecrypt::class:
                return $this->archiveTransformerOutput->transform($entity);
        }

        return $entity;
    }

    /**
     * Save $entity and $file
     */
    public function save(Media|Group|Archive &$entity, ?string $file = null): void
    {
        # Save all parents compose the path of $entity
        if (!empty($this->pathToPersist)) {
            foreach ($this->pathToPersist as $parent) {
                if (empty($parent->getUuid())) {
                    $this->entityManager->persist($parent);
                }
            }
        }

        $this->entityManager->persist($entity);

        # Save file for Media and Archive
        if ($entity instanceof Media && !empty($file)) {
            if (!file_exists($this->bag->get("doc_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $entity->getUuid())) {
                file_put_contents(
                    $this->bag->get("doc_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $entity->getUuid(),
                    $this->encryptor->encryptData($file)
                );
            } else {
                throw new \Exception("Folder '" . $this->bag->get("doc_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $entity->getUuid() . "' already exist");
            }
        } elseif ($entity instanceof Archive && !empty($file)) {
            if (!file_exists($this->bag->get("archive_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $entity->getUuid())) {
                file_put_contents(
                    $this->bag->get("archive_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $entity->getUuid(),
                    $this->encryptor->encryptData($file)
                );
            } else {
                throw new \Exception("Folder '" . $this->bag->get("archive_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $entity->getUuid() . "' already exist");
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Update $entity and $file
     */
    public function update(Media|Group|Archive &$entity, ?string $file = null): void
    {
        # Save all parents compose the path of $entity
        if (!empty($this->pathToPersist)) {
            foreach ($this->pathToPersist as $parent) {
                if (empty($parent->getUuid())) {
                    $this->entityManager->persist($parent);
                }
            }
        }

        # Update file for Media
        # Archive can't be updated
        if (($entity instanceof Media) && !empty($file)) {
            file_put_contents(
                $this->bag->get("doc_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $entity->getUuid(),
                $this->encryptor->encryptData($file)
            );
        }

        $this->entityManager->flush();

        # Update deep field of all children of $entity
        if ($entity instanceof Group) {
            $this->groupRepository->updateDeep($entity, $entity->getDeep());
        }
    }

    /**
     * Remove $entity and its file if it exists
     */
    public function remove(Media|Group|Archive $entity): void
    {
        # If $entity is Media or Archive we remove its file
        if ($entity instanceof Media && file_exists($this->bag->get("doc_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $entity->getUuid())) {
            unlink($this->bag->get("doc_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $entity->getUuid());
        } elseif ($entity instanceof Archive && file_exists($this->bag->get("archive_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $entity->getUuid())) {
            unlink($this->bag->get("archive_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $entity->getUuid());
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    /**
     * Explore file system and return content at the $path emplacement
     */
    public function explore(?string $path = null): array
    {
        $result = [];

        # Set contexts for serialization and clean $path
        $mediaContext = (new ObjectNormalizerContextBuilder())->withGroups(['read:media']);
        $groupContext = (new ObjectNormalizerContextBuilder())->withGroups(['read:group']);
        if (isset($path)) {
            $path = $this->cleanPath($path);
        }

        if (isset($path) && $path !== "") {
            # We explode $path and load all element in this
            $encryptPath = $this->explodePath($path);
            foreach ($encryptPath as &$parent) {
                $parent = $this->encryptor->encryptData($parent);
            }
            $parents = $this->groupRepository->getParents($encryptPath);

            # If last element is null, this is a Media or the ressource doesn't exist
            if ($parents[count($parents) - 1] == null) {
                $explodePath = $this->explodePath($path);
                $name = explode(".", $explodePath[count($explodePath) - 1]);
                $result = $this->mediaRepository->findMedia(
                    $this->security->getUser(),
                    $this->encryptor->encryptData($name[0]),
                    $this->encryptor->encryptData($name[count($name) - 1]),
                    $parents[count($parents) - 2] ?? null,
                );

                # If $result is null, the resource doesn't exist
                if (empty($result)) {
                    throw new NotFoundHttpException();
                }

                # Else, we return the Media entity
                return $this->serializer->normalize($this->fullTransform($result), 'json', $mediaContext->toArray());
            } elseif ((count($parents) == 1 && $parents[0] == null) || ($parents[count($parents) - 1] == null && empty($parents[count($parents) - 2]))) {
                # If the more of one element is null in the path, the resource doesn't exist
                throw new NotFoundHttpException();
            }
        } else {
            # If $path is null we are at the root
            $parents = [null];
        }

        # Load all elements at the emplacement
        $groups = $this->groupRepository->findBy(["parent" => $parents[count($parents) - 1], "owner" => $this->security->getUser()]);
        $medias = $this->mediaRepository->findBy(["parent" => $parents[count($parents) - 1], "owner" => $this->security->getUser()]);

        # We normalize the elements and add at the final $result
        foreach ($groups as $group) {
            $result[] = $this->serializer->normalize($this->fullTransform($group), 'json', $groupContext->toArray());
        }
        foreach ($medias as $media) {
            $result[] = $this->serializer->normalize($this->fullTransform($media), 'json', $mediaContext->toArray());
        }

        return $result;
    }
}
