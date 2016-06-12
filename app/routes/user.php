<?php
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

$app->get('/user/{username}', 'UserController:profile')->setName('user.profile');
$app->get('/lang', 'UserController:getSetLang');
$app->post('/lang', 'UserController:setLang')->setName('setLang');
