<?php

namespace blackjack200\lunar\user;

use pocketmine\Player;

final class UserManager {
	/** @var User[] */
	private static array $users = [];

	private function __construct() {
	}

	public static function register(Player $player) : void {
		self::$users[spl_object_hash($player)] = new User($player);
	}

	public static function unregister(Player $player) : void {
		unset(self::$users[spl_object_hash($player)]);
	}

	public static function getUser(Player $player) : ?User {
		return self::$users[spl_object_hash($player)] ?? null;
	}

	/** @return User[] */
	public static function getUsers() : array {
		return self::$users;
	}
}
