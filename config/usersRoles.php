<?php 
$GUEST = 0;
$USER = 1;
$MODERATOR = 2;
$LEADER = 3;
$ADMIN= 4;
//Helpers

$roleLevel = [
	$GUEST => 0,
	$USER => 1,
	$LEADER => 5,
	$MODERATOR => 8,
	$ADMIN => 10,
];

$roleName = [
	$roleLevel[$GUEST] => 'Guest',
	$roleLevel[$USER] => 'User',
	$roleLevel[$MODERATOR] => 'Moderator',
	$roleLevel[$LEADER] => 'Leader',
	$roleLevel[$ADMIN] => 'Admin',
];

return [
	'roleLevel' => $roleLevel,
	'roleName' => $roleName,

	'defaultRole'=> $USER,

];
?>

