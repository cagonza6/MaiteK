<?php

// form actions to send an email
$this->get('/password/recover', 'PasswordController:getRecoverPasswordRequest')->setName('password.recover');
$this->post('/password/recover', 'PasswordController:postRecoverPasswordConfirm');

// actions after the email is received
$this->get('/password/reset', 'PasswordController:getResetPasswordForm')->setName('password.reset');
$this->post('/password/reset', 'PasswordController:postResetPasswordConfirm');
