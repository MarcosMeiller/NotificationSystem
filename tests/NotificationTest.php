<?php

use PHPUnit\Framework\TestCase;
use Classes\Factories\NotificationFactory;
use Classes\Notifications\InformativeNotification;
use Classes\Notifications\UrgentNotification;

class NotificationFactoryTest extends TestCase
{
    public function testCreateInformativeNotification()
    {
        $expiration = new \DateTime('+1 day');
        $notification = NotificationFactory::createNotification('informative', 1, 'Test Informative Notification', $expiration);
        $this->assertInstanceOf(InformativeNotification::class, $notification);
    }

    public function testCreateUrgentNotification()
    {
        $expiration = new \DateTime('+1 day');
        $notification = NotificationFactory::createNotification('urgent', 1, 'Test Urgent Notification', $expiration);
        $this->assertInstanceOf(UrgentNotification::class, $notification);
    }

    public function testCreateUnknownNotification()
    {
        $this->expectException(\Exception::class);
        $expiration = new \DateTime('+1 day');
        NotificationFactory::createNotification('unknown', 1, 'Test Unknown Notification', $expiration);
    }

    public function testCreateNotificationWithInvalidExpirationDate()
    {
        $this->expectException(\Exception::class);
        NotificationFactory::createNotification('informative', 1, 'Test Informative Notification', new \DateTime('-1 day'));
    }
}
