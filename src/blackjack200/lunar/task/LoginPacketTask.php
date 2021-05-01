<?php


namespace blackjack200\lunar\task;


use blackjack200\lunar\listener\DefaultListener;
use blackjack200\lunar\user\UserManager;
use pocketmine\scheduler\Task;

class LoginPacketTask extends Task {
	private DefaultListener $listener;

	public function __construct(DefaultListener $listener) {
		$this->listener = $listener;
	}

	public function onRun(int $currentTick) : void {
		$users = UserManager::getUsers();
		$pks = &$this->listener->dirtyLoginPacket;
		foreach ($pks as $hash => $item) {
			if (!isset($users[$hash])) {
				unset($pks[$hash]);
			}
		}
	}
}