<?php


namespace blackjack200\lunar\detection\movement;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\block\Ice;
use pocketmine\entity\Effect;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class SpeedC extends DetectionBase {
	protected float $reward;

	public function __construct(User $user, string $name, $data) {
		parent::__construct($user, $name, $data);
		$this->reward = $this->getConfiguration()->getReward();
	}

	public function handleReceive(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$m = $user->getMovementInfo();
			$deltaXZ = hypot($m->moveDelta->x, $m->moveDelta->z);
			$maxSpeed = $this->getSpeed(0.4);
			if ($deltaXZ > $maxSpeed &&
				$user->getMovementInfo()->timeSinceTeleport() >= 0.25 &&
				$user->getMovementInfo()->timeSinceMotion() >= 0.75 &&
				!$user->getPlayer()->isCreative(true) &&
				!$user->getPlayer()->isFlying()
			) {
				foreach ($m->verticalBlocks as $block) {
					if ($block instanceof Ice) {
						return;
					}
				}
				if (++$this->preVL > 3) {
					$this->addVL(1);
					$this->preVL = 0;
					if ($this->overflowVL()) {
						$this->fail("A=$deltaXZ E=$maxSpeed");
					}
				}
			} elseif ($deltaXZ > 0) {
				$this->rewardPreVL($this->reward);
			}
		}
	}

	private function getSpeed(float $speed) : float {
		$speed *= 1 + (0.2 * $this->getUser()->getEffectLevel(Effect::SPEED));
		return $speed;
	}
}