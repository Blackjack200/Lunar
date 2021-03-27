<?php


namespace blackjack200\lunar\detection\action;


use blackjack200\lunar\detection\DetectionBase;

class AutoClicker extends DetectionBase {
	public function check(...$data) : void {
		$CPS = $this->getUser()->CPS;
		if ($CPS >= $this->getConfiguration()->getExtraData()->MaxCPS) {
			$this->fail("CPS={$CPS}");
		}
	}
}