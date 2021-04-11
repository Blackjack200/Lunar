<?php


namespace blackjack200\lunar\user\processor;


use blackjack200\lunar\user\info\PlayerMovementInfo;
use blackjack200\lunar\user\User;
use blackjack200\lunar\utils\AABB;
use pocketmine\block\Block;
use pocketmine\block\Door;
use pocketmine\block\Ladder;
use pocketmine\block\Trapdoor;
use pocketmine\block\Vine;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class MovementProcessor extends Processor {
	private const ICE = [ItemIds::ICE, ItemIds::PACKED_ICE, ItemIds::FROSTED_ICE];
	/** @var Vector3 */
	private static $emptyVector3;
	protected int $buffer = 0;

	public function __construct(User $user) {
		parent::__construct($user);
		if (self::$emptyVector3 === null) {
			self::$emptyVector3 = new Vector3();
		}
		$this->getUser()->getMovementInfo()->lastLocation = $this->getUser()->getPlayer()->asLocation();
		$this->getUser()->getMovementInfo()->location = $this->getUser()->getPlayer()->asLocation();
		$this->getUser()->getMovementInfo()->moveDelta = new Vector3();
		$this->getUser()->getMovementInfo()->lastMoveDelta = new Vector3();
	}

	public function processClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$info = $user->getMovementInfo();
			$player = $user->getPlayer();

			$this->updateLocation($info);

			$this->updateMoveDelta($info);

			$dist = $info->moveDelta->lengthSquared();
			if ($dist > 0.003) {
				if ($dist > 0.0042 && $this->buffer++ > 20) {
					$this->buffer = 0;
					//$player->sendMessage("record" . random_int(1, 114514));
					$info->stack->push($player->asLocation());
				}
				$verticalBlocks = AABB::getCollisionBlocks($player->getLevelNonNull(), $player->getBoundingBox()->expandedCopy(0.1, 0.2, 0.1));
				$info->lastOnGround = $info->onGround;
				$info->onGround = $player->isOnGround();
				$info->inVoid = $player->getY() < -15;
				$info->checkFly = !$player->isImmobile();
				foreach ($verticalBlocks as $block) {
					if (!$info->onGround) {
						$info->onGround = true;
					}
					$id = $block->getId();
					if (in_array($id, self::ICE, true)) {
						$info->onIce = true;
						continue;
					}

					if (
						$id === Block::SLIME_BLOCK ||
						$id === Block::COBWEB ||
						$block instanceof Door ||
						$block instanceof Trapdoor ||
						$block instanceof Vine ||
						$block instanceof Ladder ||
						$block->canClimb() ||
						$block->canBeFlowedInto()
					) {
						$info->checkFly = false;
						$info->onGround = true;
						break;
					}
				}
				//$this->getUser()->getPlayer()->sendPopup('check=' . Boolean::btos($info->checkFly) . ' on=' . Boolean::btos($info->onGround) . ' tick=' . $info->inAirTick);
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
			$moveData->inAirTick++;
			$moveData->onGroundTick = 0;
		} else {
			$moveData->inAirTick = 0;
			$moveData->onGroundTick++;
		}
	}
}