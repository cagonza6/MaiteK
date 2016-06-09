<?php

namespace App\Middleware;

class AuthMiddleware extends Middleware{

	protected $container;

	public function __invoke($request, $response, $next ){

		if(!$this->container->auth->check()){

			$this->container->flash->addMessage('info', 'Please sign in before accesing here.');
			return $response->withRedirect($this->container->router->pathFor('auth.signin'));
		}

		$response = $next($request, $response);
		return $response;
	}

}
