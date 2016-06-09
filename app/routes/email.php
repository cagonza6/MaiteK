<?php

// form actions to send an email
$this->get('/email/change', 'EmailController:getChangeEmailRequest')->setName('email.change');
$this->post('/email/change', 'EmailController:postChangeEmailRequest');

$this->get('/email/confirm', 'EmailController:getChangeEmailConfirm')->setName('email.change.confirm');
