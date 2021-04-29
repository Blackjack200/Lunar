<?php


namespace blackjack200\lunar\detection;


use pocketmine\scheduler\Task;

class KickTask extends Task {
	private DetectionBase $detection;
	private string $message;

	public function __construct(string $message, DetectionBase $detection) {
		$this->detection = $detection;
		$this->message = $message;
	}

	public function onRun(int $currentTick) : void {
		$this->detection->failImpl($this->message);
	}
}