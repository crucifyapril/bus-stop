<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusRouteRequest;
use App\Services\BusRouteService;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BusRouteController extends Controller
{
    public function __construct(
        private readonly BusRouteService $busRouteService
    ) {
    }

    public function index(): JsonResponse
    {
        try {
            $routes = $this->busRouteService->getAllRoutes();
        } catch (Exception) {
            return response()->json([
                'error' => 'Ошибка при получении маршрутов',
                'message' => 'Не удалось найти маршруты. Пожалуйста, попробуйте позже.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($routes, Response::HTTP_OK);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $route = $this->busRouteService->getRouteById($id);
        } catch (Exception) {
            return response()->json([
                'error' => 'Ошибка при получении маршрута',
                'message' => 'Маршрут не найден.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($route, Response::HTTP_OK);
    }

    public function store(BusRouteRequest $request): JsonResponse
    {
        try {
            $route = $this->busRouteService->createRoute($request->validated());
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Ошибка при создании маршрута',
                'message' => $e->getMessage(),
            ], Response::HTTP_CONFLICT);
        }

        return response()->json($route, Response::HTTP_CREATED);
    }

    public function update(BusRouteRequest $request, int $id): JsonResponse
    {
        try {
            $route = $this->busRouteService->updateRoute($id, $request->validated());
        } catch (Exception) {
            return response()->json([
                'error' => 'Ошибка при обновлении маршрута',
                'message' => 'Маршрут не найден.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($route, Response::HTTP_OK);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->busRouteService->deleteRoute($id);
        } catch (Exception) {
            return response()->json([
                'error' => 'Ошибка при удалении маршрута',
                'message' => 'Маршрут не найден для удаления.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['message' => 'Маршрут успешно удален'], Response::HTTP_NO_CONTENT);
    }
}
