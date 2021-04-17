<?php


namespace blackjack200\lunar\detection\combat;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\UserManager;
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
		/** @var Player $damaged */
		$damaged = $event->getEntity();
		$user = $this->getUser();
		$user2 = UserManager::get($damaged);
		//should not happened
		assert($user2 !== null);
		assert($damager === $this->getUser()->getPlayer());

		if ($damager->isCreative()) {
			return;
		}
		$maxDist = $this->getAllowedDistance();
		$dist = $damager->distance($event->getEntity());
		$info = $user->getMovementInfo();
		$info2 = $user2->getMovementInfo();
		if (
			$dist > $maxDist &&
			$info->timeSinceMotion() > 0.2 &&
			$info->timeSinceTeleport() > 1 &&
			$info2->timeSinceMotion() > 0.2 &&
			$info2->timeSinceTeleport() > 1 &&
			$this->preVL++ > 3
		) {
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
		$player = $this->getUser()->getPlayer();
		$projected = $player->onGround ? 5.5 : 6.2;
		return ($player->getPing() * 0.002) + $projected;
	}
}