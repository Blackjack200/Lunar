<?php


namespace blackjack200\lunar\detection\action;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

class NukerA extends DetectionBase {
	private int $count = 0;

	public function check(...$data) : void {
		/** @var InventoryTransactionPacket|null $pk */
		[$pk] = $data;
		if ($pk instanceof InventoryTransactionPacket) {
			$this->count++;
			if ($this->count >= $this->getConfiguration()->getExtraData()->MaxBlock) {
				$this->fail("COUNT={$this->count}");
			}
		} else {
			$this->count = 0;
		}
	}
}