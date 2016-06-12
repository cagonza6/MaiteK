<?php

namespace App\Controllers;

use \App\Models\User;
use \App\Models\Issue;
use \App\Controllers\Controller;
use \Respect\Validation\Validator as v;


class TrackerController extends Controller{

	public function index($request, $response, $args){
		$status=0;
		$category=1;
		if(count($args)){
			$userId = isset($args['userid'])?(int)$args['userid']:(int)$this->auth->user()->id;
			$category = (int)isset($args['category'])?$args['category']:1;
			$status = (int)isset($args['status'])?(int)$args['status']:0;
			$mine = (bool)isset($args['mine'])?(int)$args['mine']:false;
		}
		else{
			$userId = $request->getParam('userid')?$request->getParam('userid'):(int)$this->auth->user()->id;
			$category = $request->getParam('category')?$request->getParam('category'):1;
			$status = (int)$request->getParam('status')?(int)$request->getParam('status'):0;
			$mine = (bool)$request->getParam('mine')?(int)$request->getParam('mine'):false;
		}

		if (!array_key_exists($status,$this->configs['issueStatuses'])) $status=0;
		if (!array_key_exists($category, $this->configs['issueCategories'])) $category=1;


		$issues = Issue::getAllIssues($status, $userId, $mine, $category);

		for ($i = 0; $i < count($issues); $i++) {
			$issues[$i]->editIssue = $this->issuePermissions($issues[$i])['editIssue'];
			$issues[$i]->deleteIssue = $this->issuePermissions($issues[$i])['deleteIssue'];
			}

		$template = $mine?'tracker/myIssues.twig':'tracker/index.twig';
		return $this->view->render($response, $template, [
			'trackConf'=>$this->configs,
			'category'=>$category,
			'createIssue'=>$this->canCreateIssues(),
			'status'=>$status,
			'issues'=>$issues,
			'mine'=>$mine
			]);
	}

	public function userIssues($request, $response, $args){
		$status = false;
		// default user, loged in user
		$userId = $request->getParam('userid')?$request->getParam('userid'):$this->auth->user()->id;
		$category = $request->getParam('category')?$request->getParam('category'):1;

		// just valid if user is loged in
		$mine = $this->auth->check()?true:false;

		return $this->index($request, $response, [
			'userid'=> $userId,
			'status'=>false,
			'mine'=>$mine,
			'category'=>$category
		]);
	}

	// new issue
	public function getNewIssue($request, $response){
		if ($this->canCreateIssues())
			return $this->view->render($response, 'tracker/newIssue.twig',['trackConf'=>$this->configs]);
		else
			return $this->index($request, $response, $args=[]);
	}

	public function postNewIssue($request, $response){

		$title = $request->getParam('title');
		$description = $request->getParam('description');
		$priority = $request->getParam('priority');

		$category = $request->getParam('category');
		$subCategory = $request->getParam('subCategory');
		$version = $request->getParam('version');
		$page = $request->getParam('page');

		$validation = $this->validator->validate($request,
			[
				'title'=> v::notEmpty(),
				'description'=> v::notEmpty(),
		]);

		if($validation->failed())
			return $response->withRedirect($this->router->pathFor('tracker.newIssue',['trackConf'=>$this->configs]));

		$user = $this->auth->user();
		$id = Issue::createIssue($title, $description, $user->id, $priority, $category, $subCategory, $version, $page);
		// sets the user as a watcher automatically
		Issue::setWatcher($id, $this->auth->user()->id);
		$this->flash->addMessage('info', 'You are now watching this Issue.');
		return $response->withRedirect($this->router->pathFor('tracker.viewIssue',['trackConf'=>$this->configs, 'id'=>$id]));
	}

