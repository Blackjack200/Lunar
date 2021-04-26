<?php


namespace blackjack200\lunar\detection\combat;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;

class KillAuraA extends DetectionBase {
	protected float $radius;
	protected int $min;
	protected int $max;
	protected Slapper $slapper;

	public function __construct(User $user, string $name, string $fmt, ?string $webhookFmt, $data) {
		parent::__construct($user, $name, $fmt, $webhookFmt, $data);
		$this->radius = (float) $this->getConfiguration()->getExtraData()->Radius;
		$this->min = (int) $this->getConfiguration()->getExtraData()->Random->Y->Min;
		$this->max = (int) $this->getConfiguration()->getExtraData()->Random->Y->Max;

		$player = $this->getUser()->getPlayer();
		$tag = Entity::createBaseNBT($player);
		$tag->setTag($player->namedtag->getTag('Skin'));
		$this->slapper = new Slapper($player->getLevelNonNull(), $tag, $this);
		$this->slapper->spawnTo($player);
	}

	public function check(...$data) : void {
		$player = $this->getUser()->getPlayer();
		$location = clone $player->getLocation();
		$count = random_int($this->min, $this->max);
		$this->slapper->teleport(
			$location->add(
				$this->radius * cos($count) - $this->radius * sin($count),
				1,
				$this->radius * sin($count) + $this->radius * cos($count)
			)
		);
		if (random_int(0, 5) === 0) {
			$pk = new PlayerActionPacket();
			$pk->entityRuntimeId = $this->slapper->getId();
			$pk->action = PlayerActionPacket::ACTION_JUMP;
			[$pk->x, $pk->y, $pk->z] = [(int) $this->slapper->getX(), (int) $this->slapper->getY(), (int) $this->slapper->getZ()];
			$pk->face = Vector3::SIDE_SOUTH;
			$player->sendDataPacket($pk);
		}
	}

	public function finalize() : void {
		$this->slapper->close();
		parent::finalize();
	}
}