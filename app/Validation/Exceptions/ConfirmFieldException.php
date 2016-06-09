<?php

namespace App\Validation\Exceptions;
use Respect\Validation\Exceptions\ValidationException;

class ConfirmFieldException extends ValidationException{

	public static $defaultTemplates =[
		self::MODE_DEFAULT => [
			self::STANDARD => 'Confirmation does not match.'
		]
	];


}
