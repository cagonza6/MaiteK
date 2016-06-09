<?php

namespace App\Controllers;

class Controller{

	protected $container;
	protected $configs;
	protected $permissions;

	public function __construct($container){
		$this->container = $container;
	}

	public function setConfig($config){
		$this->configs = $config;
		$level = (int)$this->auth->user()->level;
		$this->permissions = $level?$config['permissions'][$level]:[];
		unset($this->configs['permissions']);
		}

	public function canDo($action){
		if (!isset($this->permissions[$action]))
			return false;
		if ($this->permissions[$action])
			return true;
		return false;
	}

	public function __get($property){
	if($this->container->{$property})
		return $this->container->{$property};
	}

}
