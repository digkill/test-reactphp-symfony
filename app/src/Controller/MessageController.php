<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MessageController
 */
class MessageController
{

    /**
     * @Route("/api/message/send")
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request, $connection): JsonResponse
    {

        $response = sprintf(
            'This kernel has handled %d requests since initiation.',
            $request->attributes->get('count', 0)
        );
        return new JsonResponse($response);
    }
}