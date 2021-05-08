<?php


namespace blackjack200\lunar\detection\movement;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class AirSwim extends DetectionBase {
	public function handleClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$info = $user->getMovementInfo();
			if ($info->inAirTick > 20 && $user->getActionInfo()->isSwimming) {
				$msg = "off=$info->inAirTick";
				$this->addVL(1, $msg);
				if ($this->overflowVL()) {
					$this->fail($msg);
					return;
				}
				$this->revertMovement();
			}
		}
	}
}