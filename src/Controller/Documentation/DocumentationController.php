<?php

/**
 * @ Created on 07/03/2023 16:31
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Controller\Documentation;

use Symfony\Component\Routing\Annotation\Route;
use App\Services\Documentation\DocumentationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/', name: 'home_documentation', methods: ['GET'])]
class DocumentationController extends AbstractController
{
    public function __invoke(
        DocumentationInterface $documentation
    ) {
        $result = $documentation->build();
        return $this->render('documentation.html.twig', [
            'api' => $result["result"], "pagination" => $result["pagination"]
        ]);
    }
}
