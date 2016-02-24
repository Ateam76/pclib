<?php
/**
 * @file
 * Ancestor of all pclib classes.
 *
 * @author -dk- <lenochware@gmail.com>
 * @link http://pclib.brambor.net/
 */

# This library is free software; you can redistribute it and/or
# modify it under the terms of the GNU Lesser General Public
# License as published by the Free Software Foundation; either
# version 2.1 of the License, or (at your option) any later version.

namespace pclib\system;

/**
 * Ancestor of all pclib classes.
 * Features:
 * - Access to undeclared members throws exception
 * - Events and object closures
 * - Object configuration
 */ 
class BaseObject
{
	/*protected*/public static $defaults = array();

	/** Occurs when new instance of the class is created. */
	public $onNewInstance;

	/** Occurs when called method does not exist. */
	public $onMissingMethod;

	/** var function() Return service object when requested with service(). */
	public $serviceLocator;
 
	/**
	 * Set or retireve default parameters of the object. 
	 * You can set any public attribute of the object.
	 * Example: Form::defaults('useXhtml', true); or Form::defaults($options);
	 * @param mixed Configuration parameter(s).
	 */
	public static function defaults()
	{
		$args = func_get_args();
		$classDef = &self::$defaults[get_called_class()];

		if (!$args) {
			return $classDef;
		}
		elseif(is_array($args[0])) {
			self::$defaults[get_called_class()] = array_merge($classDef, $args[0]);
			return $classDef;
		}

		list($name, $value) = $args;

		if (isset($value)) {
			if (self::isEvent($name)) {
				$classDef[$name][] = $value;
			}
			else {
				$classDef[$name] = $value;
			}
		}
		else {
			return $classDef[$name];
		}
	}

	function __construct()
	{
		$this->loadDefaults(get_called_class());
		$this->onNewInstance();
	}

	public static function isEvent($name)
	{
		return preg_match('/^on[A-Z]/', $name);
	}

	/**
	 * Load default parameters of class $className into object instance. 
	 */
	public function loadDefaults($className = null)
	{
		if (!$className) $className = get_class($this);
		if ($parentClass = get_parent_class($className)) {
			$this->loadDefaults($parentClass);
		}
		$this->setOptions((array)self::$defaults[$className]);
		return $this;
	}

	/**
	 * Set public properties of object from the array. 
	 * @param array $defaults Array of parameters to be set.
	 */
 	function setOptions(array $defaults)
	{
		$closure = function($o, $defaults) {
			foreach ($defaults as $key => $value) {
				if (BaseObject::isEvent($key)) {
					foreach ($value as $event) {
						$o->addEvent($key, $event);
					}
				}
				else {
					$o->$key = $value;
				}
			}
		};

		$closure($this, $defaults);
	}

	/**
	 * Run all event handlers in $object->$name property. 
	 * @param string $name Event name e.g. 'onSave'.
	 * @param array $args Event arguments.
	 */
	protected function fireEvent($name, array $args = array())
	{
		if (!is_array($this->$name)) return false;

		$event = new \stdClass;
		$event->sender = $this;
		$event->name = $name;
		$event->data = $args;
		$event->propagate = true;

		foreach ($this->$name as $func) {
			$result = call_user_func($func, $event);
			if (isset($result)) $event->result = $result;
			if (!$event->propagate) break;
		}
		return $event;
	}

	/**
	 * Add event handler to $object->$name property. 
	 * @param string $name Event name e.g. 'onSave'.
	 * @param callable $callback Event handler. (callable type hint is not supported in php 5.3)
	 */
	function addEvent($name, /*callable*/ $callback)
	{
		if (!is_array($this->$name)) $this->$name = array();
		array_push($this->$name, $callback);
	}

	/**
	 * Try acquire $service and load it into property $this->$service.
	 * Service is acquired by calling $onLoadService event.
	 * @param string $service Service name
	 * @param mixed $default Default value when service is not found
	 * @return object Service object
	 */
	protected function service($service, $default = null)
	{
		if (!$this->$service) {

			if ($this->serviceLocator) {
				$result = $this->serviceLocator($service);
			}

			if (!$result) {
				if (isset($default)) {
					$result = is_string($default)? $this->service($default) : $default;
				}
				else {
					$className = get_class($this);
					throw new \pclib\Exception("Required service '$className->$service' is not set.");
				}
			}

			$this->$service = $result;
		}

		return $this->$service;
	}

	public function __call($name, $args)
	{
		if (self::isEvent($name)) {
			return $this->fireEvent($name, $args);
		}

		// instanceof Closure
		if (isset($this->$name) and is_callable($this->$name)) {
			return call_user_func_array($this->$name, $args);
		}

		$ev = $this->fireEvent('onMissingMethod', array($name, $args));

		if (!$ev or $ev->propagate) {
			$class = get_class($this);
			throw new MemberAccessException("Call to undefined method $class->$name()."); 
		}
	}

	public function __get($name)
	{
		$class = get_class($this);
		throw new MemberAccessException("Cannot read an undeclared property $class->$name.");
	}

	public function __set($name, $value)
	{
		$class = get_class($this);
		throw new MemberAccessException("Cannot write to an undeclared property $class->$name.");
	}

	public static function __callStatic($name, $args)
	{
		$class = get_called_class();
		throw new MemberAccessException("Call to undefined static method $class::$name().");
	}

	public function __toString()
	{
		return 'Object.'.get_class($this)/*.' '.json_encode($this, JSON_PRETTY_PRINT, 1)*/;
	}

	/**
	 * Convert object to array. 
	 * @return array Object
	 */
	public function toArray()
	{
		return (array)$this;
	}

/*
	public function __isset($name)
	{
	}

	public function __unset($name)
	{
	}
*/

}