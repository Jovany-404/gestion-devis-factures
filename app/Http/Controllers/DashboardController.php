<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'clientCount' => Client::count(),
            'recentClients' => Client::latest()->take(5)->get(),
        ]);
    }
}