	// Issue //
	public function getEditIssue($request, $response, $args){
		$id = isset($args['id'])?(int)$args['id']:0;

		if (!$id){
			$this->flash->addMessage('error', 'Issue not found.');
			return $response->withRedirect($this->router->pathFor('tracker.index'));
		}

		$issue = Issue::getIssue($id, $this->auth->user()->id);
		if (!$this->canEditIssue($issue)){
			$this->flash->addMessage('error', 'You can not edit this issue.');
			return $response->withRedirect($this->router->pathFor('tracker.index'));
		}

		$comments = Issue::getIssueComents($id);

		return $this->view->render($response, 'tracker/editIssue.twig', [
			'trackConf'=>$this->configs,
			'status'=>$issue->status,
			'issue'=>$issue->fromArray(),
			'comments'=>$comments,
			]);
	}

	public function postEditIssue($request, $response, $args){

		$id = isset($args['id'])?(int)$args['id']:0;

		$issue = Issue::getIssue($id, $this->auth->user()->id);
		if (!$this->canEditIssue($issue)){
			$this->flash->addMessage('error', 'You can not edit this issue.');
			return $response->withRedirect($this->router->pathFor('tracker.index'));
		}

		$title = $request->getParam('title');
		$description = $request->getParam('description');
		$validation = $this->validator->validate($request,
			[
				'title'=> v::notEmpty(),
				'description'=> v::notEmpty(),
		]);

		if($validation->failed())
			return $response->withRedirect($this->router->pathFor('tracker.editIssue',['trackConf'=>$this->configs,'id'=>$id]));

		$user = $this->auth->user();
		Issue::editIssue($title, $description, $id, $user->id);

		$recipients = Issue::getWatchers($id);
		$this->mail->sendIssueEdited($response, $recipients,[
			'trackConf'=>$this->configs,
			'comment' =>$comment,
			'issue'=> $issue->fromArray(),
			'newDescription' => $description,
			'byUser' =>  $this->auth->user()->username
			]);

		if (!(bool)$issue->watching){
			Issue::setWatcher($issue->id, $this->auth->user()->id);
			$this->flash->addMessage('info', 'You are now watching this Issue.');
		}
		$this->flash->addMessage('success', 'Issue Edited.');
		return $response->withRedirect($this->router->pathFor('tracker.viewIssue',['trackConf'=>$this->configs,'id'=>$id]));
	}

	public function getDeleteIssue($request, $response, $args){
		$this->flash->addMessage('error', 'Action not allowed.');
		return $response->withRedirect($this->router->pathFor('tracker.index'));
	}

	public function postDeleteIssue($request, $response, $args){

		$id = $request->getParam('issueId');
		$issue = Issue::getIssue($id, $this->auth->user()->id);
		if (!$issue->id){
			$this->flash->addMessage('error', 'Issue not found.');
			return $response->withRedirect($this->router->pathFor('tracker.index'));
			}
		if (!$this->canDeleteIssue($issue)){
			$this->flash->addMessage('error', 'You can not delete this issue.');
			return $response->withRedirect($this->router->pathFor('tracker.index'));
		}
		Issue::deleteIssue($id);
		Issue::deleteIssueWatchers($id);
		$this->flash->addMessage('success', 'Issue deleted succesfully.');
		return $response->withRedirect($this->router->pathFor('tracker.index'));
	}

	// Issue view
	public function getViewIssue($request, $response, $args){
		$id = isset($args['id'])?(int)$args['id']:0;

		$issue = Issue::getIssue($id, $this->auth->user()->id);
		$issue->permissions = $this->issuePermissions($issue);
		if (!$issue->id){
			$this->flash->addMessage('error', 'Issue not found.');
			return $response->withRedirect($this->router->pathFor('tracker.index'));
		}

		$comments = Issue::getIssueComents($id);

		for($i = 0; $i < count($comments); $i++) {
			$comments[$i]->deleteComment = $this->canDeleteComment($comments[$i]);
			$comments[$i]->editComment = $this->canEditComment($comments[$i]);
			$comments[$i] = $comments[$i]->fromArray();
			}

		return $this->view->render($response, 'tracker/viewIssue.twig', ['trackConf'=>$this->configs, 'status'=>$issue->status, 'issue'=>$issue->fromArray(), 'comments'=>$comments]);
	}

