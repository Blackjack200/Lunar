<?php


namespace blackjack200\lunar\detection\combat;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;

class ReachA extends DetectionBase {
	public function check(...$data) : void {
		$this->impl(...$data);
	}

	public function impl(EntityDamageByEntityEvent $event) : void {
		if ($event instanceof EntityDamageByChildEntityEvent) {
			return;
		}
		/** @var Player $damager */
		$damager = $event->getDamager();
		if ($damager->isCreative()) {
			return;
		}
		/** @var Player $damaged */
		$damaged = $event->getEntity();

		$maxDist = $this->getAllowedDistance();
		$dist = $damager->distance($damaged);
		if ($dist > $maxDist && $this->preVL++ > 3) {
			$this->addVL(1);
			$this->preVL = 2;
			if ($this->getConfiguration()->isSuppress()) {
				$event->setCancelled(true);
			}

			if ($this->overflowVL()) {
				$this->fail("max=$maxDist dist=$dist");
			}
		}
	}

	//reference: https://github.com/Bavfalcon9/Mavoric/blob/03abce64998ea29271d39bbad913fded275e20ff/src/Bavfalcon9/Mavoric/Cheat/Combat/Reach.php#L59-L62
	public function getAllowedDistance() : float {
		$user = $this->getUser();
		$projected = $user->getMovementInfo()->onGround ? 4 : 6.2;
		return ($user->getPlayer()->getPing() * 0.002) + $projected;
	}
}