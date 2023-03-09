<?php

/**
 * @ Created on 20/02/2023 14:33
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Controller\Media;

use App\Doctrine\Entity\Media;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Encryptor\EncryptorInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\FileSystem\FileSystemInterface;
use App\Services\GEDService\GEDServiceInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

#[Route('/file/bin/{uuid}', name: 'media_read_bin', methods: ['GET'])]
class MediaReadBinController extends AbstractController
{
    public function __invoke(
        GEDServiceInterface $GEDService,
        FileSystemInterface $fileSystem,
        EncryptorInterface $encryptor,
        string $uuid
    ) {
        try {
            $content = $GEDService->readBase64Media($uuid);
            $media = $fileSystem->get($uuid, Media::class);

            $response = new Response(base64_decode($content));

            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                filename: $encryptor->decryptData($media->getName()) . "." . $encryptor->decryptData($media->getType())
            );

            $response->headers->set('Content-Disposition', $disposition);

            switch (strtolower($encryptor->decryptData($media->getType()))) {
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
