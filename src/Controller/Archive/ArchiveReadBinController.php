<?php

/**
 * @ Created on 21/02/2023 09:43
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Controller\Archive;

use App\Doctrine\Entity\Archive;
use App\Doctrine\Entity\Media;
use App\Services\GEDService\GEDServiceInterface;
use App\Services\Security\SecurityServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Encryptor\EncryptorInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\FileSystem\FileSystemInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Route('/archive/bin/{uuid}', name: 'archive_read_bin', methods: ['GET'])]
class ArchiveReadBinController extends AbstractController
{
    public function __invoke(
        GEDServiceInterface $GEDService,
        FileSystemInterface $fileSystem,
        SerializerInterface $serializer,
        EncryptorInterface $encryptor,
        ParameterBagInterface $bag,
        Security $security,
        string $uuid
    ) {
        try {
            $content = $GEDService->readBase64Archive($uuid);
            $archive = $fileSystem->get($uuid, Archive::class);

            $response = new Response(base64_decode($content));
            $path = explode("/", $encryptor->decryptData($archive->getPath()));
            $name = $path[count($path) - 1];

            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                filename: $name
            );

            $response->headers->set('Content-Disposition', $disposition);

            $name = explode(".", $name);

            switch (strtolower($name[count($name) - 1])) {
                case "pdf":
                    $response->headers->set('Content-Type', "application/pdf");
                    break;
                case "csv":
                    $response->headers->set('Content-Type', "text/csv");
                    break;
            }

            return $response;
        } catch (ResourceNotFoundException $e) {
            return new JsonResponse(["code" => 404, "message" => "Resource '$uuid' not found."], 404);
        }
    }
}
