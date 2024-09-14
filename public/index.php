<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Classes\Factories\NotificationFactory;
use Classes\User;
use Classes\Topic;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

session_start();
function initializeData() {
    $_SESSION['users'] = [];
    $_SESSION['topics'] = [];
    $_SESSION['notifications'] = [];

    $_SESSION['users'][1] = new User(1, 'test1', 'test1@example.com');
    $_SESSION['users'][2] = new User(2, 'test2', 'test2@example.com');
    $_SESSION['users'][3] = new User(3, 'test3', 'test3@example.com');
    $_SESSION['users'][4] = new User(4, 'test4', 'test4@example.com');
    $_SESSION['users'][5] = new User(5, 'test5', 'test5@example.com');

    $_SESSION['topics'][1] = new Topic(1, 'Tech News');
    $_SESSION['topics'][2] = new Topic(2, 'Sports Updates');
    $_SESSION['topics'][3] = new Topic(3, 'Entertainment');

    $expiration = new \DateTime('+1 day');
    $_SESSION['notifications'][1] = NotificationFactory::createNotification('informative', 1, 'Tech Article', $expiration);
    $_SESSION['notifications'][2] = NotificationFactory::createNotification('urgent', 2, 'Sports Event now', $expiration);
    $_SESSION['notifications'][3] = NotificationFactory::createNotification('informative', 3, 'New Movie', $expiration);
    $_SESSION['notifications'][4] = NotificationFactory::createNotification('urgent', 1, 'Event Tech Tomorrow!', $expiration);
    // Asignar notificaciones a usuarios
    foreach ($_SESSION['users'] as $user) {
        $user->addNotification($_SESSION['notifications'][1]);
        $user->addNotification($_SESSION['notifications'][2]);
        $user->addNotification($_SESSION['notifications'][3]);
        $user->addNotification($_SESSION['notifications'][4]);
    }

    $_SESSION['topics'][1]->addSubscriber(1);
    $_SESSION['topics'][1]->addSubscriber(2);
    $_SESSION['topics'][2]->addSubscriber(3);
    $_SESSION['topics'][3]->addSubscriber(4);

    $_SESSION['users'][1]->addSubscription($_SESSION['topics'][1]);
    $_SESSION['users'][2]->addSubscription($_SESSION['topics'][1]);
    $_SESSION['users'][3]->addSubscription($_SESSION['topics'][2]);
    $_SESSION['users'][4]->addSubscription($_SESSION['topics'][3]);
}

if (empty($_SESSION['users'])) {
    initializeData();
}

$app->get('/reset', function (Request $request, Response $response) {
    session_destroy();
    session_start();
    initializeData();

    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/users', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode($_SESSION['users']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->post('/users', function (Request $request, Response $response) {
    $parsedBody = $request->getParsedBody();
    $name = $parsedBody['name'] ?? null;
    $email = $parsedBody['email'] ?? null;

    if ($name && $email) {
        $userId = count($_SESSION['users']) + 1;
        $newUser = new User($userId, $name, $email);
        $_SESSION['users'][$userId] = $newUser;
        $response->getBody()->write(json_encode(["status" => "User registered"]));
    } else {
        $response = $response->withStatus(400);
        $response->getBody()->write(json_encode(["status" => "Invalid input"]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/users/{id}/notifications', function (Request $request, Response $response, array $args) {
    $userId = $args['id'];

    if (!isset($_SESSION['users'][$userId])) {
        return $response->withStatus(404)->write('User not found');
    }

    $user = $_SESSION['users'][$userId];
    $notifications = $user->getUnreadNotifications();

    $response->getBody()->write(json_encode($notifications));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


$app->get('/topics', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode($_SESSION['topics']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->post('/topics', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $topicId = count($_SESSION['topics']) + 1;
    $newTopic = new Topic($topicId, $data['name']);
    $_SESSION['topics'][$topicId] = $newTopic;

    $response->getBody()->write(json_encode(['status' => 'Topic created', 'id' => $topicId]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

$app->post('/topics/{topicId}/subscribe', function (Request $request, Response $response, array $args) {
    $topicId = $args['topicId'];
    $data = $request->getParsedBody();
    $userId = $data['userId'];

    if (!isset($_SESSION['users'][$userId])) {
        return $response->withStatus(404)->write('User not found');
    }

    if (!isset($_SESSION['topics'][$topicId])) {
        return $response->withStatus(404)->write('Topic not found');
    }

    $user = $_SESSION['users'][$userId];
    $topic = $_SESSION['topics'][$topicId];

    $topic->addSubscriber($userId);
    $user->addSubscription($topic);

    $response->getBody()->write(json_encode(['status' => 'User subscribed to topic']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->post('/topics/{topicId}/notifications/user', function (Request $request, Response $response, array $args) {
    $topicId = $args['topicId'];
    $data = $request->getParsedBody();
    $userId = $data['userId'];

    if (!isset($_SESSION['users'][$userId])) {
        return $response->withStatus(404)->write('User not found');
    }

    $user = $_SESSION['users'][$userId];
    $notification = NotificationFactory::createNotification(
        $data['type'],
        count($_SESSION['notifications']) + 1,
        $data['message'],
        new \DateTime($data['expiration'])
    );

    $_SESSION['notifications'][] = $notification;
    $user->addNotification($notification);

    $response->getBody()->write(json_encode(['status' => 'Notification sent to specific user']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

$app->post('/topics/{topicId}/notifications', function (Request $request, Response $response, array $args) {
    $topicId = $args['topicId'];
    $data = $request->getParsedBody();

    if (!isset($_SESSION['topics'][$topicId])) {
        return $response->withStatus(404)->write('Topic not found');
    }

    $topic = $_SESSION['topics'][$topicId];
    $notification = NotificationFactory::createNotification(
        $data['type'],
        count($_SESSION['notifications']) + 1,
        $data['message'],
        new \DateTime($data['expiration'])
    );

    $_SESSION['notifications'][] = $notification;

    foreach ($topic->getSubscribers() as $userId) {
        $user = $_SESSION['users'][$userId];
        $user->addNotification($notification);
    }

    $response->getBody()->write(json_encode(['status' => 'Notification sent to all subscribers']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

$app->post('/notifications/read', function (Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    $userId = $data['userId'];
    $notificationId = $data['notificationId'];

    if (!isset($_SESSION['users'][$userId])) {
        return $response->withStatus(404)->write('User not found');
    }

    $user = $_SESSION['users'][$userId];
    $marked = $user->markNotificationAsRead($notificationId);

    if ($marked) {
        $response->getBody()->write(json_encode(["status" => "Notification marked as read"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(["status" => "Notification not found"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
});


$app->run();