	// Modifications of issue properties
	public function postInViewIssue($request, $response, $args){
		$id = isset($args['id'])?(int)$args['id']:0;

		if (!$id){
			$this->flash->addMessage('error', 'Issue not found.');
			return $response->withRedirect($this->router->pathFor('tracker.index'));
		}
		$issue = Issue::getIssue($id, $this->auth->user()->id);
		$action = $request->getParam('action');
		if ($action === 'update'){
			$status = (int)$request->getParam('status');
			$priority = (int)$request->getParam('priority');

			$category = (int)$request->getParam('category');
			$subCategory = $request->getParam('subCategory');
			$page = (int)$request->getParam('page');
			$version = (int)$request->getParam('version');

			$watching = (bool)$request->getParam('watching');
			$updated = false;

			if ($status!=(int)$issue->status     || $priority!=(int)$issue->priority   || 
				$category!=(int)$issue->category || $version!=(int)$issue->version ||
				$page!=(int)$issue->page
				){
				$updated=true;
			}
			if ($updated){
				Issue::updateIssue($id, $status, $priority, $category, $subCategory, $version, $page);
				$modifications = ['priority' => $priority , 'status' => $status];
				$this->flash->addMessage('success', 'Issue Updated.');

				$recipients = Issue::getWatchers($id);
				$this->mail->sendIssueUpdated($response, $recipients,[
					'trackConf'=>$this->configs,
					'comment' =>$comment,
					'issue'=> $issue->fromArray(),
					'user'=> $this->auth->user()->fromArray(),
					'modifications' => $modifications
					]);
			}

			if ($watching!=(bool)$issue->watching){
				if($watching){
					Issue::setWatcher($issue->id, $this->auth->user()->id);
					$this->flash->addMessage('info', 'You are now watching this Issue.');
				}
				else{
					Issue::deleteWatcher($issue->id, $this->auth->user()->id);
					$this->flash->addMessage('info', 'Your are not longer watching this issue.');
				}
			}
		}
		elseif ($action === 'newComment'){
			$comment = $request->getParam('comment');
			$validation = $this->validator->validate($request,['comment'=> v::notEmpty()]);

			if($validation->failed()){
				return $response->withRedirect($this->router->pathFor('tracker.viewIssue',['trackConf'=>$this->configs, 'id'=>$id]));
			}
			Issue::newComment($id, $comment, $this->auth->user()->id);

			// sets the user as a watcher automatically if not watching
			if (!(bool)$issue->watching){
				Issue::setWatcher($issue->id, $this->auth->user()->id);
				$this->flash->addMessage('info', 'Due to your comment you are now watching this issue.');
			}
			$recipients = Issue::getWatchers($id);
			$this->mail->sendNewCommentInIssue($response, $recipients,[
				'trackConf'=>$this->configs,
				'comment' =>$comment,
				'issue'=> $issue->fromArray(),
				'byUser' =>  $this->auth->user()->username
				]);
		$this->flash->addMessage('success', 'New Comment added.');
		}
		return $response->withRedirect($this->router->pathFor('tracker.viewIssue', ['trackConf'=>$this->configs, 'id'=>$id]));
	}

	public function issuePermissions($issue){
		return [
			'createIssue' => $this->canCreateIssues(),
			'editIssue' => $this->canEditIssue($issue),
			'deleteIssue' => $this->canDeleteIssue($issue),
			'changeStatus' => $this->canChangeIssuesStatus($issue),
			'changePriority' => $this->canChangeIssuesPriority($issue),
			'changeCategory' => $this->canChangeCategory($issue),
			'changeSubCategory' => $this->canSubChangeCategories($issue),
			'changeVersion' => $this->canChangeVersion($issue),
			'changePage' => $this->canChangePage($issue),
		];

	}

