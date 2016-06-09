<?php 

return [
/* IMPORTANT:
   * It just accepts mysql connections
   * Nothing will be writen in that DB
   * the user from this configuration just need "Select" permissions
     on the target table
 
 * This is the connection information to the external database.
 * From this database will be consulted the user information in order
 * to copy theirs information to the the application's database and
 * log them in with this credentials.
 * In this way the applicacion could use other database to log in the users.
*/
	'extDb'=>[
		'driver' => 'mysql',
		'host' => 'localhost',
		'database' => 'external',
		'username' => 'external',
		'password' => 'external',
		'charset' => 'utf8',
		'collation' => 'utf8_unicode_ci',
		'prefix' => '',

		'tablesInfo'=>[
			// This options are specific for the table where the user will be readen from
			'usersTable' => 'phpbb_users',       // Name of the table witht he user data
			'usernameField' => 'username',       // name of username's field
			'passwordField' => 'user_password',  // name password's field
			'saltField' => false,               // name passwords salts's field or false if not used
			'emailField' => 'user_email',        // name of emails's field
			'passMethod' => 'phpbb',             // name of the encryption method used to save the
											 // passwords in that db, it normaly depends on the system
											 // of the external DB. Some accepted parameters are:
											 // 'php' is for "password_hash" from php
											 //     Forum systems and CMS
											 // 'md5' , 'phpbb', mybb', 'drupal', 'punbb/fluxbb'
											 // 'joomla', 'crypt', 'sha256', 'sha1'
		]
	]
];

