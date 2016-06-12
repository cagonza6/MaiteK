<?php

namespace App\Controllers;

use App\Models\User;
class UserController extends Controller{

	public function profile($request, $response, $args){
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
	}

	public function setLang($request, $response, $args){
		$lang = $request->getParam('lang');
		if (array_key_exists($lang, $this->config->get('app.webConf.languages'))){
			if ($this->auth->check())
				User::setLang($this->auth->user()->id, $lang);
			$this->session->lang = $lang;
		}
		return $this->getSetLang($request, $response, $args);
	}

	public function getSetLang($request, $response, $args){
		return $response->withRedirect($this->router->pathFor('home'));
	}

}
