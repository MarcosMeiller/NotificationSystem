<?php

namespace Classes;

class User
{
    public $id;
    public $name;
    public $email;
    public $subscriptions = [];
    public $notifications = [
        'urgent' => [],
        'informative' => []

    ];
    public $readNotifications = [];

    public function __construct($id, $name, $email)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function addSubscription($topicId)
    {
        $this->subscriptions[] = $topicId;
    }

    public function getNotifications()
    {
        return $this->notifications;
    }

    public function addNotification($notification) {
        if ($notification->getType() === 'urgent') {
            array_unshift($this->notifications['urgent'], $notification);
        } else {
            $this->notifications['informative'][] = $notification;
        }
    }

    public function getUnreadNotifications() {
        $unreadUrgent = array_filter($this->notifications['urgent'], function($notification) {
            return !$notification->isReadByUser($this->id) && !$notification->isExpired();
        });

        $unreadInformative = array_filter($this->notifications['informative'], function($notification) {
            return !$notification->isReadByUser($this->id) && !$notification->isExpired();
        });

        return array_merge($unreadUrgent, $unreadInformative);
    }

    public function markNotificationAsRead($notificationId) {
        foreach (['urgent', 'informative'] as $type) {
            foreach ($this->notifications[$type] as $notification) {
                if ($notification->getId() == $notificationId) {
                    $notification->markAsReadByUser($this->id);
                    return true;
                }
            }
        }
        return false;
    }

    

}
