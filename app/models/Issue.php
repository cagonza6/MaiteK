<?php

namespace App\Models;

use \App\Database\Database;

class Issue extends Database{

	public static function getAllIssues($status, $current_user, $mine=false, $category=false){
		$queryData = array();
		$query = '';
		$query .= 'SELECT issues.id, title, description, issues.status, priority, ';
		$query .= '       issues.date AS issueDate, fixed_in, duplicated_of, ';
		$query .= '       issues.date AS category, ';
		$query .= '       issues.user AS issueUser, users.username AS issueUsername, lastCommentDate, ';
		$query .= '       comment_user, commentUsername, ';
		$query .= '       watching ';
		$query .= 'FROM issues ';
		$query .= 'LEFT JOIN users ON users.user_id = issues.user ';
		$query .= 'LEFT JOIN (select issue_id, user_id AS watching  from issues_watchers where user_id=:current_user ) AS watchers ON watchers.issue_id = issues.id ';
		$query .= 'LEFT JOIN (SELECT ';
		$query .= '                 max(date) AS lastCommentDate, issue_id ';
		$query .= '            FROM ';
		$query .= '                    issues_comments ';
		$query .= '                GROUP BY issue_id) ';
		$query .= '     AS cmax ON cmax.issue_id = issues.id ';
		$query .= 'LEFT JOIN ( ';
		$query .= '           SELECT ';
		$query .= '                 users.username AS commentUsername , issues_comments.user_id AS comment_user, date AS comment_date, issue_id ';
		$query .= '           FROM ';
		$query .= '                issues_comments ';
		$query .= '           LEFT JOIN ';
		$query .= '                     users ON users.user_id = issues_comments.user_id ';
		$query .= '           ORDER BY ';
		$query .= '                   issue_id DESC, date DESC ';
		$query .= '                   ) ';
		$query .= '     AS c ON  ';
		$query .= '             cmax.lastCommentDate = c.comment_date AND cmax.issue_id = issues.id ';

		$queryData[':current_user'] = $current_user;

		if ($mine)
			$query .=' WHERE issues.user=:current_user ';
		elseif($status){
			$query .=' WHERE issues.status=:status ';
			$queryData[':status'] = $status;
			}
		if($category){
			$query .=' AND issues.category=:category ';
			$queryData[':category'] = $category;
		}

		$query .=" ORDER BY priority DESC, issues.id ASC";

		return self::fetchAll($query,$queryData);
	}

	public static function createIssue($title, $description, $user, $priority, $category, $subCategory, $version, $page){
		$query = "INSERT INTO issues (title, description, user, priority, date, category, subCategory,  version, page) ";
		$query .= 'values(:title, :description, :user, :priority, :date, :category, :subCategory, :version, :page)';
		$queryData = array(
			':title' => $title,
			':description' => $description,
			':user' => $user,
			':priority' => $priority,
			':category' => $category,
			':subCategory' => $subCategory,
			':version' => $version,
			':page' => $page,
			':date' => date("Y-m-d H:i:s"),
		);
		$conn = self::connection();
		$stmt = $conn->prepare($query);
		$stmt->execute($queryData);
		return $conn->lastInsertId();
	}

	public static function updateIssue($id, $status, $priority, $category, $subCategory, $version, $page){
		$query = 'UPDATE issues SET status=:status, priority=:priority, ';
		$query .= 'category=:category, subCategory=:subCategory, version=:version, page=:page ';
		$query .= 'where id=:id;';
		
		$queryData = array(
			':id' => $id,
			':status' => $status,
			':priority' => $priority,
			':category' => $category,
			':subCategory' => $subCategory,
			':version' => $version,
			':page' => $page,
		);
		$conn = self::connection();
		$stmt = $conn->prepare($query);
		$stmt->execute($queryData);
		return $conn->lastInsertId();
	}

	public static function getIssue($id, $current_user){

		$queryData = array(
			':id' => $id,
			':current_user' => $current_user
		);

		$query = 'SELECT ';
		$query .='     id, title, description, issues.status, priority, issues.date, users.username, users.user_id AS issueUser, ';
		$query .='     watching, category, subCategory, page, version, ';
		$query .='     issues.updated_at, updated_by, user_update.username AS updater ';
		$query .='FROM issues ';
		$query .='LEFT JOIN users ON users.user_id = issues.user ';
		$query .='LEFT JOIN users AS user_update ON user_update.user_id = issues.updated_by ';
		$query .= 'LEFT JOIN (select issue_id, user_id AS watching from issues_watchers where user_id=:current_user ) AS watchers ON watchers.issue_id = issues.id ';
		$query .='WHERE id=:id LIMIT 1 ;';
		return self::fetchOne($query,$queryData);
	}

