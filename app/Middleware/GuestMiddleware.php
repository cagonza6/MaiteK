<?php

namespace App\Middleware;

class GuestMiddleware extends Middleware{

	protected $container;

	public function __invoke($request, $response, $next ){

		if($this->container->auth->check()){
			$this->container->flash->addMessage('info', 'This function is not accessible for logged in users.');
			return $response->withRedirect($this->container->router->pathFor('home'));
		}

		$response = $next($request, $response);
		return $response;
	}

}
