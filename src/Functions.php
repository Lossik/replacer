<?php


namespace Lossik\Replacer;

use Lossik\Utils\Arrays;

class Functions
{


	/** @var Replacer */
	protected $replacer;

	/** @var array */
	protected $functions;


	/**
	 * @param Replacer $replacer
	 *
	 */
	public function __construct(Replacer $replacer)
	{
		$this->replacer = $replacer;
		$this->addFunction([$this, '_is'], 'is');
		$this->addFunction([$this, '_eqval'], 'eqval');
		$this->addFunction([$this, '_no_eqval'], 'no_eqval');
		$this->addFunction([$this, '_strpos_is'], 'strpos_is');
		$this->addFunction([$this, '_no'], 'no');
		$this->addFunction([$this, '_in'], 'in');
		$this->addFunction([$this, '_group_concat'], 'group_concat');
		$this->addFunction([$this, '_column'], 'column');
		$this->addFunction([$this, '_explode'], 'explode');
		$this->addFunction([$this, '_get_value'], 'get_value');
		$this->addFunction([$this, '_str_remove'], 'remove');
		$this->addFunction([$this, '_concat'], 'concat');
		$this->addFunction([$this, '_and'], 'and');
		$this->addFunction([$this, '_or'], 'or');
	}


	public function addFunction($callable, $funcName)
	{
		$this->functions[$funcName . '()'] = $callable;

		return $this;
	}


	static public function isFunctionS($string)
	{
		$f = new static(new Replacer());

		return $f->isFunction($string);
	}


	public function isFunction($function)
	{
		if (!is_array($function)) {
			return false;
		}
		if (!count($function)) {
			return false;
		}
		$args = $function;
		$func = array_shift($args);
		if (!(is_string($func) && strlen($func) > 2)) {
			return false;
		}
		if (!(strpos($func, '()', strlen($func) - 2) === strlen($func) - 2)) {
			return false;
		}
		if (!(strpos(substr($func, 0, strlen($func) - 2), '()') === false)) {
			return false;
		}

		return true;
	}


	public function callFunction($function)
	{
		if (!$this->isFunction($function)) {
			throw new \LogicException("Neni funkce, nelze provest");
		}
		$args = $this->getArgs($function);
		$func = $this->getFunction($function);

		return $this->call($func, $args);
	}


	protected function getArgs($function)
	{
		array_shift($function);

		return $function;
	}


	protected function getFunction($function)
	{
		return array_shift($function);
	}


	protected function call($function, $args)
	{
		$callback = $this->functions[$function];
		if (!$callback) {
			throw new \LogicException("Funkce $function neni povolena");
		}
		$args = $this->evalArgs($args);

		return call_user_func_array($callback, $args);
	}


	protected function evalArgs($args)
	{
		foreach ($args as &$arg) {
			if ($this->isFunction($arg)) {
				$arg = $this->callFunction($arg);
				continue;
			}
			if ($this->replacer->isReplaceable($arg)) {
				$arg = $this->replacer->replace($arg, false);
				continue;
			}
		}

		return $args;
	}


	/**
	 * @internal
	 * @param $value
	 * @return bool
	 */
	public function _is($value)
	{
		return (bool)$value;
	}


	/**
	 * @internal
	 * @param $value1
	 * @param $value2
	 * @return bool
	 */
	public function _eqval($value1, $value2)
	{
		return $value1 === $value2;
	}


	/**
	 * @internal
	 * @param $value1
	 * @param $value2
	 * @return bool
	 */
	public function _no_eqval($value1, $value2)
	{
		return $value1 !== $value2;
	}


	/**
	 * @internal
	 * @param $haystack
	 * @param $needle
	 * @param $result
	 * @return bool
	 */
	public function _strpos_is($haystack, $needle, $result)
	{
		return strpos($haystack, $needle) === $result;
	}


	/**
	 * @internal
	 * @param $value
	 * @return bool
	 */
	public function _no($value)
	{
		return !$value;
	}


	/**
	 * @internal
	 * @param $value
	 * @param $array
	 * @return bool
	 */
	public function _in($value, $array)
	{
		return in_array($value, $array);
	}


	/**
	 * @internal
	 * @param $delimiter
	 * @param $array
	 * @return string
	 */
	public function _group_concat($array, $delimiter)
	{
		return implode($delimiter, $array);
	}


	/**
	 * @param mixed $_arg
	 * @return string
	 */
	public function _concat($_arg)
	{
		return implode('', func_get_args());
	}


	/**
	 * @internal
	 * @param $delimiter
	 * @param $array
	 * @return bool
	 */
	public function _explode($delimiter, $array)
	{
		return explode($delimiter, $array);
	}


	/**
	 * @internal
	 * @param $array
	 * @param $column
	 * @return bool
	 */
	public function _column($array, $column)
	{
		return array_keys(Arrays::column($array, $column));
	}


	/**
	 * @internal
	 * @param $array
	 * @param $column
	 * @return bool
	 */
	public function _get_value($array, $column)
	{
		return Arrays::getValue($array, $column);
	}


	/**
	 * @internal
	 * @param $string
	 * @param $remove
	 * @return bool
	 */
	public function _str_remove($string, $remove)
	{
		return str_replace($remove, '', $string);
	}


	public function _and()
	{
		$result = true;
		foreach (func_get_args() as $arg) {
			$result = $result && $arg;
		}

		return $result;
	}


	public function _or()
	{
		$result = false;
		foreach (func_get_args() as $arg) {
			$result = $result || $arg;
		}

		return $result;
	}

}