<?php


namespace blackjack200\lunar\detection\action;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;

class AutoClicker extends DetectionBase {
	protected $maxCPS;

	public function __construct(User $user, string $name, $data) {
		parent::__construct($user, $name, $data);
		$this->maxCPS = $this->getConfiguration()->getExtraData()->MaxCPS;
	}

	public function check(...$data) : void {
		$CPS = $this->getUser()->CPS;
		if ($CPS >= $this->maxCPS) {
			$this->addVL(1);
			if ($this->overflowVL()) {
				$this->fail("CPS=$CPS");
			}
		} else {
			$this->VL *= $this->getConfiguration()->getReward();
		}
	}
}