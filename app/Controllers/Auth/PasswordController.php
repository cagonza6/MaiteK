<?php

namespace App\Controllers\Auth;

use \App\Models\User;
use \App\Controllers\Controller;
use \Respect\Validation\Validator as v;

class PasswordController extends Controller{

	public function getChangePassword($request, $response){
		if ($this->config->get('app.useExtDb'))
			return $response->withRedirect($this->router->pathFor('home'));
		return $this->view->render($response, 'auth/password/change.twig');
	}

	public function postChangePassword($request, $response){

		$password = $request->getParam('password');
		$validation = $this->validator->validate($request, [
			'password_old' => v::noWhitespace()->notEmpty()->matchesPassword($this->auth->user()->password),
			'password' => v::noWhitespace()->notEmpty()->length(
					$this->config->get('app.passwordLimits.MinLength'),
					$this->config->get('app.passwordLimits.MaxLength')
					),
			'password_confirm' => v::ConfirmField($password),
		]);

		if ($validation->failed($password)){

			return $response->withRedirect($this->router->pathFor('auth.password.change'));

		}

		User::setPassword($this->auth->user()->id, $this->hasher->hashPassword($password));
		$this->flash->addMessage('info', 'Your password was changed.');
		return $response->withRedirect($this->router->pathFor('home'));

	}

	public function getResetPasswordForm($request, $response){
		$email = $request->getParam('email');
		$token = $request->getParam('token');

		if (!$email || !$token){
			$this->flash->addMessage('error', 'We found a problem with the link. Please contact the admin.');
			return $response->withRedirect($this->router->pathFor('home'));
		}
		$this->view->render($response, 'auth/password/reset.twig',['email' => $email, 'token' => $token]);
	}

	public function postResetPasswordConfirm($request, $response){

		$email = $request->getParam('email');
		$identifier = $request->getParam('token');
		$password = $request->getParam('password');

		$hashedIdentifier = $this->hasher->hash($identifier);

		$validation = $this->validator->validate($request,
			[
				'password'=> v::noWhitespace()->notEmpty(),
				'password_confirm'=> v::ConfirmField($password),
		]);


		if($validation->failed()){
			return $response->withRedirect($this->router->pathFor('password.reset', ['email'=>$email,'token'=>$identifier]));
		}

		$user =  User::getRecoveryToken($email);
		if (!$user->user_id || !$this->hasher->hashCheck($user->token, $hashedIdentifier)) {
			$this->flash->addMessage('error', 'There was a problem reseting your password.');
		} else {
			User::setPassword($user->user_id, $this->hasher->hashPassword($password));
			User::deleteRecoverryToken($user->user_id);
			$this->flash->addMessage('info', 'Your password has being reseted.');
		}
		return $response->withRedirect($this->router->pathFor('home'));

	}


	public function getRecoverPasswordRequest($request, $response){
		$this->view->render($response, 'auth/password/recover.twig');
	}

	public function postRecoverPasswordConfirm($request, $response){

		$email = $request->getParam('email');

		// validate the email
		$validation = $this->validator->validate($request, [
			'email'=> v::noWhitespace()->notEmpty()->email(),
		]);

		if ($validation->failed($email)){
			$this->flash->addMessage('error', 'Invalid Email.');
			return $response->withRedirect($this->router->pathFor('password.recover'));
		}

		$user = User::searchbyEmail($email);
		if (!$user->id){
			$this->flash->addMessage('error', 'User not Found.');
			return $response->withRedirect($this->router->pathFor('password.recover'));
			}

		$identifier = $this->randomlib->generateString(128);
		User::generateRecoverryToken($user->id, $user->email, $this->hasher->hash($identifier));

		$this->mail->sendPasswordRecovery($response, $email, ['email'=> $user->email, 'token'=>$identifier]);

		$this->flash->addMessage('info', 'Please check your email and the spam filter.');
		return $response->withRedirect($this->router->pathFor('home'));

	}

}
