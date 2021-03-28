<?php


namespace blackjack200\lunar\user\processor;


use blackjack200\lunar\user\info\MovementInfo;
use blackjack200\lunar\user\User;
use blackjack200\lunar\utils\AABB;
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
		$this->getUser()->getMoveData()->lastLocation = $this->getUser()->getPlayer()->asLocation();
		$this->getUser()->getMoveData()->location = $this->getUser()->getPlayer()->asLocation();
		$this->getUser()->getMoveData()->moveDelta = new Vector3();
		$this->getUser()->getMoveData()->lastMoveDelta = new Vector3();
	}

	public function processClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$moveData = $user->getMoveData();

			$this->updateLocation($moveData);

			$this->updateMoveDelta($moveData);

			if ($moveData->moveDelta->lengthSquared() > 0.001) {
				$verticalBlocks = AABB::getCollisionBlocks($user->getPlayer()->getLevelNonNull(), $this->getUser()->getPlayer()->getBoundingBox()->expandedCopy(0.1, 0.2, 0.1));

				//$horizonBlocks = $this->getUser()->getPlayer()->getLevelNonNull()->getCollisionBlocks($this->getUser()->getPlayer()->getBoundingBox()->expandedCopy(0.2, -0.1, 0.2));
				$moveData->onGround = count($verticalBlocks) !== 0;
				var_dump($moveData->onGround);
			}
		}
	}

	public function updateLocation(MovementInfo $moveData) : void {
		$moveData->lastLocation = $moveData->location;
		$moveData->location = $this->getUser()->getPlayer()->asLocation();
	}

	public function updateMoveDelta(MovementInfo $moveData) : void {
		$moveData->lastMoveDelta = $moveData->moveDelta;
		$moveData->moveDelta = $moveData->location->subtract($moveData->lastLocation)->asVector3();
	}

	public function check(...$data) : void {
		$moveData = $this->getUser()->getMoveData();
		if (!$moveData->onGround) {
			$moveData->offGroundTick++;
		} else {
			$moveData->offGroundTick = 0;
		}
	}
}