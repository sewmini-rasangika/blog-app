<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

class Activity
{
    public static function log(
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?User $actor = null
    ): void {
        try {
            $actor ??= auth()->user();

            ActivityLog::create([
                'user_id'      => $actor?->id,
                'user_name'    => $actor?->name,
                'action'       => $action,
                'description'  => Str::limit($description, 250, ''),
                'subject_type' => $subject ? class_basename($subject) : null,
                'subject_id'   => $subject?->getKey(),
                'properties'   => $properties ?: null,
                'ip_address'   => request()->ip(),
                'user_agent'   => Str::limit((string) request()->userAgent(), 250, ''),
                'created_at'   => now(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}