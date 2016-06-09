<?php

namespace App\Middleware;

class CaptchaMiddleware extends Middleware{

	protected $container;

	public function __invoke($request, $response, $next ){

		$this->container['captchaCode'] = $this->container->session->captchaCode;

		$this->container->session->captchaCode = $this->container->captcha->getPhrase();

		$response = $next($request, $response);
		return $response;
	}

}
