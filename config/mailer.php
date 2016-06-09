<?php 
// Basic configurations to use gmail as email sender.
return [
	'mailer'=>[
		'IsSMTP' => true,
		'smtp_auth' => true,
		'smtp_secure' => 'tls',
		'host' => 'smtp.gmail.com',
		'username' => 'email@gmail.com',
		'password' => 'emailpassword',
		'port' => 587,
		'html' => true,
		'debugMode' => true,
	]
];

?>
