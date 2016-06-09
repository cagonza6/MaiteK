<?php

namespace App\Middleware;

class ValidationErrorsMiddleware extends Middleware{

	protected $container;

	public function __invoke($request, $response, $next ){

		$this->container->view->getEnvironment()->addGlobal('errors', $this->container->session->errors);
		$this->container->session->errors = null;

		$response = $next($request, $response);
		return $response;
	}

}
