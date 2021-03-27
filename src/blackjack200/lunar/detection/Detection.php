<?php


namespace blackjack200\lunar\detection;


use blackjack200\lunar\user\User;

interface Detection {
	/**
	 * This is the construct of Detection
	 * @param mixed $data Configure of the Detection
	 */
	public function __construct(User $user, string $name, $data);

	/**
	 * This method is trigger by DetectionTrigger
	 * @see DetectionTrigger
	 */
	public function check(...$data) : void;

	public function alert(string $message) : void;

	public function fail(string $message) : void;

	public function debug(string $message) : void;

	public function destruct() : void;

	public function getName() : string;
}