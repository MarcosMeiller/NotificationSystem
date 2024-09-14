<?php
namespace Classes;

class Topic
{
    public $id;
    public $name;
    public $subscribers = [];

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addSubscriber($userId)
    {
        $this->subscribers[] = $userId;
    }

    public function getSubscribers()
    {
        return $this->subscribers;
    }
}
