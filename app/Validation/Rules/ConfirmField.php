<?php

namespace App\Validation\Rules;
use Respect\Validation\Rules\AbstractRule;

class ConfirmField extends AbstractRule{

	protected $password;

	public function __construct($original){

		$this->original = $original;

	}

	public function validate ($input){

		return ($input === $this->original)?true:false;

	}

}
