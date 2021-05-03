<?php


namespace blackjack200\lunar\configuration;


use blackjack200\lunar\utils\Objects;

final class DetectionConfiguration {
	private string $class;
	private string $name;
	private int $punishment;
	private int $maxVL;
	private float $reward;
	private bool $enable;
	private object $extraData;
	private bool $suppress;

	public function __construct(string $class, string $name, array $data, bool $recursiveObject) {
		$this->class = $class;
		$this->name = $name;
		$this->punishment = Punishment::fromString($data['Punishment']);
		$this->enable = $data['Enable'];
		$this->maxVL = $data['MaxVL'] ?? -1;
		$this->reward = $data['Reward'] ?? 1;
		$this->suppress = $data['Suppress'] ?? false;
		unset($data['Punishment'], $data['Enable'], $data['MaxVL'], $data['Reward'], $data['Suppress']);
		$this->extraData = $recursiveObject ? Objects::convert($data) : (object) $data;
	}

	public function getClass() : string { return $this->class; }

	public function getName() : string { return $this->name; }

	public function getExtraData() : object { return $this->extraData; }

	public function getPunishment() : int { return $this->punishment; }

	public function isEnable() : bool { return $this->enable; }

	public function hasMaxVL() : bool { return $this->maxVL > 0; }

	public function getMaxVL() : float { return $this->maxVL; }

	public function getReward() : float { return $this->reward; }

	public function isSuppress() : bool { return $this->suppress; }
}