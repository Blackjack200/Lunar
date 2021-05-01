<?php


namespace blackjack200\lunar\task;


use blackjack200\lunar\listener\DefaultListener;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use ReflectionClass;
use ReflectionProperty;

class LoginPacketTask extends Task {
	private DefaultListener $listener;
	private ReflectionProperty $pro;

	public function __construct(DefaultListener $listener) {
		$this->listener = $listener;
		$clazz = new ReflectionClass(Server::class);
		$pro = $clazz->getProperty('players');
		$pro->setAccessible(true);
		$this->pro = $pro;
	}

	public function onRun(int $currentTick) : void {
		$users = $this->getPlayers();
		$pks = &$this->listener->dirtyLoginPacket;
		$fin = [];
		foreach ($users as $player) {
			$hash = spl_object_hash($player);
			if (isset($pks[$hash])) {
				$fin[$hash] = $pks[$hash];
			}
		}
		$pks = $fin;
	}

	/**
	 * fucking piece of shit
	 * @return Player[]
	 */
	private function getPlayers() : array {
		return $this->pro->getValue(Server::getInstance());
	}
}