<?php

require __DIR__. '/../vendor/autoload.php';

use App\Email\Mailer;
use App\Helpers\Hasher;
use RandomLib\Factory as RandomLib;
use Gregwar\Captcha\CaptchaBuilder;
use Respect\Validation\Validator as v;

// Use the ridiculously long Symfony namespaces
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;

$app = new \Slim\App([
	'settings' => [
		'displayErrorDetails'=>true,
	]
]);

$container = $app->getContainer();

$container['config'] = function ($container){
	return new Noodlehaus\Config([
		'../config/application.php',
		'../config/db.php',
		'../config/db_external.php',
		'../config/mailer.php',
		// addons
		'../config/track.php',
	]);
};

if($container->config->get('app.debugMode')){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

date_default_timezone_set($container->config->get('app.webConf.timeZone'));

$Database = new \App\Database\Database();
$Database->addConnection($container->config->get('db'));

$user = new \App\models\User();

$container['database'] = function ($container) use ($Database){
	return $Database;
};

$session = new \RKA\Session();
$container['session'] = function ($container) use($session){
	return $session;
};

$container['sessionManager'] = function ($container){
	$session = new \RKA\SessionMiddleware(['name' => $container->config->get('app.sessionName')]);
	$session->start();
	return $session;
};

           // i18n support
$container['translator'] = function ($container){
	$defaultLang = $container->config->get('app.webConf.defLanguage');

	// First try to get the users lang, if fails taked the session lang
	if (!($lang = $container->auth->user()->lang)){
		if(!($lang = $container->session->lang)){
			// if fails takes the default lang, as last chanse
			$lang = $defaultLang;
		}
	}
	$translator = new Translator($lang,  new MessageSelector());
	$translator->setFallbackLocales([$defaultLang]);
	$translator->addLoader('php', new PhpFileLoader());
	$translator->addResource('php', __DIR__.'/../resources/lang/core/'.$defaultLang.'.php', $defaultLang); // English - Default

	if ($lang && array_key_exists($lang, $container->config->get('app.webConf.languages')) && $lang != $defaultLang )
		$translator->addResource('php', __DIR__.'/../resources/lang/core/'.$lang.'.php', $lang); // User lang

	return $translator;
};

$container['view'] = function($container){
	$view = new \Slim\Views\Twig(__DIR__.'/../resources/views',[
		'cache' => false,
	]);

	$view->addExtension(
		new \Slim\Views\TwigExtension(
			$container->router,
			$container->request->getUri()
	));

	$view->addExtension(
		new TranslationExtension($container->translator)
	);

	$view->getEnvironment()->addGlobal('auth', [
		'check'=>$container->auth->check(),
		'user' => $container->auth->user(),
		'baseURI' => $container->config->get('app.webConf.baseURI')
		]);

	$view->getEnvironment()->addGlobal('flash', $container->flash);
	$view->getEnvironment()->addGlobal('captcha', $container->captcha);
	$view->getEnvironment()->addGlobal('markDownParser', $container->MarkdownParser);

	// general configurations, such as app name, email of the app, and etc.
	$view->getEnvironment()->addGlobal('useExtDb', $container->config->get('app.useExtDb'));
	$view->getEnvironment()->addGlobal('appConfig', $container->config->get('app.webConf'));

	return $view;
};

$container['flash'] = function ($container){
	return new \Slim\Flash\Messages;
};

$container['auth'] = function ($container){
	return new \App\Auth\Auth($container->session);
};

$container['validator'] = function ($container){
	return new \App\Validation\Validator($container->session);
};

$container['HomeController'] = function ($container){
	return new \App\Controllers\HomeController($container);
};

$container['UserController'] = function ($container){
	return new \App\Controllers\UserController($container);
};

$container['AuthController'] = function ($container){
	return new \App\Controllers\Auth\AuthController($container);
};

$container['PasswordController'] = function ($container){
	return new \App\Controllers\Auth\PasswordController($container);
};

$container['EmailController'] = function ($container){
	return new \App\Controllers\Auth\EmailController($container);
};

$container['csrf'] = function ($container){
	return new \Slim\Csrf\Guard;
};

$container['mail'] = function ($container){
	$mailer = new PHPMailer();

	if ($container->config->get('mailer.IsSMTP') === true)
		$mailer->IsSMTP();

	$mailer->setFrom($container->config->get('app.webConf.appEmail'), $container->config->get('app.webConf.appEmailUser'), false);
	$mailer->AddReplyTo($container->config->get('app.webConf.appEmail'), $container->config->get('app.webConf.appEmailUser'));
	$mailer->Host = $confs = $container->config->get('mailer.host');
	$mailer->SMTPAuth = $confs = $container->config->get('mailer.smtp_auth');
	$mailer->SMTPSecure = $confs = $container->config->get('mailer.smtp_secure');
	$mailer->Port = $confs = $container->config->get('mailer.port');
	$mailer->Username = $confs = $container->config->get('mailer.username');
	$mailer->Password = $confs = $container->config->get('mailer.password');
	$mailer->isHTML = $confs = $container->config->get('mailer.html');
	$mailer->SMTPDebug = $confs = $container->config->get('mailer.debugMode');

	return new Mailer($container->view, $mailer);

};

$container['randomlib'] = function ($container){
	$factory = new RandomLib;
	return $factory->getMediumStrengthGenerator();
};

$container['hasher'] = function ($container){
	return new Hasher($container->config->get('app.hash'));
};

$container['notFoundHandler'] = function ($container) {
	return function ($request, $response) use ($container) {
		$container->view->render($response, 'errors/404.twig');
		return $response->withStatus(404);
	};
};

$container['captcha'] = function ($container) {
	$builder =  new CaptchaBuilder;
	$builder->setIgnoreAllEffects(true);
	$builder->build();
	return $builder;
};

$container['MarkdownParser'] = function ($container) {
// use github markdown
	$parser = new \cebe\markdown\GithubMarkdown();
	return $parser;
};

// add your modules here
$container['TrackerController'] = function ($container){
	$Tracker=  new \App\Controllers\TrackerController($container);
	$Tracker->setConfig($container->config->get('tracker'));
	return $Tracker;
};


$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));
$app->add(new \App\Middleware\CaptchaMiddleware($container));
$app->add($container->sessionManager);
$app->add($container->csrf);

v::with('App\\Validation\\Rules\\');
require __DIR__ . '/../app/routes.php';
