<?php

use PHPUnit\Framework\TestCase;
use Classes\User;
use Classes\Factories\NotificationFactory;

class UserTest extends TestCase
{
    private $user;
    private $notification;

    protected function setUp(): void
    {
        $this->user = new User(1, 'Jonh Doe', 'doe@example.com');
        $expiration = new \DateTime('+1 day');
        $this->notification = NotificationFactory::createNotification('informative', 1, 'Test Notification', $expiration);
    }

    public function testAddNotification()
    {
        $this->user->addNotification($this->notification);
        $notifications = $this->user->getNotifications();
        $this->assertCount(1, $notifications['informative']);
        $this->assertSame($this->notification, $notifications['informative'][0]);
    }

    public function testMarkNotificationAsRead()
    {
        $this->user->addNotification($this->notification);
        $this->user->markNotificationAsRead($this->notification->getId());
        $this->assertTrue($this->notification->isReadByUser(1));
    }

    public function testGetUnreadNotifications()
    {
        $this->user->addNotification($this->notification);
        $this->user->markNotificationAsRead($this->notification->getId());
        $unreadNotifications = $this->user->getUnreadNotifications();
        $this->assertEmpty($unreadNotifications);
    }
}
