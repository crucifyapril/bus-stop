<?php

namespace App\Services;

use App\Models\ArrivalTime;
use App\Models\Bus;
use App\Models\Route;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BusRouteService
{
    public function getAllRoutes(): LengthAwarePaginator
    {
        return Route::with(['stops', 'buses.arrivalTimes'])->paginate(10);
    }

    public function getRouteById(int $id)
    {
        return Route::with(['stops', 'buses.arrivalTimes'])->findOrFail($id);
    }

    /**
     * @throws Exception
     */
    public function createRoute(array $data)
    {
        DB::beginTransaction();

        try {
            // Проверяем существование маршрута с таким же направлением
            $existingRoute = Route::query()->where('name', $data['name'])
                ->where('direction', $data['direction'])
                ->first();

            if ($existingRoute) {
                throw new Exception('Маршрут с таким направлением уже существует.');
            }

            // Создаем маршрут
            $route = Route::query()->create([
                'name' => $data['name'],
                'direction' => $data['direction']
            ]);

            // Добавляем остановки
            if (!empty($data['stops'])) {
                foreach ($data['stops'] as $index => $stopId) {
                    $route->stops()->attach($stopId, ['position_in_route' => $index + 1]);
                }
            }

            // Добавляем автобусы и их расписание
            if (!empty($data['buses'])) {
                foreach ($data['buses'] as $busData) {
                    $bus = Bus::query()->create([
                        'name' => $busData['name'],
                        'route_id' => $route->id,
                    ]);

                    if (!empty($busData['arrival_times'])) {
                        foreach ($busData['arrival_times'] as $stopId => $arrivalTime) {
                            ArrivalTime::query()->create([
                                'bus_id' => $bus->id,
                                'stop_id' => $stopId,
                                'arrival_time' => $arrivalTime,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return $route->load(['stops', 'buses.arrivalTimes']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function updateRoute(int $id, array $data)
    {
        DB::beginTransaction();

        try {
            $route = Route::query()->findOrFail($id);
            $route->update([
                'name' => $data['name'],
                'direction' => $data['direction']
            ]);

            if (isset($data['stops'])) {
                $route->stops()->detach();
                foreach ($data['stops'] as $index => $stopId) {
                    $route->stops()->attach($stopId, ['position_in_route' => $index + 1]);
                }
            }

            if (!empty($data['buses'])) {
                foreach ($data['buses'] as $busData) {
                    $bus = Bus::query()->updateOrCreate(
                        ['name' => $busData['name'], 'route_id' => $route->id],
                        ['name' => $busData['name']]
                    );

                    if (!empty($busData['arrival_times'])) {
                        foreach ($busData['arrival_times'] as $stopId => $arrivalTime) {
                            ArrivalTime::query()->updateOrCreate(
                                ['bus_id' => $bus->id, 'stop_id' => $stopId],
                                ['arrival_time' => $arrivalTime]
                            );
                        }
                    }
                }
            }

            DB::commit();
            return $route->load(['stops', 'buses.arrivalTimes']);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function deleteRoute(int $id)
    {
        DB::beginTransaction();

        try {
            $route = Route::query()->findOrFail($id);

            // Удаляем все автобусы, связанные с маршрутом
            foreach ($route->buses as $bus) {
                ArrivalTime::query()->where('bus_id', $bus->id)->delete();
                $bus->delete();
            }

            // Удаляем маршрут и его связи
            $route->stops()->detach();
            $route->delete();

            DB::commit();
            Log::info("Deleted route: {$id}");
            return "Маршрут удален";
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}
