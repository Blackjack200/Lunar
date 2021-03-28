<?php


namespace blackjack200\lunar\detection\combat;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;

class KillAura extends DetectionBase {
	protected float $radius;
	protected float $min;
	protected float $max;
	protected Slapper $slapper;

	public function __construct(User $user, string $name, $data) {
		parent::__construct($user, $name, $data);
		$this->radius = (float) $this->getConfiguration()->getExtraData()->Radius;
		$this->min = (float) $this->getConfiguration()->getExtraData()->Random->Y->Min;
		$this->max = (float) $this->getConfiguration()->getExtraData()->Random->Y->Max;
	}

	public function check(...$data) : void {
		[$flag, $move] = $data;
		$player = $this->getUser()->getPlayer();
		if ($flag !== null) {
			$tag = Entity::createBaseNBT($player);
			$skin = $player->getSkin();
			$tag->setTag(new CompoundTag("Skin", [
				new StringTag("Name", $skin->getSkinId()),
				new StringTag("PlayFabId", $skin->getPlayFabId() ?? ""),
				new ByteArrayTag("Data", $skin->getSkinData()),
				new ByteArrayTag("CapeData", $skin->getCapeData()),
				new StringTag("GeometryName", $skin->getGeometryName()),
				new ByteArrayTag("GeometryData", $skin->getGeometryData())
			]));
			$this->slapper = new Slapper($player->getLevelNonNull(), $tag, $this);
			$this->slapper->spawnTo($player);
		}
		if ($move !== null) {
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
	}

	public function destruct() : void {
		parent::destruct();
		$this->slapper->close();
	}
}