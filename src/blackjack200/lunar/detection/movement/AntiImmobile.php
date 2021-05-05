<?php


namespace blackjack200\lunar\detection\movement;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class AntiImmobile extends DetectionBase {
	private static Vector3 $zero;

	public function __construct(User $user, string $name, string $fmt, ?string $webhookFmt, $data) {
		parent::__construct($user, $name, $fmt, $webhookFmt, $data);
		if (!isset(self::$zero)) {
			self::$zero = new Vector3();
		}
	}

	public function handleClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$info = $user->getMovementInfo();
			$dist = $info->moveDelta->distanceSquared(self::$zero);
			if (
				$info->immobileTick > 2 &&
				$dist > 0 &&
				$info->timeSinceTeleport() > 0.07
			) {
				$msg = "dist=$dist";
				$this->addVL(1, $msg);
				if ($this->overflowVL()) {
					$this->fail($msg);
					return;
				}
				$this->revertMovement();
			}
		}
	}
}