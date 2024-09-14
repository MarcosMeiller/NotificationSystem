<?php

namespace Classes\Notifications;

class UrgentNotification extends Notification {

    public function getType() {
        return 'urgent';
    }
}
