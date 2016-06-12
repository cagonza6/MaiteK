<?php

namespace App\Controllers;

use \App\Models\User;
use \App\Models\Issue;
use \App\Controllers\Controller;
use \Respect\Validation\Validator as v;


class FileGalleryController extends Controller{

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
}
