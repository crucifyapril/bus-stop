<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Route;
use App\Models\Stop;
use App\Models\Bus;
use App\Models\ArrivalTime;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $stopPushkina = Stop::firstOrCreate(['name' => 'ул. Пушкина']);
            $stopLenina = Stop::firstOrCreate(['name' => 'ул. Ленина']);
            $stopPopova = Stop::firstOrCreate(['name' => 'ост. Попова']);

            $route11 = Route::firstOrCreate([
                'name' => 'Автобус №11',
                'direction' => 'forward',
            ]);

            $route21 = Route::firstOrCreate([
                'name' => 'Автобус №21',
                'direction' => 'forward',
            ]);

            $route11->stops()->sync([
                $stopPushkina->id => ['position_in_route' => 1],
                $stopPopova->id => ['position_in_route' => 2],
            ]);

            $route21->stops()->sync([
                $stopPushkina->id => ['position_in_route' => 1],
                $stopLenina->id => ['position_in_route' => 2],
            ]);

            $bus11 = Bus::firstOrCreate([
                'name' => 'Автобус №11',
                'route_id' => $route11->id,
            ]);

            $bus21 = Bus::firstOrCreate([
                'name' => 'Автобус №21',
                'route_id' => $route21->id,
            ]);

            $arrivalTimes = [
                ['bus_id' => $bus11->id, 'stop_id' => $stopPushkina->id, 'arrival_time' => '08:15'],
                ['bus_id' => $bus11->id, 'stop_id' => $stopPushkina->id, 'arrival_time' => '18:40'],
                ['bus_id' => $bus11->id, 'stop_id' => $stopPushkina->id, 'arrival_time' => '19:15'],

                ['bus_id' => $bus21->id, 'stop_id' => $stopPushkina->id, 'arrival_time' => '18:30'],
                ['bus_id' => $bus21->id, 'stop_id' => $stopPushkina->id, 'arrival_time' => '19:04'],
                ['bus_id' => $bus21->id, 'stop_id' => $stopPushkina->id, 'arrival_time' => '19:30'],
            ];

            foreach ($arrivalTimes as $time) {
                ArrivalTime::firstOrCreate($time);
            }
        });
    }
}
