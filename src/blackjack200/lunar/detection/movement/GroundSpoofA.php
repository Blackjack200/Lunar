<?php


namespace blackjack200\lunar\detection\movement;


use blackjack200\lunar\configuration\Boolean;
use blackjack200\lunar\detection\DetectionBase;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class GroundSpoofA extends DetectionBase {
	public function handleClient(DataPacket $packet) : void {
		$info = $this->getUser()->getMovementInfo();
		if ($packet instanceof MovePlayerPacket &&
			$packet->onGround !== $info->onGround &&
			$this->preVL++ > 10 &&
			$info->timeSinceTeleport() > 1) {
			$this->preVL = 5;
			$this->addVL(1);
			if ($this->overflowVL()) {
				$this->fail('packet=' . Boolean::btos($packet->onGround));
			}
		}
	}
}