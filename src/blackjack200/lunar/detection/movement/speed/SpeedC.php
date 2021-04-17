<?php


namespace blackjack200\lunar\detection\movement\speed;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\entity\Effect;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class SpeedC extends DetectionBase {
	protected float $reward;
	protected float $lastLostSpeed = 0.0;

	public function __construct(User $user, string $name, $data) {
		parent::__construct($user, $name, $data);
		$this->reward = $this->getConfiguration()->getReward();
		$this->lastLostSpeed = microtime(true);
	}

	public function handleClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$info = $user->getMovementInfo();
			$deltaXZ = hypot($info->moveDelta->x, $info->moveDelta->z);
			$maxSpeed = $this->getSpeed();
			$t = $this->lastLostSpeed - microtime(true);

			if ($deltaXZ > $maxSpeed &&
				$t < 0 &&
				!$info->onIce &&
				$info->checkFly &&
				!$info->inVoid &&
				$info->timeSinceTeleport() >= 0.25 &&
				$info->timeSinceMotion() >= 0.75 &&
				!$user->getPlayer()->isCreative() &&
				!$user->getPlayer()->isFlying()
			) {
				if ($this->preVL++ > 2) {
					$this->addVL(1);
					$this->preVL = 0;
					$this->revertMovement();
					if ($this->overflowVL()) {
						$this->fail("A=$deltaXZ E=$maxSpeed");
					}
				}
			} elseif ($deltaXZ > 0) {
				$this->preVL *= $this->reward;
			}
		}
	}

	private function getSpeed() : float {
		return 0.4 * (1 + (0.2 * $this->getSpeedLevel()));
	}

	public function getSpeedLevel() : int {
		$effect = $this->getUser()->getPlayer()->getEffect(Effect::SPEED);
		if ($effect !== null) {
			if ($effect->getDuration() === 1) {
				$this->lastLostSpeed = microtime(true) + 2;
			}
			return $effect->getEffectLevel();
		}
		return 0;
	}
}