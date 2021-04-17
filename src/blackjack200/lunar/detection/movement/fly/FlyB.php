<?php


namespace blackjack200\lunar\detection\movement\fly;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;

class FlyB extends DetectionBase {
	public function handleClient(DataPacket $packet) : void {
		$info = $this->getUser()->getMovementInfo();
		if ($packet instanceof PlayerActionPacket &&
			$packet->action === PlayerActionPacket::ACTION_JUMP &&
			!$info->onGround &&
			$info->timeSinceTeleport() > 1
		) {
			$this->addVL(1);
			$this->revertMovement();
			if ($this->overflowVL()) {
				$this->fail("off={$info->inAirTick}");
			}
		}
	}
}