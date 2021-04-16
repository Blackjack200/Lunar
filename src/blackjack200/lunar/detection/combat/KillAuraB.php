<?php


namespace blackjack200\lunar\detection\combat;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;

class KillAuraB extends DetectionBase {
	private float $lastSwing;
	private float $duration;

	public function __construct(User $user, string $name, $data) {
		parent::__construct($user, $name, $data);
		$this->lastSwing = microtime(true);
		$this->duration = $this->getConfiguration()->getExtraData()->SwingDuration;
	}

	public function handleClient(DataPacket $packet) : void {
		if ($packet instanceof AnimatePacket && $packet->action === AnimatePacket::ACTION_SWING_ARM) {
			$this->lastSwing = microtime(true);
		}

		if ($packet instanceof InventoryTransactionPacket && $packet->trData instanceof UseItemOnEntityTransactionData) {
			$lastSwing = microtime(true) - $this->lastSwing;
			// seems 4 AnimatePacket per sec
			if ($lastSwing > $this->duration && $this->preVL++ > 2) {
				$this->preVL = 1;
				$this->addVL(1);
				if ($this->overflowVL()) {
					$this->fail("last=$lastSwing");
				}
			}
		}
	}
}