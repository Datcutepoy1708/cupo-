<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Ghi nhật ký hành động của nhân viên vào hệ thống.
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        ?Model $subject = null,
        ?array $properties = null,
        ?User $user = null
    ): AdminActivityLog {
        $currentUser = $user ?? Auth::user();

        return AdminActivityLog::create([
            'user_id' => $currentUser?->id,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent() ?? 'System / Console',
        ]);
    }
}