	public static function getIssueComents($id){
		$query = 'SELECT  ';
		$query .= '     id, issue_id, comments.user_id, description, date, users.username, comments.updated_at, ';
		$query .= '     comments.updated_by, user_update.username AS updater ';
		$query .= 'FROM ';
		$query .= '    issues_comments as comments ';
		$query .= 'LEFT JOIN users ON users.user_id = comments.user_id ';
		$query .= 'LEFT JOIN users AS user_update ON user_update.user_id = comments.updated_by ';
		$query .= 'WHERE issue_id=:id ';
		$query .= ' ORDER BY id ASC ';

		$queryData = array(
			':id' => (int)$id,
		);

		return self::fetchAll($query,$queryData);
	}

	public static function getComent($id){
		$query = 'SELECT  ';
		$query .= '     comments.id, issue_id, comments.user_id, description, date, username ';
		$query .= 'FROM ';
		$query .= '    issues_comments as comments ';
		$query .= 'LEFT JOIN users ON users.user_id = comments.user_id ';
		$query .= 'WHERE comments.id=:id LIMIT 1;';

		$queryData = array(
			':id' => (int)$id,
		);
		return self::fetchOne($query,$queryData);
	}

	public static function setPriority($id, $priority){
		$query = "UPDATE issues SET priority=:priority, updated_at=:updated_at WHERE id=:id;";
		$queryData = array(
			':priority' => $priority,
			':id' => $id,
			':updated_at' => date("Y-m-d H:i:s")
		);
		self::executeQuery($query, $queryData);
	}

	public static function setStatus($id, $status){
		$query = "UPDATE issues SET status=:status, updated_at=:updated_at WHERE id=:id";
		$queryData = array(
			':status' => $status,
			':id' => $id,
			':updated_at' => date("Y-m-d H:i:s")
		);
		self::executeQuery($query, $queryData);
	}

	public static function newComment($issue_id, $description, $user){
		$query = "INSERT INTO issues_comments (issue_id, description, user_id, date) values(:issue_id,:description,:user_id,:date)";
		$queryData = array(
			':issue_id' => $issue_id,
			':description' => $description,
			':user_id' => $user,
			':date' => date("Y-m-d H:i:s"),
		);
		self::executeQuery($query, $queryData);
	}

	public static function editComment($id, $description, $updated_by){
		$query = 'UPDATE issues_comments SET description=:description, updated_by=:updated_by WHERE id=:id ;';
		$queryData = array(
			':description' => $description,
			':id' => $id,
			':updated_by' => $updated_by,
		);
		self::executeQuery($query, $queryData);
	}

	public static function editIssue($title, $description, $id, $updated_by){
		$query = 'UPDATE issues SET title=:title, description=:description, updated_by=:updated_by WHERE id=:id ;';
		$queryData = array(
			':title' => $title,
			':description' => $description,
			':id' => $id,
			':updated_by' => $updated_by,
		);
		self::executeQuery($query, $queryData);
	}

	public static function deleteIssue($id){
		$query = 'DELETE FROM issues WHERE id=:id';
		$queryData = array(
			':id' => $id,
		);
		self::executeQuery($query, $queryData);
		$query = 'DELETE FROM issues_comments WHERE issue_id=:id';
		self::executeQuery($query, $queryData);
	}
	
	public static function deleteComment($id){
		$query = 'DELETE FROM issues_comments WHERE id=:id';
		$queryData = array(
			':id' => $id,
		);
		self::executeQuery($query, $queryData);
	}

	public static function setWatcher($issueID, $userID){
		$query = 'INSERT INTO issues_watchers (issue_id, user_id ) ';
		$query .= 'values(:issueID, :userID)';
		$queryData = array(
			':issueID' => $issueID,
			':userID' => $userID,
		);
		self::executeQuery($query, $queryData);
	}

	public static function getWatchers($issueID){
		$query = ' SELECT email, username FROM issues_watchers ';
		$query .= 'INNER JOIN users ON issues_watchers.user_id = users.user_id ';
		$query .= 'WHERE issue_id=:issueID ';
		$query .= 'GROUP BY users.user_id ';
		$queryData = array(
			':issueID' => $issueID,
		);
		return self::fetchAll($query,$queryData);
	}

	public static function deleteWatcher($issueID, $userID){
		$query = 'DELETE FROM issues_watchers WHERE issue_id=:issueID AND user_id=:userID AND id>0 ;';
		$queryData = array(
			':issueID' => $issueID,
			':userID' => $userID,
		);
		self::executeQuery($query, $queryData);
	}

	public static function deleteIssueWatchers($issueID){
		$query = 'DELETE FROM issues_watchers WHERE issue_id=:issueID AND id>0 ';
		$queryData = array(
			':issueID' => $issueID,
		);
		self::executeQuery($query, $queryData);
	}

}
