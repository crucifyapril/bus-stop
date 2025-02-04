<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusRequest;
use App\Services\BusService;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BusController extends Controller
{
    public function __construct(
        private readonly BusService $busService
    ) {
    }

    public function findBus(BusRequest $request): JsonResponse
    {
        $from = $request->input('from');
        $to = $request->input('to');

        try {
            $buses = $this->busService->findBuses($from, $to);
        } catch (Exception) {
            return response()->json([
                'error' => 'Ошибка при поиске маршрута',
                'message' => 'Маршрут не найден',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($buses);
    }
}
