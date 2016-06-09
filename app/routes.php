<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Models\User;

// Things that everyone can access
$app->get('/', 'HomeController:index')->setName('home');
$app->get('/activate', 'AuthController:getActivateAccount' )->setName('activate');

	// Track open optins
$app->get('/tracker[/status/]', 'TrackerController:index' )->setName('tracker.index');
$app->get('/tracker/userIssues/', 'TrackerController:userIssues' )->setName('tracker.myIssues');
$app->get('/tracker/view[/{id}]', 'TrackerController:getViewIssue' )->setName('tracker.viewIssue');

	// user
	include_once 'routes/user.php';

// group for unsigned in users, if the user already signed in, they can not access here.
$app->group('', function(){

// sign up
	$this->get('/auth/signup', 'AuthController:getSignUp')->setName('auth.signup');
	$this->post('/auth/signup', 'AuthController:postSignUp');
// sign in
	$this->get('/auth/signin', 'AuthController:getSignIn')->setName('auth.signin');
	$this->post('/auth/signin', 'AuthController:postSignIn');

	// Pasword recovery
	include_once 'routes/password.php';

})->add(new GuestMiddleware($container));

// group for singed in users
$app->group('', function(){

	// Email options: change email
	include_once 'routes/email.php';

	// sign outh method
	$this->get('/auth/signout', 'AuthController:getSignOut')->setName('auth.signout');
	$this->get('/auth/password/change', 'PasswordController:getChangePassword')->setName('auth.password.change');
	$this->post('/auth/password/change', 'PasswordController:postChangePassword');

	// Tracker
	$this->get('/tracker/new', 'TrackerController:getNewIssue' )->setName('tracker.newIssue');
	$this->post('/tracker/new', 'TrackerController:postNewIssue' );
	$this->post('/tracker/view[/{id}]', 'TrackerController:postInViewIssue' )->setName('tracker.viewIssue.post');
		// edit issue
	$this->get('/tracker/edit[/{id}]', 'TrackerController:getEditIssue' )->setName('tracker.editIssue');
	$this->post('/tracker/edit[/{id}]', 'TrackerController:postEditIssue' );
		// delete issue
	$this->post('/tracker/delete[/{id}]', 'TrackerController:postDeleteIssue' )->setName('tracker.deleteIssue');
	$this->get('/tracker/delete[/{id}]', 'TrackerController:getDeleteIssue' );
		// Ddit comment
	$this->get('/tracker/comment/edit[/{id}]', 'TrackerController:getEditComment' )->setName('tracker.editComment');
	$this->post('/tracker/comment/edit[/{id}]', 'TrackerController:postEditComment' );
		// Delete comment
	$this->get('/tracker/comment/delete[/{id}]', 'TrackerController:getDeleteComment' )->setName('tracker.deleteComment');
	$this->post('/tracker/comment/delete[/{id}]', 'TrackerController:postDeleteComment' );

})->add(new AuthMiddleware($container));
