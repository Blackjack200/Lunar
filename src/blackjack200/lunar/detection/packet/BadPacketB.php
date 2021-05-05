<?php


namespace blackjack200\lunar\detection\packet;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\PlayerMovementType;

class BadPacketB extends DetectionBase {
	private int $type;

	public function handleClient(DataPacket $packet) : void {
		if (
			(
				$packet::NETWORK_ID === MovePlayerPacket::NETWORK_ID &&
				$this->getMovementType() !== PlayerMovementType::LEGACY
			) ||
			(
				$packet::NETWORK_ID === PlayerAuthInputPacket::NETWORK_ID &&
				$this->getMovementType() === PlayerMovementType::LEGACY
			)
		) {
			$this->error($packet);
		}
	}

	private function getMovementType() : int {
		if (!isset($this->type)) {
			$this->type = $this->getUser()->startGame->playerMovementSettings->getMovementType();
		}
		return $this->type;
	}

	private function error(DataPacket $packet) : void {
		$this->addVL(1);
		if ($this->overflowVL()) {
			$this->fail("type=$this->type pid=" . $packet::NETWORK_ID);
		}
	}
}