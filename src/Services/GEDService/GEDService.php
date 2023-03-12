<?php

/**
 * @ Created on 21/02/2023 13:24
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Services\GEDService;

use App\Doctrine\Entity\Group;
use App\Doctrine\Entity\Media;
use App\DTO\EntityDTO\GroupDTO;
use App\DTO\EntityDTO\MediaDTO;
use App\Doctrine\Entity\Archive;
use App\DTO\EntityDTO\ArchiveDTO;
use App\DTO\EntityDecrypt\ArchiveDecrypt;
use App\Services\Security\SecurityServiceInterface;
use App\Doctrine\Repository\GroupRepository;
use App\Doctrine\Repository\MediaRepository;
use App\Services\Encryptor\EncryptorInterface;
use App\Doctrine\Repository\ArchiveRepository;
use App\Services\FileSystem\FileSystemInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

/**
 * Class GEDService.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class GEDService implements GEDServiceInterface
{
    public function __construct(
        private ArchiveRepository $archiveRepository,
        private GroupRepository $groupRepository,
        private MediaRepository $mediaRepository,
        private FileSystemInterface $fileSystem,
        private SerializerInterface $serializer,
        private EncryptorInterface $encryptor,
        private ParameterBagInterface $bag,
        private SecurityServiceInterface $security,
    ) {
        $this->encryptor->loadKeyPair($this->security->getUser()->getUuid());
    }


    ### Explorer ###
    public function explore(?string $path): array
    {
        return $this->fileSystem->explore($path);
    }


    ### Group ###
    public function createGroup(GroupDTO $groupDTO): GroupDTO
    {
        $groupDecrypt = $this->fileSystem->transform($groupDTO);

        $violations = $this->fileSystem->valid($groupDecrypt);
        if (count($violations)) {
            throw new ValidationFailedException($groupDTO, $violations);
        }

        $group = $this->fileSystem->encrypt($groupDecrypt);
        $this->fileSystem->save($group);

        return $this->fileSystem->fullTransform($group);
    }

    public function updateGroup(GroupDTO $groupDTO): GroupDTO
    {
        $currentGroup = $this->fileSystem->get($groupDTO->uuid, Group::class);
        if (empty($currentGroup)) {
            throw new ResourceNotFoundException();
        }

        $currentDecryptGroup = $this->fileSystem->decrypt($currentGroup);

        $mergeGroup = array_merge(
            array_filter($this->serializer->normalize($this->fileSystem->fullTransform($currentGroup), 'json')),
            array_filter($this->serializer->normalize($groupDTO, 'json'))
        );

        $inputContext = (new ObjectNormalizerContextBuilder())->withGroups(['write:group']);
        $groupDTO = $this->serializer->denormalize($mergeGroup, GroupDTO::class, 'json', $inputContext->toArray());

        $groupDecrypt = $this->fileSystem->transform($groupDTO);

        $currentDecryptGroup->name = $groupDecrypt->name;
        $currentGroup->setName($this->encryptor->encryptData($groupDecrypt->name));
        $currentDecryptGroup->parent = $groupDecrypt->parent;
        $currentGroup->setParent($groupDecrypt->parent);
        $currentDecryptGroup->deep = $groupDecrypt->deep;
        $currentGroup->setDeep($groupDecrypt->deep);

        $violations = $this->fileSystem->valid($currentDecryptGroup);

        if (count($violations)) {
            throw new ValidationFailedException($groupDTO, $violations);
        }

        $this->fileSystem->update($currentGroup);

        return $this->fileSystem->fullTransform($currentGroup);
    }

    public function deleteGroup(string $uuid): void
    {
        $group = $archive = $this->fileSystem->get($uuid, Group::class);
        if (empty($group)) {
            throw new ResourceNotFoundException();
        }

        if (count($this->groupRepository->findBy(["parent" => $group])) || count($this->mediaRepository->findBy(["parent" => $group]))) {
            throw new \Exception("Directory must be empty to be removed.");
        }

        $this->fileSystem->remove($group);
    }

    public function readGroup(string $uuid): GroupDTO
    {
        $group = $this->fileSystem->get($uuid, Group::class);
        if (empty($group)) {
            throw new ResourceNotFoundException();
        }

        return $this->fileSystem->fullTransform($group);
    }

    public function readAllGroup(int $page = 1): array
    {
        $page = $page > 0 ? $page : 1;

        $result = $this->groupRepository->findBy(
            ["owner" => $this->security->getUser()],
            limit: $this->bag->get("ged_pagination"),
            offset: ($page - 1) * $this->bag->get("ged_pagination")
        );

        foreach ($result as &$group) {
            $group = $this->fileSystem->fulltransform($group);
        }

        return $result;
    }


    ### Media ###
    public function createMedia(MediaDTO $mediaDTO): MediaDTO
    {
        $groupDecrypt = $this->fileSystem->transform($mediaDTO);

        $violations = $this->fileSystem->valid($groupDecrypt);
        if (count($violations)) {
            throw new ValidationFailedException($mediaDTO, $violations);
        } elseif (empty($mediaDTO->content)) {
            throw new \Exception("Content is required.");
        }

        $group = $this->fileSystem->encrypt($groupDecrypt);
        $this->fileSystem->save($group, $mediaDTO->content);

        return $this->fileSystem->fullTransform($group);
    }

    public function updateMedia(MediaDTO $mediaDTO): MediaDTO
    {
        $currentMedia = $this->mediaRepository->findOneBy(["uuid" => $mediaDTO->uuid, "owner" => $this->security->getUser()]);
        if (empty($currentMedia)) {
            throw new ResourceNotFoundException();
        }

        $currentDecryptMedia = $this->fileSystem->decrypt($currentMedia);

        $mergeMedia = array_merge(
            array_filter($this->serializer->normalize($this->fileSystem->fullTransform($currentMedia), 'json')),
            array_filter($this->serializer->normalize($mediaDTO, 'json'))
        );

        $inputContext = (new ObjectNormalizerContextBuilder())->withGroups(['write:media']);
        $mediaDTO = $this->serializer->denormalize($mergeMedia, MediaDTO::class, 'json', $inputContext->toArray());

        $mediaDecrypt = $this->fileSystem->transform($mediaDTO);

        $currentDecryptMedia->name = $mediaDecrypt->name;
        $currentMedia->setName($this->encryptor->encryptData($mediaDecrypt->name));
        $currentDecryptMedia->type = $mediaDecrypt->type;
        $currentMedia->setType($this->encryptor->encryptData($mediaDecrypt->type));
        $currentDecryptMedia->parent = $mediaDecrypt->parent;
        $currentMedia->setParent($mediaDecrypt->parent);
        $currentDecryptMedia->deep = $mediaDecrypt->deep;
        $currentMedia->setDeep($mediaDecrypt->deep);
        $currentDecryptMedia->metadata = $mediaDecrypt->metadata;
        $currentMedia->setMetadata($this->encryptor->encryptData(json_encode($mediaDecrypt->metadata)));
        $mediaDecrypt->content = $mediaDTO->content;

        $violations = $this->fileSystem->valid($currentDecryptMedia);

        if (count($violations)) {
            throw new ValidationFailedException($mediaDTO, $violations);
        }

        if (isset($mediaDecrypt->content)) {
            $this->fileSystem->update($currentMedia, $mediaDecrypt->content);
        } else {
            $this->fileSystem->update($currentMedia);
        }

        return $this->fileSystem->fullTransform($currentMedia);
    }

    public function archiveMedia(string $uuid): ArchiveDTO
    {
        $media = $this->fileSystem->get($uuid, Media::class);
        if (empty($media)) {
            throw new ResourceNotFoundException();
        }

        $mediaDTO = $this->fileSystem->fullTransform($media);

        $archiveDecrypt = new ArchiveDecrypt();
        $archiveDecrypt->path = $mediaDTO->path;
        $archiveDecrypt->metadata = json_encode($mediaDTO->metadata);
        $archiveDecrypt->owner = $media->getOwner();
        $content = null;
        if (file_exists($this->bag->get("doc_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $media->getUuid())) {
            $content = $this->encryptor->decryptData(file_get_contents($this->bag->get("doc_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $media->getUuid()));
        }

        $archive = $this->fileSystem->encrypt($archiveDecrypt);

        $this->fileSystem->save($archive, $content);
        $this->fileSystem->remove($media);

        return $this->fileSystem->fullTransform($archive);
    }

    public function readMedia(string $uuid): MediaDTO
    {
        $media = $this->fileSystem->get($uuid, Media::class);
        if (empty($media)) {
            throw new ResourceNotFoundException();
        }

        return $this->fileSystem->fullTransform($media);
    }

    public function readAllMedia(int $page = 1): array
    {
        $page = $page > 0 ? $page : 1;

        $result = $this->mediaRepository->findBy(
            ["owner" => $this->security->getUser()],
            limit: $this->bag->get("ged_pagination"),
            offset: ($page - 1) * $this->bag->get("ged_pagination")
        );

        foreach ($result as &$media) {
            $media = $this->fileSystem->fulltransform($media);
        }

        return $result;
    }

    public function readBase64Media(string $uuid): string
    {
        $media = $this->fileSystem->get($uuid, Media::class);
        if (empty($media)) {
            throw new ResourceNotFoundException();
        }

        if (!file_exists($this->bag->get("doc_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $uuid)) {
            throw new \Exception("File doesn't exist.");
        }

        $mediaDTO = new MediaDTO();
        $mediaDTO->content = $this->encryptor->decryptData(file_get_contents($this->bag->get("doc_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $uuid));

        return $mediaDTO->content;
    }


    ### Archive ###
    public function deleteArchive(string $uuid): void
    {
        $archive = $this->fileSystem->get($uuid, Archive::class);
        if (empty($archive)) {
            throw new ResourceNotFoundException();
        }

        $this->fileSystem->remove($archive);
    }

    public function readArchive(string $uuid): ArchiveDTO
    {
        $archive = $this->fileSystem->get($uuid, Archive::class);
        if (empty($archive)) {
            throw new ResourceNotFoundException();
        }

        return $this->fileSystem->fullTransform($archive);
    }

    public function readAllArchive(int $page = 1): array
    {
        $page = $page > 0 ? $page : 1;

        $result = $this->archiveRepository->findBy(
            ["owner" => $this->security->getUser()],
            limit: $this->bag->get("ged_pagination"),
            offset: ($page - 1) * $this->bag->get("ged_pagination")
        );

        foreach ($result as &$archive) {
            $archive = $this->fileSystem->fulltransform($archive);
        }

        return $result;
    }

    public function readBase64Archive(string $uuid): string
    {
        $archive = $this->fileSystem->get($uuid, Archive::class);
        if (empty($archive)) {
            throw new ResourceNotFoundException();
        }

        if (!file_exists($this->bag->get("archive_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $uuid)) {
            throw new \Exception("File doesn't exist.");
        }

        $archiveDTO = new ArchiveDTO();
        $archiveDTO->content = $this->encryptor->decryptData(file_get_contents($this->bag->get("archive_dir") . "/" . $this->security->getUser()->getUuid() . "/" . $uuid));
        return $archiveDTO->content;
    }
}
