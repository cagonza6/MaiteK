<?php

namespace App\Email;

class Mailer{

	protected $view;
	protected $mailer;

	public function __construct($view, $mailer){

		$this->view = $view;
		$this->mailer = $mailer;
	}

	public function send($response, $template, $header, $templateData){

		if (!$header['to'])
			return;

		$message = new Message($this->mailer);
		$message->body($this->view->render($response, $template, $templateData));

		if (is_array($header['to']))
			foreach ($header['to'] as $recipient)
				$message->addBCC($recipient->email);
		else
			$message->to($header['to']);
		$message->subject($header['subject']);
		$this->mailer->send();

	}

	public function sendConfirmAccount($response, $recipients, $templateData){
		$template = 'email/auth/registered.twig';
		$header = ['subject'=>'Thank you for registering', 'to' => $recipients];
		$this->send($response, $template,  $header, $templateData);
	}

	public function sendPasswordRecovery($response, $recipients, $templateData){
		$template = '/email/auth/pasword/recover.twig';
		$header = ['subject'=>'Recover your password', 'to' => $recipients];
		$this->send($response, $template, $header, $templateData);
	}

	public function sendConfirmChangeEmail($response, $recipients, $templateData){
		$template = '/email/auth/email/change.twig';
		$header = ['subject'=>'Changing your email', 'to' => $recipients];
		$this->send($response, $template, $header, $templateData);
	}

	public function sendNewCommentInIssue($response, $recipients, $templateData){
		$template = '/email/tracker/comments/newComment.twig';
		$header = ['subject'=>'New Comment in Issue', 'to' => $recipients];
		$this->send($response, $template, $header, $templateData);
	}

	public function sendIssueEdited($response, $recipients, $templateData){
		$template = '/email/tracker/issues/edited.twig';
		$header = ['subject'=>'New Comment in Issue', 'to' => $recipients];
		$this->send($response, $template, $header, $templateData);
	}

	public function sendIssueUpdated($response, $recipients, $templateData){
		$template = '/email/tracker/issues/updated.twig';
		$header = ['subject'=>'New Comment in Issue', 'to' => $recipients];
		$this->send($response, $template, $header, $templateData);
	}


}
