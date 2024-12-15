<?php

/**
 * @ Created on 21/02/2023 10:16
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Controller\Explorer;

use Symfony\Component\Routing\Annotation\Route;
use App\Services\GEDService\GEDServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/explorer/{path}', name: 'explorer_explore', requirements: ['path' => '.+'], methods: ['GET'])]
class ExplorerExploreController extends AbstractController
{
    public function __invoke(
        GEDServiceInterface $gedService,
        ?string $path = null
    ) {
        try {
            return new JsonResponse($gedService->explore($path));
        } catch (NotFoundHttpException $_) {
            return new JsonResponse(["code" => 404, "message" => "Not found file '$path'"], 404);
        }
    }
}
