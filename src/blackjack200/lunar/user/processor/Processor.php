<?php


namespace blackjack200\lunar\user\processor;


use blackjack200\lunar\user\User;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\DataPacket;

abstract class Processor implements Listener {
	/** @var User */
	private $user;

	public function __construct(User $user) {
		$this->user = $user;
	}

	public function processServerBond(DataPacket $packet) : void { }

	public function processClient(DataPacket $packet) : void { }

	final public function __destruct() {
		$this->destruct();
	}

	public function destruct() : void {
		//GC Hack
		$this->user = null;
	}

	public function getUser() : User { return $this->user; }

	public function check(...$data) : void { }

	public function finalize() : void { }
}