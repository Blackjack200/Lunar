<?php


namespace blackjack200\lunar\user\processor;


use blackjack200\lunar\user\info\PlayerMovementInfo;
use blackjack200\lunar\user\User;
use blackjack200\lunar\utils\AABB;
use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\item\ItemIds;
use pocketmine\level\Location;
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
		$user = $this->getUser();
		$info = $user->getMovementInfo();
		$info->velocity = new Vector3();
		$info->moveDelta = new Vector3();
		$info->location = $user->getPlayer()->asLocation();
		$this->updateLocation($info, $info->location);
		$this->updateMoveDelta($info);
	}

	public function updateLocation(PlayerMovementInfo $movementInfo, Location $location) : void {
		$movementInfo->lastLocation = $movementInfo->location;
		$movementInfo->location = $location;
	}

	public function updateMoveDelta(PlayerMovementInfo $movementInfo) : void {
		$movementInfo->lastMoveDelta = $movementInfo->moveDelta;
		$movementInfo->moveDelta = $movementInfo->location->subtract($movementInfo->lastLocation)->asVector3();
	}

	public function processClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$player = $user->getPlayer();
			if ($this->valid($packet, $player)) {
				$info = $user->getMovementInfo();
				$location = Location::fromObject($packet->position->round(4)->subtract(0, 1.62, 0), $player->getLevel(), $packet->yaw, $packet->pitch);

				$this->updateLocation($info, $location);

				$this->updateMoveDelta($info);

				$dist = $info->moveDelta->distanceSquared($player);
				if ($dist > 0.006) {
					if ($this->buffer++ > 4) {
						$this->buffer = 0;
						$info->locationHistory->push($player->asLocation());
					}
					$AABB = AABB::fromPosition($location)->expandedCopy(0.5, 0.2, 0.5);
					$verticalBlocks = AABB::getCollisionBlocks($location->getLevel(), $AABB);
					$info->lastOnGround = $info->onGround;
					$info->onGround = count($player->getLevelNonNull()->getCollisionBlocks($AABB, true)) !== 0;
					$info->lastActualOnGround = $info->actualOnGround;
					$info->actualOnGround = $info->onGround;
					$info->onIce = false;

					$info->inVoid = $location->y < -15;
					$info->checkFly = !$player->isImmobile() && !$player->hasEffect(Effect::LEVITATION);
					foreach ($verticalBlocks as $block) {
						/** @var Block $block */
						$id = $block->getId();
						if (in_array($id, self::ICE, true)) {
							$user->getExpiredInfo()->set('ice');
							$info->onIce = true;
							continue;
						}

						if (
							$id === Block::SLIME_BLOCK ||
							$id === Block::COBWEB ||
							$block->isTransparent() ||
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
	}

	private function valid(MovePlayerPacket $packet, Vector3 $oldPos) : bool {
		$rawPos = $packet->position;
		$dist = $rawPos->distanceSquared($oldPos);
		if (!($dist !== 0.0 && $dist < 100)) {
			return false;
		}
		foreach ([$rawPos->x, $rawPos->y, $rawPos->z, $packet->yaw, $packet->headYaw, $packet->pitch] as $float) {
			if (is_infinite($float) || is_nan($float)) {
				return false;
			}
		}
		$packet->yaw = fmod($packet->yaw, 360);
		$packet->pitch = fmod($packet->pitch, 360);
		if ($packet->yaw < 0) {
			$packet->yaw += 360;
		}
		return true;
	}

	public function check(...$data) : void {
		$user = $this->getUser();
		$player = $user->getPlayer();
		if ($player->spawned) {
			$info = $user->getMovementInfo();
			if (!$info->checkFly) {
				$user->getExpiredInfo()->set('checkFly');
			}
			if (!$info->onGround) {
				$info->inAirTick++;
				$info->onGroundTick = 0;
			} else {
				$info->inAirTick = 0;
				$info->onGroundTick++;
			}
			if ($player->isImmobile()) {
				$info->immobileTick++;
			} else {
				$info->immobileTick = 0;
			}
			$info2 = $user->getActionInfo();
			if ($info2->isFlying) {
				$info->flightTick++;
			} elseif ($info->flightTick !== 0) {
				$info->flightTick = 0;
				$user->getExpiredInfo()->set('flight');
			}

			if ($info2->isSprinting) {
				$info->sprintTick++;
			} elseif ($info->sprintTick !== 0) {
				$info->sprintTick = 0;
				$user->getExpiredInfo()->set('sprint');
			}
		}
	}
}