<?php


namespace blackjack200\lunar\detection\packet;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\TextPacket;

class BadPacketC extends DetectionBase {
	private static Vector3 $zero;

	public function __construct(User $user, string $name, string $fmt, ?string $webhookFmt, $data) {
		parent::__construct($user, $name, $fmt, $webhookFmt, $data);
		if (!isset(self::$zero)) {
			self::$zero = new Vector3();
		}
	}

	public function handleClient(DataPacket $packet) : void {
		if ($packet instanceof TextPacket && $packet->type === TextPacket::TYPE_CHAT) {
			$user = $this->getUser();
			$info = $user->getMovementInfo();
			if (
				$info->checkFly &&
				$info->timeSinceMotion() > 0.5 &&
				$info->timeSinceTeleport() > 1 &&
				$info->moveDelta->distanceSquared(self::$zero) > 0
			) {
				$this->addVL(1);
				if ($this->overflowVL()) {
					$this->fail('invalid chat');
				}
			}
		}
	}
}