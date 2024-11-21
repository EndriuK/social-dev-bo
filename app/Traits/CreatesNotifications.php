<?php

namespace App\Traits;

use App\Models\Notification;

trait CreatesNotifications
{
    protected function createNotification(int $userId, string $type, array $data)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'data' => $data,
            'is_read' => false
        ]);
    }
} 