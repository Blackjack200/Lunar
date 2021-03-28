<?php

namespace blackjack200\lunar\user;

use blackjack200\lunar\configuration\DetectionConfiguration;
use blackjack200\lunar\detection\action\AutoClicker;
use blackjack200\lunar\detection\action\NukerA;
use blackjack200\lunar\detection\combat\KillAura;
use blackjack200\lunar\detection\Detection;
use blackjack200\lunar\detection\DetectionTrigger;
use blackjack200\lunar\detection\packet\ClientDataFaker;
use blackjack200\lunar\Lunar;
use blackjack200\lunar\user\processor\InGameProcessor;
use blackjack200\lunar\user\processor\LoginProcessor;
use blackjack200\lunar\user\processor\Processor;
use pocketmine\Player;

class User implements DetectionTrigger {
	public ClientData $clientData;
	private Player $player;
	/** @var Detection[] */
	private array $detections = [];
	/** @var Processor[] */
	private array $processors = [];
	public int $CPS = 0;

	public function __construct(Player $player) {
		$this->player = $player;
		$this->processors[] = new LoginProcessor($this);
		$this->processors[] = new InGameProcessor($this);
		//TODO This shouldn't be hardcoded
		$this->registerStandardDetection(ClientDataFaker::class, 'ClientDataFaker');
		$this->registerStandardDetection(NukerA::class, 'NukerA');
		$this->registerStandardDetection(AutoClicker::class, 'AutoClicker');
		$this->registerStandardDetection(KillAura::class, 'KillAura');
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
				$data
			);
		}
	}

	public function __destruct() {
		$this->close();
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

	public function close() : void {
		foreach ($this->detections as $detection) {
			$detection->destruct();
		}

		foreach ($this->processors as $processor) {
			$processor->destruct();
		}
	}
}
