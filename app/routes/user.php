<?php
use \App\Models\User;

// actions after the email is received
$app->get('/user/{username}', function($request, $response, $args){

	$username = $args['username'];
	$user = User::loadUser($username);
	if(!$user->username){
		$this->view->render($response, 'errors/404.twig');
		return $response->withStatus(404); 
	}
	$this->view->render($response, 'user/profile.twig',['user'=>$user,
		'avatar'=>User::gravatarAvatar($user->email),
		'appConf'=>$this->config->get('app')
	]);

})->setName('user.profile');
