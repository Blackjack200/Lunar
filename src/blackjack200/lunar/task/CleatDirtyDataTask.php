<?php


namespace blackjack200\lunar\task;


use blackjack200\lunar\listener\DefaultListener;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use ReflectionClass;
use ReflectionProperty;

class CleatDirtyDataTask extends Task {
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
		$loginPackets = &$this->listener->dirtyLoginPacket;
		$startGamePackets = &$this->listener->dirtyStartGamePacket;
		$fin = [];
		$fin2 = [];
		foreach ($users as $player) {
			$hash = spl_object_hash($player);
			if (isset($loginPackets[$hash])) {
				$fin[$hash] = $loginPackets[$hash];
				if (isset($startGamePackets[$hash])) {
					$fin2[$hash] = $startGamePackets[$hash];
				}
			}
		}
		$loginPackets = $fin;
		$startGamePackets = $fin2;
	}

	/**
	 * fucking piece of shit
	 * @return Player[]
	 */
	private function getPlayers() : array {
		return $this->pro->getValue(Server::getInstance());
	}
}