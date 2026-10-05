<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Company;
use App\Models\Level;
use App\Models\Position;
use App\Models\Wilayah;
use App\Models\EventHourGroup;
use App\Models\PlafonEventDalamKota;
use App\Models\PlafonEventLuarKota;
use App\Models\User;
use App\Models\CostCategory;
use App\Models\WeekDayGroup;

class DashboardMasterController extends Controller
{
    public function index()
    {
        // Menghitung jumlah total data di database untuk statistik kartu
        $counts = [
            'users' => User::count(),
            'roles' => Role::count(),
            'companies' => Company::count(),
            'levels' => Level::count(),
            'positions' => Position::count(),
            'wilayah' => Wilayah::count(),
            'event_hours' => EventHourGroup::count(),
            'plafon_dalam' => PlafonEventDalamKota::count(),
            'plafon_luar' => PlafonEventLuarKota::count(),
            'cost_categories' => CostCategory::count(),
            'week_day_groups' => WeekDayGroup::count(),
          ];

        return view('dashboard.master_data', compact('counts'));
    }
}