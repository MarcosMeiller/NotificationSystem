<?php

use PHPUnit\Framework\TestCase;
use Classes\Topic;
use Classes\User;

class TopicTest extends TestCase
{
    private $topic;
    private $user;

    protected function setUp(): void
    {
        $this->topic = new Topic(1, 'Tech News');
        $this->user = new User(1, 'Jonh Doe', 'doe@example.com');
    }

    public function testAddSubscriber()
    {
        $this->topic->addSubscriber($this->user->getId());
        $subscribers = $this->topic->getSubscribers();
        $this->assertContains($this->user->getId(), $subscribers);
    }
}
