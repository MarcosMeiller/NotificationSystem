<?php

namespace Classes\Notifications;

abstract class Notification {
    public $id;
    public $message;
    public $isRead = false;
    public $expirationDate;
    public $isForAll;
    public $targetUser;
    public $readByUsers = [];

    public function __construct($id, $message, \DateTime $expirationDate, $isForAll = true, $targetUser = null) {
        $this->id = $id;
        $this->message = $message;
        $this->expirationDate = $expirationDate;
        $this->isForAll = $isForAll;
        $this->targetUser = $targetUser;
    }

    public function getId() {
        return $this->id;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getExpirationDate() {
        return $this->expirationDate;
    }

    public function markAsRead() {
        $this->isRead = true;
    }

    public function markAsReadByUser($userId) {
        if (!in_array($userId, $this->readByUsers)) {
            $this->readByUsers[] = $userId;
        }
    }

    public function isReadByUser($userId) {
        return in_array($userId, $this->readByUsers);
    }

    public function isForAll() {
        return $this->isForAll;
    }

    public function getTargetUser() {
        return $this->targetUser;
    }

    public function isExpired() {
        return new \DateTime() > $this->expirationDate;
    }

    abstract public function getType();
}
