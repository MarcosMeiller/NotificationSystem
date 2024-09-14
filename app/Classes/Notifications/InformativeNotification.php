<?php

namespace Classes\Notifications;

class InformativeNotification extends Notification {

    public function getType() {
        return 'informative';
    }
}