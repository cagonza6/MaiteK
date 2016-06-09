<?php

namespace App\Email;

class Message {

	protected $mailer;

	public function __construct($mailer){
		$this->mailer = $mailer;
	}

	public function to($address){
		$this->mailer->addAddress($address);
	}

	public function addBCC($address){
		$this->mailer->addBCC($address);
	}

	public function subject($subject){
		$this->mailer->Subject = $subject;
	}

	public function body($body){
		$this->mailer->MsgHTML($body);
	}

}
