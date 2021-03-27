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
				$this->addVL(1);
				if ($this->overflowVL()) {
					$this->fail("COUNT={$this->count}");
				}
				return;
			}
		} else {
			$this->count = 0;
		}
		$this->rewardVL($this->getConfiguration()->getReward());
	}
}