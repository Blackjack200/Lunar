<?php


namespace blackjack200\lunar\configuration;


class DetectionConfiguration {
	private int $punishment;
	private bool $enable;
	private object $extraData;

	public function __construct(array $data) {
		$this->punishment = Punishment::parsePunishment($data['Punishment']);
		$this->enable = Boolean::stob($data['Enable']);
		unset($data['Punishment'], $data['Enable']);
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
}