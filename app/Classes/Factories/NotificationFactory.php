<?php

namespace Classes\Factories;

use Classes\Notifications\UrgentNotification;
use Classes\Notifications\InformativeNotification;

class NotificationFactory
{
    public static function createNotification(string $type, int $id, string $message, \DateTime $expiration)
    {
        if (!in_array($type, ['urgent', 'informative'])) {
            throw new \Exception("Unknown notification type");
        }

        if ($expiration < new \DateTime()) {
            throw new \Exception("Invalid expiration date");
        }

        switch ($type) {
            case 'urgent':
                return new UrgentNotification($id, $message, $expiration);
            case 'informative':
                return new InformativeNotification($id, $message, $expiration);
            default:
                throw new \Exception("Unknown notification type");
        }
    }
}
