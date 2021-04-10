<?php


namespace blackjack200\lunar\detection\action;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;

class NukerA extends DetectionBase {
	protected int $maxBlock;
	private int $count = 0;

	public function __construct(User $user, string $name, $data) {
		parent::__construct($user, $name, $data);
		$this->maxBlock = $this->getConfiguration()->getExtraData()->MaxBlock;
	}

	public function handleClient(DataPacket $packet) : void {
		if (($packet instanceof InventoryTransactionPacket) &&
			$packet->trData instanceof UseItemTransactionData &&
			$packet->trData->getActionType() === UseItemTransactionData::ACTION_BREAK_BLOCK
		) {
			$this->count++;
			if ($this->count >= $this->maxBlock) {
				$this->addVL(1);
				if ($this->overflowVL()) {
					$this->fail("COUNT={$this->count}");
				}
				return;
			}
		}
	}

	public function check(...$data) : void {
		$this->count = 0;
		$this->rewardVL($this->getConfiguration()->getReward());
	}
}