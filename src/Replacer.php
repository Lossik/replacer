<?php


namespace Lossik\Replacer;

class Replacer
{


	/** @var array[] */
	protected $objects;
	private $OBJECT_PREFIX = '$';
	private $OBJECT_SEPARATOR = '->';


	static public function replaceable($string)
	{
		$r = new static();

		return $r->isReplaceable($string);
	}


	public function isReplaceable($string, $syntaxOnly = true)
	{
		if (!(is_string($string) && strlen($string))) {
			return false;
		}
		if (!($string[0] === $this->OBJECT_PREFIX)) {
			return false;
		}

		$tmp = explode($this->OBJECT_SEPARATOR, $string);
		if (count($tmp) > 2) {
			return false;
		}
		if ($syntaxOnly) {
			return true;
		}
		if (!$this->hasObject($tmp[0])) {
			return false;
		}
		if (!isset($tmp[1])) {
			return true;
		}
		$obj = $this->getObject($tmp[0], false);
		if (!$this->hasProp($obj, $tmp[1])) {
			return false;
		}

		return true;
	}


	protected function hasObject($prefixedName)
	{
		return key_exists($prefixedName, $this->objects);
	}


	protected function getObject($prefixedName, $need = true)
	{
		if (key_exists($prefixedName, $this->objects)) {

			return $this->objects[$prefixedName];
		}
		if ($need) {
			throw new \LogicException("Object \"$prefixedName\" nelze nahradit.");
		}

		return null;
	}


	protected function hasProp($obj, $prop)
	{
		if (is_scalar($obj) || is_null($obj)) {
			return false;
		}

		return is_object($obj) ? property_exists($obj, $prop) : key_exists($prop, $obj);
	}


	public function addObject($obj, $name)
	{
		if (is_object($obj) || is_array($obj) || is_scalar($obj) || is_null($obj)) {
			$this->objects[$this->OBJECT_PREFIX . $name] = $obj;
		}

		return $this;
	}


	public function removeObject($name)
	{
		unset($this->objects[$name]);

		return $this;
	}


	public function replace($string, $need = true, $default = null)
	{

		if (!$this->isReplaceable($string, false)) {
			if ($need) {

				throw new \LogicException("\"$string\" nelze nahradit.");
			}

			return $default;
		}

		$tmp = explode($this->OBJECT_SEPARATOR, $string);

		$obj = $this->getObject($tmp[0], $need);

		$prop = isset($tmp[1]) ? $tmp[1] : null;

		return $prop ? $this->getProp($obj, $prop) : $obj;
	}


	protected function getProp($obj, $prop)
	{
		if (is_scalar($obj) || is_null($obj)) {
			throw new \LogicException("Scalar nema vlastnosti.");
		}

		return is_object($obj) ? $obj->$prop : $obj[$prop];
	}

}