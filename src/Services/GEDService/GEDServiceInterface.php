<?php

namespace App\Services\GEDService;

use App\DTO\EntityDTO\GroupDTO;
use App\DTO\EntityDTO\MediaDTO;
use App\DTO\EntityDTO\ArchiveDTO;

/**
 * Interface GEDServiceInterface.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
interface GEDServiceInterface
{
    function explore(?string $path): array;

    function createGroup(GroupDTO $groupDTO): GroupDTO;

    function updateGroup(GroupDTO $groupDTO): GroupDTO;

    function deleteGroup(string $uuid): void;

    function readGroup(string $uuid): GroupDTO;

    function readAllGroup(int $page = 1): array;

    function createMedia(MediaDTO $mediaDTO): MediaDTO;

    function updateMedia(MediaDTO $mediaDTO): MediaDTO;

    function archiveMedia(string $uuid): ArchiveDTO;

    function readMedia(string $uuid): MediaDTO;

    function readAllMedia(int $page = 1): array;

    function readBase64Media(string $uuid): string;

    function deleteArchive(string $uuid): void;

    function readArchive(string $uuid): ArchiveDTO;

    function readAllArchive(int $page = 1): array;

    function readBase64Archive(string $uuid): string;
}
