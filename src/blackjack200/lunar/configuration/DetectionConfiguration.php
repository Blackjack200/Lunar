<?php


namespace blackjack200\lunar\configuration;


class DetectionConfiguration {
	private int $punishment;
	private int $maxVL;
	private float $reward;
	private bool $enable;
	private object $extraData;
	private bool $suppress;

	public function __construct(array $data, bool $recursiveObject) {
		$this->punishment = Punishment::parsePunishment($data['Punishment']);
		$this->enable = $data['Enable'];
		$this->maxVL = $data['MaxVL'] ?? -1;
		$this->reward = $data['Reward'] ?? 1;
		$this->suppress = $data['Suppress'] ?? false;
		unset($data['Punishment'], $data['Enable'], $data['MaxVL'], $data['Reward'], $data['Suppress']);
		$this->extraData = $recursiveObject ? Objects::convert($data) : (object) $data;
	}

	public function getExtraData() : object {
		return $this->extraData;
	}

	public function getPunishment() : int {
		return $this->punishment;
	}

	public function isEnable() : bool {
		return $this->enable;
	}

	public function hasMaxVL() : bool {
		return $this->maxVL > 0;
	}

	public function getMaxVL() : float {
		return $this->maxVL;
	}

	public function getReward() : float {
		return $this->reward;
	}

	public function isSuppress() : bool {
		return $this->suppress;
	}
}