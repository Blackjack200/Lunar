<?php


namespace blackjack200\lunar\user\processor;


use blackjack200\lunar\user\info\LocationInfo;
use blackjack200\lunar\user\User;
use blackjack200\lunar\utils\AABB;
use pocketmine\block\Block;
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

				$dist = $info->delta->current()->distanceSquared($player);
				if ($dist > 0.006) {
					if ($this->buffer++ > 4) {
						$this->buffer = 0;
						$info->history->push($player->asLocation());
					}
					$AABB = AABB::fromPosition($location)->expandedCopy(0.5, 0.2, 0.5);
					$verticalBlocks = AABB::getCollisionBlocks($location->getLevel(), $AABB);
					$inVoid = $location->y < -15;
					$info->onGround->push(count($player->getLevelNonNull()->getCollisionBlocks($AABB, true)) !== 0 || $inVoid);
					$info->onIce->push(false);

					$info->inVoid->push($inVoid);
					//$info->checkFly = !$player->isImmobile() && !$player->hasEffect(Effect::LEVITATION);
					foreach ($verticalBlocks as $block) {
						/** @var Block $block */
						$id = $block->getId();
						if (in_array($id, self::ICE, true)) {
							$user->getExpiredInfo()->set('ice');
							$info->onIce->push(true);
							continue;
						}

						if ($block->canClimb()) {
							$info->onClimbable->push(true);
						}
						if ($id === Block::COBWEB) {
							$info->inCobweb->push(true);
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

	public function updateLocation(LocationInfo $info, Location $location) : void {
		$info->location->push($location);
	}

	public function updateMoveDelta(LocationInfo $info) : void {
		$info->delta->push($info->location->current()->subtract($info->location->last())->asVector3());
	}

	public function check(...$data) : void {
		$user = $this->getUser();
		$player = $user->getPlayer();
		if ($player->spawned) {
			$info = $user->getMovementInfo();
			if (!$info->onGround->current()) {
				$info->air->add();
			} else {
				$info->air->reset();
			}

			if ($info->onIce->current()) {
				$info->ice->add();
			} else {
				$info->ice->reset();
			}

			if ($info->onClimbable->current()) {
				$info->climbable->add();
			} else {
				$info->climbable->reset();
			}

			if ($info->inCobweb->current()) {
				$info->cobweb->add();
			} else {
				$info->cobweb->reset();
			}

			if ($info->inLiquid->current()) {
				$info->liquid->add();
			} else {
				$info->liquid->reset();
			}
		}
	}
}