<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Répond aux requêtes automatiques (ex. Chrome DevTools) pour éviter des 404 dans les logs.
 */
class WellKnownController extends AbstractController
{
    #[Route('/.well-known/appspecific/com.chrome.devtools.json', name: 'well_known_chrome_devtools', methods: ['GET'], priority: 255)]
    public function chromeDevTools(): Response
    {
        return new JsonResponse([], Response::HTTP_OK);
    }
}
