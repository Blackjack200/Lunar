<?php


namespace blackjack200\lunar\user\processor;


class PlayerActionProcessor extends Processor {
	public function check(...$data) : void {
		$user = $this->getUser();
		$player = $user->getPlayer();
		$flying = $player->isFlying();
		$user->getActionInfo()->isFlying = $flying;
		if ($flying) {
			$user->getActionInfo()->lastStopFly = microtime(true);
		}
	}
}