	// Comments //
	public function getEditComment($request, $response, $args){
		$id = isset($args['id'])?(int)$args['id']:0;

		if (!$id){
			$this->flash->addMessage('error', 'Issue not found.');
			return $response->withRedirect($this->router->pathFor('tracker.index'));
		}

		$comment = Issue::getComent($id);
		if (!$this->canEditComment($comment)){
			$this->flash->addMessage('error', 'You can not edit this comment.');
			return $response->withRedirect($this->router->pathFor('tracker.viewIssue', ['id'=>$id]));
		}

		return $this->view->render($response, 'tracker/editComment.twig', [
			'trackConf'=>$this->configs,
			'comment'=>$comment->fromArray(),
			]);
	}

	public function postEditComment($request, $response, $args){

		$id = isset($args['id'])?(int)$args['id']:0;
		$description = $request->getParam('comment');

		$comment = Issue::getComent($id);

		if (!$this->canEditComment($comment)){
			$this->flash->addMessage('error', 'You can not edit this comment.');
			return $response->withRedirect($this->router->pathFor('tracker.viewIssue', ['id'=>$id]));
		}

		$validation = $this->validator->validate($request, [
			'comment'=> v::notEmpty(),
			]);

		if($validation->failed())
			return $response->withRedirect($this->router->pathFor('tracker.editComment',['id'=>$id]));

		$this->flash->addMessage('success', 'Comment succesfully modified.');
		Issue::editComment($id, $description, $this->auth->user()->id);

		return $response->withRedirect($this->router->pathFor('tracker.viewIssue',['trackConf'=>$this->configs,'id'=>$comment->issue_id]));
	}

	public function getDeleteComment($request, $response, $args){
		$this->flash->addMessage('error', 'Action not allowed.');
		return $response->withRedirect($this->router->pathFor('tracker.index'));
	}

	public function postDeleteComment($request, $response, $args){

		$id = isset($args['id'])?(int)$args['id']:0;

		$comment = Issue::getComent($id);
		if (!$comment->id){
			$this->flash->addMessage('error', 'Comment not found.');
			return $response->withRedirect($this->router->pathFor('tracker.index'));
			}
		if (!$this->canDeleteComment($comment)){
			$this->flash->addMessage('error', 'You can not delete this comment.');
			return $response->withRedirect($this->router->pathFor('tracker.index'));
			
		}
		Issue::deleteComment($comment->id);
		$this->flash->addMessage('success', 'Comment deleted succesfully.');
		return $response->withRedirect($this->router->pathFor('tracker.viewIssue', ['id'=>$comment->issue_id]));

	}


/* Permissions */

	public function isIssueClose($issue){
		if ($issue->status)
			return true;
		elseif ($issue->duplicated_of)
			return true;
		return false;
	}

/* Create Issue*/
	public function canCreateIssues(){
		return $this->canDo('createIssue');
	}

/* Priority*/
	public function canChangeIssuesPriority(){
		if ($this->canDo('editIssues'))
			return $this->canDo('changePriority');
		return false;
	}

/* Status */
	public function canChangeIssuesStatus(){
		if ($this->canDo('editIssues'))
			return $this->canDo('changeStatus');
		return false;
	}

/* Category */
	public function canChangeCategories(){
		return $this->canDo('changeCategory');
	}
/* Category */
	public function canSubChangeCategories(){
		return $this->canDo('changeSubCategory');
	}

/* Versions */
	public function canChangeVersions(){
		return $this->canDo('changeVersion');
	}

/* Pages */
	public function canChangePages(){
		return $this->canDo('changePage');
	}

/*Edit issues checks*/

	public function canEditIssues($issueStatus ){
		return $this->canDo('editIssues');
	}

	public function canEditClosedIssues(){
		if($this->canDo('editCloseIssues'))
			return true;
		return false;
	}

	public function canEditOwnIssues($issueUserId ){
		if($this->canDo('editOwnIssues'))
			if((int)$issueUserId == (int)$this->auth->user()->id)
				return true;
		return false;
	}

