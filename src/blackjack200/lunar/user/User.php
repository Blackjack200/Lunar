<?php

namespace blackjack200\lunar\user;

use blackjack200\lunar\detection\Detection;
use blackjack200\lunar\detection\DetectionTrigger;
use blackjack200\lunar\DetectionRegistry;
use blackjack200\lunar\user\info\ExpiredInfo;
use blackjack200\lunar\user\info\PlayerActionInfo;
use blackjack200\lunar\user\info\PlayerMovementInfo;
use blackjack200\lunar\user\processor\InGameProcessor;
use blackjack200\lunar\user\processor\LoginProcessor;
use blackjack200\lunar\user\processor\MovementProcessor;
use blackjack200\lunar\user\processor\PlayerActionProcessor;
use blackjack200\lunar\user\processor\Processor;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\Player;

final class User implements DetectionTrigger {
	public LoginData $loginData;
	public StartGamePacket $startGame;
	public int $CPS = 0;
	public float $lastHurt;
	private Player $player;
	/** @var Detection[] */
	private array $detections;
	/** @var Processor[] */
	private array $processors = [];
	private PlayerMovementInfo $moveData;
	private PlayerActionInfo $actionInfo;
	private ExpiredInfo $expiredInfo;
	private float $joinTime;
	private bool $closed = false;

	public function __construct(Player $player) {
		$this->player = $player;
		$this->joinTime = microtime(true);
		$this->lastHurt = microtime(true);
		$this->moveData = new PlayerMovementInfo();
		$this->actionInfo = new PlayerActionInfo();
		$this->expiredInfo = new ExpiredInfo(32);
		$this->processors[LoginProcessor::class] = new LoginProcessor($this);
		$this->processors[InGameProcessor::class] = new InGameProcessor($this);
		$this->processors[MovementProcessor::class] = new MovementProcessor($this);
		$this->processors[PlayerActionProcessor::class] = new PlayerActionProcessor($this);

		$this->detections = DetectionRegistry::getDetections($this);
	}

	public function timeSinceHurt() : float { return microtime(true) - $this->lastHurt; }

	public function close() : void {
		$this->closed = true;
		foreach ($this->detections as $detection) {
			$detection->finalize();
		}

		foreach ($this->processors as $processor) {
			$processor->finalize();
		}

		$this->detections = [];
		$this->processors = [];
	}

	public function isClosed() : bool { return $this->closed; }

	public function trigger(string $class, ...$data) : void {
		$detection = $this->detections[$class] ?? null;
		if ($detection !== null) {
			$detection->check(...$data);
		}
	}

	public function triggerProcessor(string $class, ...$data) : void {
		$processor = $this->processors[$class] ?? null;
		if ($processor !== null) {
			$processor->check(...$data);
		}
	}

	public function getDetections() : array { return $this->detections; }

	public function getProcessors() : array { return $this->processors; }

	public function getPlayer() : Player { return $this->player; }

	public function getMovementInfo() : PlayerMovementInfo { return $this->moveData; }

	public function getActionInfo() : PlayerActionInfo { return $this->actionInfo; }

	public function getExpiredInfo() : ExpiredInfo { return $this->expiredInfo; }

	public function timeSinceJoin() : float { return microtime(true) - $this->joinTime; }

	public function getEffectLevel(int $id) : int {
		$level = 0;
		$effect = $this->player->getEffect($id);
		if ($effect !== null) {
			$level = $effect->getEffectLevel();
		}
		return $level;
	}
}
