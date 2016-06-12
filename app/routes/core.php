<?php
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

// Just for NON-loged in users
$app->group('', function(){

// Account
	// Activate
	$this->get('/activate', 'AuthController:getActivateAccount' )->setName('activate');
	// Create
	$this->get('/auth/signup', 'AuthController:getSignUp')->setName('auth.signup');
	$this->post('/auth/signup', 'AuthController:postSignUp');

// Login
	$this->get('/auth/signin', 'AuthController:getSignIn')->setName('auth.signin');
	$this->post('/auth/signin', 'AuthController:postSignIn');

// Password
	// Recovery
	$this->get('/password/recover', 'PasswordController:getRecoverPasswordRequest')->setName('password.recover');
	$this->post('/password/recover', 'PasswordController:postRecoverPasswordConfirm');
	// Reset
	$this->get('/password/reset', 'PasswordController:getResetPasswordForm')->setName('password.reset');
	$this->post('/password/reset', 'PasswordController:postResetPasswordConfirm');

})->add(new GuestMiddleware($container));


// group for singed in users
$app->group('', function(){

// Log out
	$this->get('/auth/signout', 'AuthController:getSignOut')->setName('auth.signout');

// Email options
	// change
	$this->get('/email/change', 'EmailController:getChangeEmailRequest')->setName('email.change');
	$this->post('/email/change', 'EmailController:postChangeEmailRequest');
	// confirm
	$this->get('/email/confirm', 'EmailController:getChangeEmailConfirm')->setName('email.change.confirm');

// Password
	$this->get('/auth/password/change', 'PasswordController:getChangePassword')->setName('auth.password.change');
	$this->post('/auth/password/change', 'PasswordController:postChangePassword');

})->add(new AuthMiddleware($container));
