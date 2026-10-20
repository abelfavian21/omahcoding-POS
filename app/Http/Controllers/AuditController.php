<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('action'), fn ($q) => $q->where('action', 'like', $request->string('action') . '%'))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('audit.index', ['logs' => $logs]);
    }
}
