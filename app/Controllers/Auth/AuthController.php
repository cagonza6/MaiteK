<?php

namespace App\Controllers\Auth;

use \App\Models\User;
use App\Database\ExtDb;
use \App\Controllers\Controller;
use \Respect\Validation\Validator as v;

use \App\Externals\vanillaForums\Gdn_PasswordHash;

class AuthController extends Controller{

	public function getSignOut($request, $response){

		$this->auth->logout();
		return $response->withRedirect($this->router->pathFor('home'));
	}

	public function getSignIn($request, $response){
		return $this->view->render($response, 'auth/signin.twig');
	}

	public function postSignIn($request, $response){
		$username = $request->getParam('username');
		$password = $request->getParam('password');

		$auth = $this->auth->attempt(
			$username ,
			$password
		);

		// just check for the external db when the wuth fails
		if($this->config->get('app.useExtDb')){

				$extDB = new ExtDb($this->config->get('extDb'));
				$extUser = $extDB->selectExtUser($username);
				$localUser = User::loadUser($username);

				$externalExists = $extUser->username;
				$localExists = $localUser->user_id?true:false;

			// here enter just the users that could not be loged in with the credentias in MaiteK
			if(!$auth){
				// if the user exists in the the external DB and not in the local
				// it need to be created
				$ExtAuth = new Gdn_PasswordHash();
				if ($externalExists && !$localExists) {
					$method = $this->config->get('extDb.tablesInfo.passMethod');

					//and can login there the credentials given here, we create a new user here with those credentials
					if($ExtAuth->checkPassword($password, $extUser->password, $method, $username, $extUser->salt)){

						$userID = User::createUser(
							$username,
							$this->hasher->hashPassword($password),
							$extUser->email,
							!(bool)$this->config->get('app.ExtDbconfirm'),
							(int)$this->config->get('app.roles.defaultRole'),
							(int)$this->config->get('app.teams.defaultTeam')
						);
						if ((bool)$this->config->get('app.ExtDbconfirm')){

							$identifier = $this->randomlib->generateString(128);
							User::generateAccountToken($userID, $extUser->email, $this->hasher->hash($identifier));

							$this->mail->sendConfirmAccount($response, $extUser->email, ['email'=>$extUser->email, 'token'=>$identifier]);
							$this->flash->addMessage('info', 'This is the first time you start a session our system. Your account has been transferd, but you still need to activete it by confirm with the email we send you. Please check your email and SPAM filter.');
						}
						else{
							// autoactivate the account since we created it just yet
							User::activateAccount($userID );
							// and we loged him/her here
							$auth = $this->auth->attempt($username, $password);
						}
					}
					else
						$auth = false;
				}
				// if the user exists in both places and can login in the external DB
				// we update the passwor in MaiteK since the external DB is the main one.
				elseif ($externalExists && $localExists){
					// and the credentials from the external DB are correct
					if($ExtAuth->checkPassword($password, $extUser->password, $method, $username, $extUser->salt)){
						// updates the password
						User::setPassword($localUser->user_id, $this->hasher->hashPassword($password));
						$auth = $this->auth->attempt($username, $password);
					}
				}
			}
			if($auth){
				// it updates the required parameters of the users just
				// if their do not match the main DB
					if($extUser->email!= $localUser->email)
						User::setEmail($localUser->user_id, $extUser->email);
				}
		}

		// this is the only different response since there is no ban option yet,
		if ($auth === null){
			$this->flash->addMessage('error', 'You can not login since your account has not been activated.');
			return $response->withRedirect($this->router->pathFor('home'));
		}

		if(!$auth){
			$this->flash->addMessage('error', 'Error in username or password.');
			return $response->withRedirect($this->router->pathFor('auth.signin'));
		}

		$this->flash->addMessage('info', 'You have been singed in.');
		return $response->withRedirect($this->router->pathFor('home'));
	}

	public function getSignUp($request, $response){
		if ($this->config->get('app.useExtDb'))
			return $response->withRedirect($this->router->pathFor('home'));
		return $this->view->render($response, 'auth/signup.twig');
	}

	public function postSignUp($request, $response){

		$email = $request->getParam('email');
		$password = $request->getParam('password');
		$username = $request->getParam('username');
		$captcha = $request->getParam('captcha');

		$validation = $this->validator->validate($request,
			[
				'username'=> v::notEmpty()->notEmpty()->noWhitespace()->UsernameAvailable()->length(
					$this->config->get('app.usernameLimits.MinLength'),
					$this->config->get('app.usernameLimits.MaxLength')
					),
				'email'=> v::noWhitespace()->notEmpty()->email()->emailAvailable(),
				'email_confirm'=> v::ConfirmField($email),
				'password'=> v::noWhitespace()->notEmpty()->length(
					$this->config->get('app.passwordLimits.MinLength'),
					$this->config->get('app.passwordLimits.MaxLength')
					),
				'password_confirm'=> v::ConfirmField($password),
				'captcha'=> v::ConfirmField($this->captchaCode),
		]);

		if($validation->failed()){
			return $response->withRedirect($this->router->pathFor('auth.signup'));
		}

		$userID = User::createUser(
			$username,
			$this->hasher->hashPassword($password),
			$email,
			!(bool)$this->config->get('app.ExtDbconfirm'),
			(int)$this->config->get('app.roles.defaultRole'),
			(int)$this->config->get('app.teams.defaultTeam')
		);

		$identifier = $this->randomlib->generateString(128);
		User::generateAccountToken($userID, $email, $this->hasher->hash($identifier));

		$this->mail->sendConfirmAccount($response, $email, ['email'=>$email, 'token'=>$identifier]);

		//not really necessary to sign up automatically, I do not like that.
		// it will be removed
		$this->flash->addMessage('info', 'You have been singed up.');
		//$this->auth->attempt($username, $request->getParam('password'));

		return $response->withRedirect($this->router->pathFor('home'));
	}

	public function getActivateAccount($request, $response){

		$email = $request->getParam('email');
		$identifier = $request->getParam('token');

		$hashedIdentifier = $this->hasher->hash($identifier);

		$user =  User::getUserCreationToken($email);

		if (!$user->user_id || !$this->hasher->hashCheck($user->token, $hashedIdentifier)) {
			$this->flash->addMessage('error', 'There was a problem activating your account.');
		} else {
			User::activateAccount($user->user_id);
			User::deleteAccountToken($user->user_id);
			$this->flash->addMessage('info', 'Your account has been activated and you can sign in.');
		}
		return $response->withRedirect($this->router->pathFor('home'));

	}
}
