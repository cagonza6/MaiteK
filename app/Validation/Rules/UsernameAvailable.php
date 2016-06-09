<?php

namespace App\Validation\Rules;

use App\Models\User;
use Respect\Validation\Rules\AbstractRule;

class UsernameAvailable extends AbstractRule{

	public function validate ($input){

		return User::userExists($input)?false:true;

	}

}
