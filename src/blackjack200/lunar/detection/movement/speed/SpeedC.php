<?php


namespace blackjack200\lunar\detection\movement\speed;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\entity\Effect;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class SpeedC extends DetectionBase {
	protected float $reward;

	public function __construct(User $user, string $name, string $fmt, ?string $webhookFmt, $data) {
		parent::__construct($user, $name, $fmt, $webhookFmt, $data);
		$this->reward = $this->getConfiguration()->getReward();
	}

	public function handleClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$info = $user->getMovementInfo();
			$deltaXZ = hypot($info->moveDelta->x, $info->moveDelta->z);
			$maxSpeed = $this->getSpeed();

			if ($deltaXZ > $maxSpeed &&
				$info->checkFly &&
				!$info->inVoid &&
				!$user->getActionInfo()->isFlying &&
				$info->timeSinceTeleport() >= 0.25 &&
				$info->timeSinceMotion() >= 0.75 &&
				!$user->getPlayer()->isCreative() &&
				$user->getExpiredInfo()->duration(Effect::SPEED) > 1 &&
				$user->getExpiredInfo()->duration('flight') > 1
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
		$user = $this->getUser();
		$ret = 0.4 * (1 + (0.2 * $this->getUser()->getEffectLevel(Effect::SPEED)));
		if ($user->getExpiredInfo()->duration('ice') < 1) {
			$ret = ($ret / 0.6) * 0.98;
		}
		return $ret;
	}
}