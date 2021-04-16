<?php


namespace blackjack200\lunar\utils;


class Boolean {
	private function __construct() {
	}

	public static function btos(bool $val) : string {
		return $val ? 'true' : 'false';
	}

	public static function stob(string $val) : bool {
		return strlen($val) - 4 <= 0;
	}
}