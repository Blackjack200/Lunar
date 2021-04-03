<?php

namespace blackjack200\lunar\user;

use blackjack200\lunar\configuration\DetectionConfiguration;
use blackjack200\lunar\detection\action\AutoClicker;
use blackjack200\lunar\detection\action\NukerA;
use blackjack200\lunar\detection\combat\KillAura;
use blackjack200\lunar\detection\combat\MultiAura;
use blackjack200\lunar\detection\Detection;
use blackjack200\lunar\detection\DetectionTrigger;
use blackjack200\lunar\detection\movement\FlyA;
use blackjack200\lunar\detection\movement\FlyB;
use blackjack200\lunar\detection\movement\SpeedA;
use blackjack200\lunar\detection\movement\SpeedC;
use blackjack200\lunar\detection\packet\BadPacketA;
use blackjack200\lunar\detection\packet\ClientDataFaker;
use blackjack200\lunar\Lunar;
use blackjack200\lunar\user\info\PlayerActionInfo;
use blackjack200\lunar\user\info\PlayerMovementInfo;
use blackjack200\lunar\user\processor\InGameProcessor;
use blackjack200\lunar\user\processor\LoginProcessor;
use blackjack200\lunar\user\processor\MovementProcessor;
use blackjack200\lunar\user\processor\PlayerActionProcessor;
use blackjack200\lunar\user\processor\Processor;
use pocketmine\Player;

class User implements DetectionTrigger {
	public ClientData $clientData;
	public int $CPS = 0;
	private Player $player;
	/** @var Detection[] */
	private array $detections = [];
	/** @var Processor[] */
	private array $processors = [];
	private PlayerMovementInfo $moveData;
	private PlayerActionInfo $actionInfo;
	private float $joinTime;

	public function __construct(Player $player) {
		$this->player = $player;
		$this->joinTime = microtime(true);
		$this->moveData = new PlayerMovementInfo();
		$this->actionInfo = new PlayerActionInfo();
		$this->processors[] = new LoginProcessor($this);
		$this->processors[] = new InGameProcessor($this);
		$this->processors[] = new MovementProcessor($this);
		$this->processors[] = new PlayerActionProcessor($this);
		//TODO This shouldn't be hardcoded
		$this->registerStandardDetection(ClientDataFaker::class, 'ClientDataFaker');
		$this->registerStandardDetection(NukerA::class, 'NukerA');

		$this->registerStandardDetection(AutoClicker::class, 'AutoClicker');
		$this->registerStandardDetection(KillAura::class, 'KillAura');
		$this->registerStandardDetection(MultiAura::class, 'MultiAura');

		$this->registerStandardDetection(SpeedA::class, 'SpeedA');
		$this->registerStandardDetection(SpeedC::class, 'SpeedC');
		$this->registerStandardDetection(FlyA::class, 'FlyA');
		$this->registerStandardDetection(FlyB::class, 'FlyB');
		$this->registerStandardDetection(BadPacketA::class, 'BadPacketA');
	}

	/**
	 * @param class-string<Detection> $class
	 */
	private function registerStandardDetection(string $class, string $name) : void {
		$data = Lunar::getInstance()->getConfiguration()[$name];
		if ($data instanceof DetectionConfiguration && $data->isEnable()) {
			$this->detections[] = new $class(
				$this,
				$name,
				clone $data
			);
		}
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
}
