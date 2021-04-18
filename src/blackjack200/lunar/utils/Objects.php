<?php


namespace blackjack200\lunar\utils;


use stdClass;

class Objects {
	/**
	 * @param mixed|object $val
	 * @return mixed|object
	 */
	public static function convert($val) {
		$obj = new stdClass();
		if (is_array($val)) {
			foreach ($val as $key => $value) {
				$obj->$key = self::convert($value);
			}
		} else {
			return $val;
		}
		return $obj;
	}

	/**
	 * @param array<string, string> $values
	 */
	public static function replace(string $haystack, string $fmt, array $values) : string {
		return str_replace(
			array_map(
				static fn(string $k) : string => sprintf($fmt, $k),
				array_keys($values)
			),
			array_values($values),
			$haystack
		);
	}
}