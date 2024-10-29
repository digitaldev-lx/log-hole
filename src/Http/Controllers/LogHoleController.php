<?php

namespace DigitalDevLx\LogHole\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class LogHoleController
{
    public function index()
    {
        // Verifica se o usuário tem permissão
        if (!config('app.debug')) {
            throw new AuthorizationException('You do not have access to view this dashboard.');
        }

        // Recupera os logs do banco de dados
        $logs = DB::table(config('log-hole.database.table'))->orderBy('logged_at', 'desc')->paginate(10);

        return view('log-hole::dashboard', compact('logs'));
    }
}
