<?php


namespace blackjack200\lunar\detection\packet;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\network\mcpe\protocol\types\DeviceOS;

class ClientDataFaker extends DetectionBase {
	public function check() : void {
		$deviceOS = $this->getUser()->clientData->getClientData()->DeviceOS;
		$pass = !in_array($deviceOS,
			$this->getConfiguration()->getExtraData()->DeviceOS);
		if ($deviceOS !== DeviceOS::WINDOWS_10 && isset($this->getUser()->clientData->getChainData()->extraData->titleId)) {
			$titleId = $this->getUser()->clientData->getChainData()->extraData->titleId;
			$pass = ($titleId === "896928775");
		}
		if ($pass) {
			$this->fail("LoginData is incorrect");
		}
	}
}