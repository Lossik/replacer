<?php


namespace Lossik\Utils;


class Arrays
{


	static public function assoc($records, $uniqueColumns)
	{

		$tmp = [];
		foreach ($records as $record) {
			$ref = &$tmp;
			foreach ($uniqueColumns as $uniqueColumn) {
				switch (true) {
					case($uniqueColumn === []):
					case(!self::getValue($record, $uniqueColumn)):
						$ref = &$ref[];
						break;
					case($val = self::getValue($record, $uniqueColumn)):
						$ref = &$ref[$val];
						break;
				}
			}
			$ref = $record;
		}

		return $tmp;
	}


	/**
	 * @param object|array $obj
	 * @param string $key
	 * @param null $default
	 *
	 *
	 * @return mixed|null
	 */
	static public function getValue($obj, $key, $default = null)
	{
		if (is_array($obj)) {
			return array_key_exists($key, $obj) ? $obj[$key] : $default;
		}
		if (is_object($obj)) {
			return property_exists($obj, $key) ? $obj->$key : $default;
		}

		return $default;
	}


	static public function difference($old, $new, $ignoreColumns = [])
	{
		$diff = array_diff_assoc($new, $old);
		foreach ($ignoreColumns as $ignoreColumn) {
			unset($diff[$ignoreColumn]);
		}

		return $diff;
	}


	static public function leftMerge($left, $right)
	{
		if (is_array($left) && is_array($right)) {
			foreach ($left as $key => $val) {
				if (is_int($key)) {
					$right[] = $val;
				}
				else {
					if (isset($right[$key])) {
						$val = static::leftMerge($val, $right[$key]);
					}
					$right[$key] = $val;
				}
			}

			return $right;
		}
		elseif ($left === null && is_array($right)) {

			return $right;
		}
		else {

			return $left;
		}
	}


	/**
	 * @param \ArrayAccess[]|array[]|object[] $arrays
	 * @param string $colunm
	 *
	 *
	 * @return array
	 */
	static public function column($arrays, $colunm)
	{
		$result = [];
		foreach ($arrays as $array) {
			$value = self::getValue($array, $colunm);
			if ($value) {
				$result[$value] = $value;
			}
		}

		return $result;
	}


	static public function unsetNull(array &$array)
	{
		foreach ($array as $key => $value) {
			if (is_null($value)) {
				unset($array[$key]);
			}
		}
	}


	static public function first($array, $keys)
	{
		foreach ($keys as $key) {
			if (array_key_exists($key, $array)) {
				return $array[$key];
			}
		}

		return null;
	}


	static public function unsetEach(& $array, $keys)
	{
		foreach ((array)$keys as $key) {
			unset($array[$key]);
		}
	}

}