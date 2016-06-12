<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

// Open for every one
$app->get('/tracker[/status/]', 'TrackerController:index' )->setName('tracker.index');
$app->get('/tracker/view[/{id}]', 'TrackerController:getViewIssue' )->setName('tracker.viewIssue');
$app->get('/tracker/userIssues/', 'TrackerController:userIssues' )->setName('tracker.myIssues');

// Guest group
$app->group('', function(){ /* inser routes here*/ })->add(new GuestMiddleware($container));

// Loged in users
$app->group('', function(){
		// New issue
	$this->get('/tracker/new', 'TrackerController:getNewIssue' )->setName('tracker.newIssue');
	$this->post('/tracker/new', 'TrackerController:postNewIssue' );
	$this->post('/tracker/view[/{id}]', 'TrackerController:postInViewIssue' )->setName('tracker.viewIssue.post');

		// Edit issue
	$this->get('/tracker/edit[/{id}]', 'TrackerController:getEditIssue' )->setName('tracker.editIssue');
	$this->post('/tracker/edit[/{id}]', 'TrackerController:postEditIssue' );

		// Delete issue
	$this->post('/tracker/delete[/{id}]', 'TrackerController:postDeleteIssue' )->setName('tracker.deleteIssue');
	$this->get('/tracker/delete[/{id}]', 'TrackerController:getDeleteIssue' );

		// Edit comment
	$this->get('/tracker/comment/edit[/{id}]', 'TrackerController:getEditComment' )->setName('tracker.editComment');
	$this->post('/tracker/comment/edit[/{id}]', 'TrackerController:postEditComment' );

		// Delete comment
	$this->get('/tracker/comment/delete[/{id}]', 'TrackerController:getDeleteComment' )->setName('tracker.deleteComment');
	$this->post('/tracker/comment/delete[/{id}]', 'TrackerController:postDeleteComment' );

})->add(new AuthMiddleware($container));
