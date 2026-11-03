<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $action = array_key_exists($request->query('action'), AuditLog::ACTIONS) ? $request->query('action') : null;

        return view('admin.activity.index', [
            'logs' => AuditLog::with('user')
                ->when($action, fn ($q) => $q->where('action', $action))
                ->latest('id')
                ->paginate(30)
                ->withQueryString(),
            'action' => $action,
        ]);
    }
}
