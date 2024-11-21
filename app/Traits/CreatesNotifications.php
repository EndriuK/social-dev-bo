<?php

namespace App\Traits;

use App\Models\Notification;

trait CreatesNotifications
{
    public function createNotification($userId, $type, $data)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'data' => $data
        ]);
    }
} 