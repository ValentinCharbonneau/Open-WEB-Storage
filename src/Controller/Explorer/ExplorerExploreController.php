<?php

/**
 * @ Created on 21/02/2023 10:16
 * @ This file is part of the netagri-api project.
 * @ Contact (c) Omar Kennouche <topdeveloppement@gmail.com>
 * @ Licence For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Controller\Explorer;

use Symfony\Component\Routing\Annotation\Route;
use App\Services\GEDService\GEDServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/%base_ged_version%/explorer/{path}', name: 'explorer_explore', requirements: ['path' => '.+'], methods: ['GET'])]
class ExplorerExploreController extends AbstractController
{
    public function __invoke(
        GEDServiceInterface $GEDService,
        ?string $path = null
    ) {
        try {
            return new JsonResponse($GEDService->explore($path));
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(["code" => 404, "message" => "Not found file '$path'"], 404);
        }
    }
}
