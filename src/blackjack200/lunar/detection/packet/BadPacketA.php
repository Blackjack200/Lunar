<?php


namespace blackjack200\lunar\detection\packet;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;

class BadPacketA extends DetectionBase {
	public function handleReceive(DataPacket $packet) : void {
		if (
			$packet instanceof InventoryTransactionPacket &&
			$packet->trData instanceof UseItemOnEntityTransactionData &&
			$packet->trData->getEntityRuntimeId() === $this->getUser()->getPlayer()->getId()
		) {
			$this->addVL(1);
			if ($this->overflowVL()) {
				$this->fail("self-hit");
			}
		}
	}
}