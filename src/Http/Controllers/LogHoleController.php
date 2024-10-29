<?php

namespace DigitalDevLx\LogHole\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class LogHoleController
{
    public function index()
    {

        // Recupera os logs do banco de dados
        $logs = DB::table(config('log-hole.database.table'))->orderBy('logged_at', 'desc')->paginate(10);

        return view('log-hole::dashboard', compact('logs'));
    }
}
