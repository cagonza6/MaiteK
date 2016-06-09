<?php 

/* These are the main options needed to fill data and show names and some basic functionalities */
return [
	'app' => [
		'webConf'=>[
			'baseURI'       => 'http://localhost', // The internet address where the aplicacion is hosted. ex: http://www.example.com
			'defLanguage'   => 'en_US',        // Default language if something fails
			'languages'     => [
								'en_US'=>'English',
								'es_CL'=>'Español'
								],      // languages availables for the app
			'appName'       => 'MAITEK',       // Name of the homepage
			'appSubName'    => 'My Application Intended To Exchange Knowledge', // secondary name
			'appEmailUser'  => 'My page',      // Name of who sends the emails from the app.
			'appEmail'      => 'm@i.te',       // Email account from where the emails wil be send
			'timeZone'      => 'Europe/Berlin',// timezone of the server
			'timeFormat'    => 'd-M-Y',        // time format for dates
			'trackerName'   => 'Bug Jar',
	],

	'userEmailConfirm'=> true,          // do users need to confirm their account by email?
	'useExtDb'      => false,           // this allows the to use an external DB for the login, see db_external.php
	'ExtDbconfirm'  => false,           // should the accounts from the external DB be confirmed by email after transfered?
	'debugMode'     => true,            // activates the php option to show of errors, deactivate for production!!!.

	'teams'=>[
		'defaultTeam' => 0,
		0 => 'None',
		1 => 'Admins',
		2 => 'Leaders',
		3 => 'Moderators',
		4 => 'Colaborators',
		5 => 'Users'
	],

	'roles'=> include 'usersRoles.php', // check this file in order to modify the existing roles


// Do not edit beyond this point if you do not know what it does


	'genders'=>[
		'n' => 'Not telling',
		'm' => 'Male',
		'f' => 'Female'
	],

	'sessionName'=> 'MaiteKSession', // Session Name
/*
 * Secondary options intended to make more flexible some parameters for
 * user registration, this are valid just for stand alone operation
 * (when not using external DB).
 * Do not change anithing if you don't know excactly what you do.
*/
	'hash'=>[
		'algo'=>PASSWORD_BCRYPT,
		'cost'=>10
	],
	
	// Parameters for username and password conditions
	'usernameLimits'    => [
		'MinLength'    => 5,						// Minimum username length.
		'MaxLength'    => 32,						// Maximum username length.
	],
	'passwordLimits'    => [
		'MinLength'    => 5,						// Minimum password length.
		'MaxLength'    => 128,						// Maximum password length.
	],
 ],
];


?>
