<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\ArrivalTime;
use App\Models\Route;
use App\Models\Stop;

class BusService
{
    public function findBuses(int $fromId, int $toId): array
    {
        Log::info("Searching buses from stop: $fromId to stop: $toId");

        $routes = Route::query()
            ->whereHas('stops', function ($query) use ($fromId) {
                $query->where('stops.id', $fromId);
            })
            ->whereHas('stops', function ($query) use ($toId) {
                $query->where('stops.id', $toId);
            })
            ->with(['buses', 'stops' => function ($query) {
                $query->orderBy('position_in_route');
            }])
            ->get();

        Log::info("Found routes:", $routes->toArray());

        if ($routes->isEmpty()) {
            Log::info("No routes found for stops $fromId and $toId");
        }

        $result = [];

        foreach ($routes as $route) {
            foreach ($route->buses as $bus) {
                $arrivals = ArrivalTime::query()
                    ->where('bus_id', $bus->id)
                    ->where('stop_id', $fromId)
                    ->where('arrival_time', '>', now()->format('H:i:s'))
                    ->orderBy('arrival_time')
                    ->take(3)
                    ->pluck('arrival_time')
                    ->map(fn($time) => substr($time, 0, 5))
                    ->toArray();

                Log::info("Arrivals for bus {$bus->name} on route {$route->name}: " . implode(", ", $arrivals));

                if ($arrivals) {
                    $lastStop = $route->stops->last();

                    $result[] = [
                        'route' => "{$bus->name} в сторону ост. {$lastStop->name}",
                        'next_arrivals' => $arrivals
                    ];
                }
            }
        }

        Log::info("Final result:", $result);

        return [
            'from' => Stop::query()->find($fromId)->name,
            'to' => Stop::query()->find($toId)->name,
            'buses' => $result
        ];
    }
}
