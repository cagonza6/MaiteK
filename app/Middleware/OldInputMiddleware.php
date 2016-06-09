<?php

namespace App\Middleware;

class OldInputMiddleware extends Middleware{

	protected $container;

	public function __invoke($request, $response, $next ){

		$this->container->view->getEnvironment()->addGlobal('old', $this->container->session->old?$this->container->session->old:[]);

		$this->container->session->old = $request->getParams();
		$response = $next($request, $response);
		return $response;
	}

}
