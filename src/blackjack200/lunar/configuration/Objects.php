<?php


namespace blackjack200\lunar\configuration;


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
}