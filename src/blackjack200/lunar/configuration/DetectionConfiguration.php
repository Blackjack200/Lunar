<?php


namespace blackjack200\lunar\configuration;


class DetectionConfiguration {
	private int $punishment;
	private int $maxVL;
	private float $reward;
	private float $enable;
	private object $extraData;

	public function __construct(array $data) {
		$this->punishment = Punishment::parsePunishment($data['Punishment']);
		$this->enable = Boolean::stob($data['Enable']);
		$this->maxVL = $data['MaxVL'] ?? -1;
		$this->reward = $data['Reward'] ?? 1;
		unset($data['Punishment'], $data['Enable'], $data['MaxVL'], $data['Reward']);
		$this->extraData = (object) $data;
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

	public function getReward() {
		return $this->reward;
	}
}