<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Models\User;

// Home page
$app->get('/', 'HomeController:index')->setName('home');

include_once 'routes/core.php';
include_once 'routes/user.php'; // User routes: profile

		// Applications
include_once 'routes/applications/tracker.php'; // tracker
