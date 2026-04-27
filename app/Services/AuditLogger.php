<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public function log(User $admin, string $action, ?Model $auditable = null, ?array $properties = null): void
    {
        AdminAuditLog::query()->create([
            'user_id' => $admin->id,
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'properties' => $properties,
            'ip_address' => request()->ip(),
        ]);
    }
}
