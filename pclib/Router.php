<?php
/**
 * @file
 * Application router.
 *
 * @author -dk- <lenochware@gmail.com>
 * @link http://pclib.brambor.net/
 */

# This library is free software; you can redistribute it and/or
# modify it under the terms of the GNU Lesser General Public
# License as published by the Free Software Foundation; either
# version 2.1 of the License, or (at your option) any later version.

/**
 * Translates URL to instance of Action class.
 * Action contains class and method name (with parameters) which will be called.
 */
class Router extends BaseObject implements IService
{
	public $friendlyUrl = false;
	public $baseUrl;

	/** var Action */
	public $action;

	public $onGetAction;
	public $onCreateUrl;


function __construct()
{
	parent::__construct();
	$this->baseUrl = BASE_URL;
	$this->action = $this->getAction();
}

/**
 * Create Action from current request.
 * Override for your own URL format.
 * @return Action $action
 */
function getAction()
{
	$action = new Action($_GET);

	//%form button has been pressed, set route accordingly.
	if ($_REQUEST['pcl_form_submit']) {
		$action->method = $_REQUEST['pcl_form_submit'];
	}

	$this->onGetAction($action);
	return $action;
}

/**
 * Transform internal action (e.g. 'products/edit/id:1') to URL.
 * @param string|Action $s
 * @return string $url
 */
function createUrl($s)
{
	$action = is_string($s)? new Action($s) : $s;
	//TODO: test instanceof Action

	$this->onCreateUrl($action);

	if (!$action->controller) return $this->baseUrl;

	if ($this->friendlyUrl) {
		$params = $action->params;
		return $this->baseUrl.$action->path.($params? '?'.$this->buildQuery($params) : '');
	} else {
		$params = array('r' => $action->path) + $action->params;
		return $this->baseUrl.'?'.$this->buildQuery($params);
	}
}


protected function buildQuery($query_data)
{
	$trans = array('%2F'=>'/','%3A'=>':','%2C'=>',');
	return strtr(http_build_query($query_data), $trans);
}

} //Router

/**
 * It encapsulates call of the controller's action: $controller->method($params).
 * It Can be mapped to URL.
 */
class Action 
{
	public $path;
	public $module;
	public $controller;
	public $method;
	public $params;

	function __construct($s = null)
	{
		if (is_string($s)) {
			$this->fromString($s);
		}
		elseif(is_array($s)) {
			$this->fromArray($s);
		}
	}

	/**
	 * Convert route to string.
	 * @return string $route
	 */
	function toString()
	{
		$params = array();
		foreach ($this->params as $key => $value) {
			$params[] = $key.':'.$value;
		}
		return $this->path.($params? '/'.implode('/',$params) : '');
	}

	function __toString() {
		return $this->toString();
	}

	/**
	 * Create new Action object from the string.
	 * @param string $s string-route e.g. 'orders/edit/id:1'
	 */
	function fromString($s)
	{
		$ra = explode('/', $this->replaceParams($s, null));

		$params = $path = array();

		foreach($ra as $section) {
			if ($section == '__GET__') { $params += $_GET; continue; }
			list($name,$value) = explode(':', $section);
			if (isset($value)) $params[$name] = $value;
			else $path[] = $section;
		}

		$this->path = implode('/', $path);
		$this->params = $params;

		$n = count($path);
		if ($n >= 3) {
			$path = array_slice($path, -3);
			$this->module = array_shift($path);
		}
		$this->controller = $path[0];
		$this->method = $path[1];
	}

	function fromArray($get)
	{
		$this->path = $get['r'];
		list($this->controller, $this->method) = explode('/', $this->path);
		unset($get['r']);
		$this->params = $get;
	}

	protected function replaceParams($s, $params)
	{
		preg_match_all("/{([a-z0-9_.]+)}/i", $s, $found);
		
		if ($found[0]) {
			$values = array();
			foreach ($found[1] as $name) {
				if ($name == 'GET') $value = '__GET__';
				elseif (substr($name,0,4) == 'GET.') $value = $_GET[substr($name,4)];
				elseif($params) $value = $params[$name]; 
				else $value = '';
				$values[] = $value;
			}
			return str_replace($found[0], $values, $s);
		}
		else {
			return $s;
		}
	}
} //Action
