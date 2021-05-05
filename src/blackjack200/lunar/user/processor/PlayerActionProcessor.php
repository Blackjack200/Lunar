<?php


namespace blackjack200\lunar\user\processor;


use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;

class PlayerActionProcessor extends Processor {
	public function processClient(DataPacket $packet) : void {
		if ($packet instanceof PlayerActionPacket) {
			$user = $this->getUser();
			$info2 = $user->getExpiredInfo();
			$info = $user->getActionInfo();
			switch ($packet->action) {
				case PlayerActionPacket::ACTION_START_SPRINT:
					$info->isSprinting = true;
					break;
				case PlayerActionPacket::ACTION_STOP_SPRINT:
					$info->isSprinting = false;
					$info2->set('sprint');
					break;
				case PlayerActionPacket::ACTION_START_GLIDE:
					$info->isGliding = true;
					break;
				case PlayerActionPacket::ACTION_STOP_GLIDE:
					$info->isGliding = false;
					$info2->set('glide');
					break;
				case PlayerActionPacket::ACTION_START_SNEAK:
					$info->isSneaking = true;
					break;
				case PlayerActionPacket::ACTION_STOP_SNEAK:
					$info->isSprinting = false;
					$info2->set('sneak');
					break;
				case PlayerActionPacket::ACTION_START_SWIMMING:
					$info->isSwimming = true;
					break;
				case PlayerActionPacket::ACTION_STOP_SWIMMING:
					$info->isSwimming = false;
					$info2->set('swim');
					break;
				case PlayerActionPacket::ACTION_JUMP:
					$user->getMovementInfo()->lastJump = microtime(true);
					break;
			}
		}
	}

	public function check(...$data) : void {
		$user = $this->getUser();
		$player = $user->getPlayer();
		$flying = $player->isFlying();
		$user->getActionInfo()->isFlying = $flying;
		if (!$flying && $user->getActionInfo()->isFlying) {
			$user->getExpiredInfo()->set('fly');
		}
	}
}