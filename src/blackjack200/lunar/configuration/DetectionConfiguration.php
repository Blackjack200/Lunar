<?php


namespace blackjack200\lunar\configuration;


use stdClass;

class DetectionConfiguration {
	private int $punishment;
	private int $maxVL;
	private float $reward;
	private float $enable;
	private object $extraData;

	public function __construct(array $data, bool $recursiveObject) {
		$this->punishment = Punishment::parsePunishment($data['Punishment']);
		$this->enable = $data['Enable'];
		$this->maxVL = $data['MaxVL'] ?? -1;
		$this->reward = $data['Reward'] ?? 1;
		unset($data['Punishment'], $data['Enable'], $data['MaxVL'], $data['Reward']);
		$this->extraData = $recursiveObject ? $this->convert($data) : (object) $data;
	}

	/**
	 * @param mixed|object $val
	 * @return mixed|object
	 */
	private function convert($val) {
		$obj = new stdClass();
		if (is_array($val)) {
			foreach ($val as $key => $value) {
				$obj->$key = $this->convert($value);
			}
		} else {
			return $val;
		}
		return $obj;
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
}