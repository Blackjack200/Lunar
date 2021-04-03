<?php


namespace blackjack200\lunar\user\processor;


use blackjack200\lunar\user\info\PlayerMovementInfo;
use blackjack200\lunar\user\User;
use blackjack200\lunar\utils\AABB;
use pocketmine\block\Block;
use pocketmine\block\Liquid;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class MovementProcessor extends Processor {
	/** @var Vector3 */
	private static $emptyVector3;
	protected $verticalAABB;

	public function __construct(User $user) {
		parent::__construct($user);
		if (self::$emptyVector3 === null) {
			self::$emptyVector3 = new Vector3();
		}
		$this->verticalAABB = $this->getUser()->getPlayer()->getBoundingBox()->expandedCopy(0.1, 0.2, 0.1);
		$this->getUser()->getMovementInfo()->lastLocation = $this->getUser()->getPlayer()->asLocation();
		$this->getUser()->getMovementInfo()->location = $this->getUser()->getPlayer()->asLocation();
		$this->getUser()->getMovementInfo()->moveDelta = new Vector3();
		$this->getUser()->getMovementInfo()->lastMoveDelta = new Vector3();
	}

	public function processClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$info = $user->getMovementInfo();

			$this->updateLocation($info);

			$this->updateMoveDelta($info);

			if ($info->moveDelta->lengthSquared() > 0.009) {
				$player = $user->getPlayer();
				$verticalBlocks = AABB::getCollisionBlocks($player->getLevelNonNull(), $player->getBoundingBox()->expandedCopy(0.1, 0.2, 0.1));
				$info->verticalBlocks = $verticalBlocks;
				//$horizonBlocks = $this->getUser()->getPlayer()->getLevelNonNull()->getCollisionBlocks($this->getUser()->getPlayer()->getBoundingBox()->expandedCopy(0.2, -0.1, 0.2));
				$info->lastOnGround = $info->onGround;
				$info->onGround = count($verticalBlocks) !== 0;
				$info->inVoid = $player->getY() < 0;

				$info->checkFly = !$player->isImmobile();

				foreach ($verticalBlocks as $block) {
					$id = $block->getId();
					if (
						$id === Block::SLIME_BLOCK ||
						$id === Block::COBWEB ||
						$block instanceof Liquid
					) {
						$info->checkFly = false;
						break;
					}
				}
			}
		}
	}

	public function updateLocation(PlayerMovementInfo $movementInfo) : void {
		$movementInfo->lastLocation = $movementInfo->location;
		$movementInfo->location = $this->getUser()->getPlayer()->asLocation();
	}

	public function updateMoveDelta(PlayerMovementInfo $movementInfo) : void {
		$movementInfo->lastMoveDelta = $movementInfo->moveDelta;
		$movementInfo->moveDelta = $movementInfo->location->subtract($movementInfo->lastLocation)->asVector3();
	}

	public function check(...$data) : void {
		$moveData = $this->getUser()->getMovementInfo();
		if (!$moveData->onGround) {
			$moveData->offGroundTick++;
			$moveData->onGroundTick = 0;
		} else {
			$moveData->offGroundTick = 0;
			$moveData->onGroundTick++;
		}
	}
}