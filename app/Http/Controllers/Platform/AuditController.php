<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = PlatformAuditLog::with('admin')
            ->when($request->filled('action'), fn ($q) => $q->where('action', 'like', addcslashes((string) $request->query('action'), '%_\\') . '%'))
            ->orderByDesc('id')->paginate(30)->withQueryString();

        return view('platform.audit', ['logs' => $logs]);
    }
}
