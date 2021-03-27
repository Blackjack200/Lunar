<?php


namespace blackjack200\lunar\detection;


interface DetectionTrigger {
	/**
	 * @param class-string $class
	 */
	public function trigger(string $class) : void;
}