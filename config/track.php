<?php 
$userLevels = include  'usersRoles.php';
$roles = $userLevels['roleLevel'];

return [
	'tracker'=>[
		'versions' => [ // start from 1
			1 => 'v0.1',
		],

		'issueCategories'=>[ // start from 1
			1 => 'Error',
			2 => 'Enhancement',
		],

		'issueSubCategories'=>[ // start from 1
			1 => 'SubCat 1',
			2 => 'SubCat 2',
		],

		'issuePriorities'=>[ // start from 1
			1 => 'Low',
			2 => 'Middle',
			3 => 'High',
		],

		'issueStatuses' => [ // starts from 0
			0 => 'Active',
			1 => 'Solved',
		],

	'permissions'=>[
			$roles[$ADMIN] =>[
				'createIssue' =>true,

				'editIssues' =>true,
				'editCloseIssues' => true,
				'editOwnIssues' =>true,
				'editOthersIssues' =>true,

				'changeStatus' => true, // needs editIssues
				'changePriority' => true, // needs editIssues
				'changeCategory' => true, // needs editIssues
				'changeSubCategory' => true, // needs editIssues
				'changePage' => true, // needs editIssues
				'changeVersion' => true, // needs editIssues

				'deleteIssues' =>true,
				'deleteOwnIssues' =>true, // needs deleteIssues
				'deleteOthersIssues' =>true, // needs deleteIssues

				'deleteComment' =>true,
				'deleteOwnComment' =>true, // needs deleteComment
				'deleteOthersComments' =>true, // needs deleteComment

				'editComment' =>true,
				'editOwnComment' =>true, // needs editComment
				'editOthersComments' =>true, // needs editComment

				],

			$roles[$LEADER] =>[
				'createIssue' =>true,

				'editIssues' =>true,
				'editCloseIssues' => true,
				'editOwnIssues' =>true,
				'editOthersIssues' =>true,

				'changeStatus' => true, // needs editIssues
				'changePriority' => true, // needs editIssues
				'changeCategory' => true, // needs editIssues
				'changeSubCategory' => true, // needs editIssues
				'changePage' => true, // needs editIssues
				'changeVersion' => true, // needs editIssues

				'deleteIssues' =>false,
				'deleteOwnIssues' =>false,
				'deleteOthersIssues' =>true,

				'deleteComment' =>true,
				'deleteOwnComment' =>true, // needs deleteComment
				'deleteOthersComments' =>true, // needs deleteComment

				'editComment' =>true,
				'editOwnComment' =>true, // needs editComment
				'editOthersComments' =>true, // needs editComment
			],

			$roles[$USER] =>[
				'createIssue' =>true,

				'editIssues' =>true,
				'editCloseIssues' => false,
				'editOwnIssues' =>true,
				'editOthersIssues' =>false,

				'changeStatus' => false, // needs editIssues
				'changePriority' => true, // needs editIssues
				'changeCategory' => true, // needs editIssues
				'changeSubCategory' => false, // needs editIssues
				'changePage' => true, // needs editIssues
				'changeVersion' => true, // needs editIssues

				'deleteIssues' =>false,
				'deleteOwnIssues' =>false,
				'deleteOthersIssues' =>false,

				'deleteComment' =>false,
				'deleteOwnComment' =>false, // needs deleteComment
				'deleteOthersComments' =>false, // needs deleteComment

				'editComment' =>true,
				'editOwnComment' =>true, // needs editComment
				'editOthersComments' =>false, // needs editComment
			],
		]
	]
];
?>
