<?php

namespace App\Controllers\Auth;

use \App\Models\User;
use \App\Controllers\Controller;
use \Respect\Validation\Validator as v;

class EmailController extends Controller{

	public function getChangeEmailRequest($request, $response){
		if ($this->config->get('app.useExtDb'))
			return $response->withRedirect($this->router->pathFor('home'));
		return $this->view->render($response, 'auth/email/change.twig');
	}

	public function postChangeEmailRequest($request, $response){

		$password = $request->getParam('password');
		$email = $request->getParam('email');
		$email_confirm = $request->getParam('email_confirm');
		$user = $this->auth->user();

		$validation = $this->validator->validate($request, [
			'password' => v::noWhitespace()->notEmpty()->matchesPassword($user->password),
			'email'=> v::noWhitespace()->notEmpty()->email()->emailAvailable(),
			'email_confirm' => v::ConfirmField($email),
		]);

		if ($validation->failed()){
			return $response->withRedirect($this->router->pathFor('email.change'));
		}

		$identifier = $this->randomlib->generateString(128);
		User::createEmailchangeToken($user->id, $user->email, $email, $this->hasher->hash($identifier));

		$this->mail->sendConfirmChangeEmail($response, $email, ['user'=>$user, 'email'=>$email, 'token'=>$identifier]);

		$this->flash->addMessage('info', 'Please check the new email in order to confirm the change.');
		return $response->withRedirect($this->router->pathFor('home'));

	}

	public function getChangeEmailConfirm($request, $response){

		$email = $request->getParam('email');
		$identifier = $request->getParam('token');

		$hashedIdentifier = $this->hasher->hash($identifier);

		$validation = $this->validator->validate($request,
			[
				'email'=> v::noWhitespace()->notEmpty()->email()->emailAvailable(),
		]);

		$user =  User::getEmailChangeToken($email);

		if ($user->old_email != $this->auth->user()->email){
			$this->flash->addMessage('error', 'Your old mail does not maches our records.');
			return $response->withRedirect($this->router->pathFor('home'));
			}

		if (!$this->hasher->hashCheck($user->token, $hashedIdentifier)) {
			$this->flash->addMessage('error', 'There was a problem while changing your email.');
		}
		else {
			User::setEmail($user->user_id, $email);
			User::deleteEmailChangeToken($user->user_id);
			$this->flash->addMessage('info', 'Your Email has being Changed.');
		}
		return $response->withRedirect($this->router->pathFor('home'));

	}

}