	public function canEditOthersIssues($issueUserId ){
		if($this->canDo('editOthersIssues'))
			if ((int)$issueUserId != (int)$this->auth->user()->id)
				return true;
		return false;
	}

	public function canEditIssue($issue){

		if(!$this->canEditIssues($this->auth->user()->id))
			return false;
		if($this->isIssueClose($issue)){
			if($this->canEditClosedIssues())
				return true;
			return false;
		}
		if ($this->canEditOwnIssues($issue->issueUser))
				return true;
		if ($this->canEditOthersIssues($issue->issueUser))
				return true;
		return false;
	}

/* Delete Issues checks*/

	public function canDeleteIssues($issueStatus ){
		return $this->canDo('deleteIssues');
	}

	public function canDeleteOwnIssues($issueUserId ){
		if($this->canDo('deleteOwnIssues'))
			if ($issueUserId == $this->auth->user()->id)
				return true;
		return false;
	}

	public function canDeleteOthersIssues($issueUserId){
		if ($this->canDo('deleteOthersIssues'))
			if ($issueUserId != $this->auth->user()->id)
				return true;
		return false;
	}

	public function canDeleteIssue($issue){
		if(!$this->canDeleteIssues($this->auth->user()->id))
			return false;
		elseif ($this->canDeleteOwnIssues($issue->issueUser))
				return true;
		elseif ($this->canDeleteOthersIssues($issue->issueUser))
				return true;
		return false;
	}

/* Delete comments checks*/

	public function canDeleteComments(){
		return $this->canDo('deleteComment');
	}

	public function canDeleteOwncomments($commentUserId ){
		if($this->canDo('deleteOwnComment'))
			if ((int)$commentUserId == (int)$this->auth->user()->id)
				return true;
		return false;
	}

	public function canDeleteOthersComments($commentUserId){
		if ($this->canDo('deleteOthersComments'))
			if ((int)$commentUserId != (int)$this->auth->user()->id)
				return true;
		return false;
	}

	public function canDeleteComment($comment){
		$issue = Issue::getIssue($comment->issue_id, $this->auth->user()->id);

		if($this->isIssueClose($issue))
			return false;

		if(!$this->canDeleteComments())
			return false;
		elseif ($this->canDeleteOwncomments($comment->user_id))
				return true;
		elseif ($this->canDeleteOthersComments($comment->user_id))
				return true;
		return false;
	}

/* Edit comments checks*/

	public function canEditComments(){
		return $this->canDo('editComment');
	}

	public function canEditOwncomments($commentUserId ){
		if($this->canDo('editOwnComment'))
			if ((int)$commentUserId == (int)$this->auth->user()->id)
				return true;
		return false;
	}

	public function canEditOthersComments($commentUserId){
		if ($this->canDo('editOthersComments'))
			if ((int)$commentUserId != (int)$this->auth->user()->id)
				return true;
		return false;
	}

	public function canEditComment($comment){

		$issue = Issue::getIssue($comment->issue_id, $this->auth->user()->id);
		if($this->isIssueClose($issue))
			return false;
		if(!$this->canEditComments())
			return false;
		elseif ($this->canEditOwncomments($comment->user_id))
				return true;
		elseif ($this->canEditOthersComments($comment->user_id))
				return true;
		return false;
	}

/* Change category*/
	public function canChangeCategory($issue){

		if(!$this->canChangeCategories())
			return false;
		if($this->canEditIssue($issue))
			return true;
		return false;
	}

/* Change Versions*/
	public function canChangeVersion($issue){

		if(!$this->canChangeVersions())
			return false;
		if($this->canEditIssue($issue))
			return true;
		return false;
	}

/* Sub Category */
	public function canSubChangeCategorie($issue){
		if(!$this->canEditIssue($issue))
			return false;
		if($this->canSubChangeCategories())
			return true;
		return false;
	}

/* Change Page*/
	public function canChangePage($issue){

		if(!$this->canChangePages())
			return false;
		if($this->canEditIssue($issue))
			return true;
		return false;
	}

}
