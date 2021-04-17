<?php

namespace blackjack200\lunar\user;

use blackjack200\lunar\detection\Detection;
use blackjack200\lunar\detection\DetectionTrigger;
use blackjack200\lunar\DetectionRegistry;
use blackjack200\lunar\user\info\PlayerActionInfo;
use blackjack200\lunar\user\info\PlayerMovementInfo;
use blackjack200\lunar\user\processor\InGameProcessor;
use blackjack200\lunar\user\processor\LoginProcessor;
use blackjack200\lunar\user\processor\MovementProcessor;
use blackjack200\lunar\user\processor\PlayerActionProcessor;
use blackjack200\lunar\user\processor\Processor;
use pocketmine\Player;

class User implements DetectionTrigger {
	public LoginData $loginData;
	public int $CPS = 0;
	public float $lastHurt;
	private Player $player;
	/** @var Detection[] */
	private array $detections;
	/** @var Processor[] */
	private array $processors = [];
	private PlayerMovementInfo $moveData;
	private PlayerActionInfo $actionInfo;
	private float $joinTime;

	public function __construct(Player $player) {
		$this->player = $player;
		$this->joinTime = microtime(true);
		$this->lastHurt = microtime(true);
		$this->moveData = new PlayerMovementInfo();
		$this->actionInfo = new PlayerActionInfo();
		$this->processors[] = new LoginProcessor($this);
		$this->processors[] = new InGameProcessor($this);
		$this->processors[] = new MovementProcessor($this);
		$this->processors[] = new PlayerActionProcessor($this);

		$this->detections = DetectionRegistry::getDetections($this);
	}

	public function timeSinceHurt() : float {
		return microtime(true) - $this->lastHurt;
	}

	public function __destruct() {
		foreach ($this->detections as $detection) {
			$detection->destruct();
		}

		foreach ($this->processors as $processor) {
			$processor->destruct();
		}
	}

	public function close() : void {
		foreach ($this->detections as $detection) {
			$detection->close();
		}

		foreach ($this->processors as $processor) {
			$processor->close();
		}
	}

	public function trigger(string $class, ...$data) : void {
		foreach ($this->detections as $detection) {
			if ($detection instanceof $class) {
				$detection->check(...$data);
				return;
			}
		}
	}

	public function triggerProcessor(string $class, ...$data) : void {
		foreach ($this->processors as $processor) {
			if ($processor instanceof $class) {
				$processor->check(...$data);
				return;
			}
		}
	}

	public function getDetections() : array {
		return $this->detections;
	}

	public function getProcessors() : array {
		return $this->processors;
	}

	public function getPlayer() : Player {
		return $this->player;
	}

	public function getMovementInfo() : PlayerMovementInfo {
		return $this->moveData;
	}

	public function getActionInfo() : PlayerActionInfo {
		return $this->actionInfo;
	}

	public function timeSinceJoin() : float {
		return microtime(true) - $this->joinTime;
	}

	public function getEffectLevel(int $id) : int {
		$level = 0;
		$effect = $this->player->getEffect($id);
		if ($effect !== null) {
			$level = $effect->getEffectLevel();
		}
		return $level;
	}
}
