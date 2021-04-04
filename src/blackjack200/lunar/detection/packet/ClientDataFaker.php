<?php


namespace blackjack200\lunar\detection\packet;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\network\mcpe\protocol\types\DeviceOS;

class ClientDataFaker extends DetectionBase {
	public function check(...$data) : void {
		$deviceOS = $this->getUser()->clientData->getClientData()->DeviceOS;
		$deviceModel = $this->getUser()->clientData->getClientData()->DeviceModel;

		if ($deviceOS === DeviceOS::ANDROID && trim($deviceModel) === '') {
			$deviceOS = 20;
		}

		$pass = !in_array(
				$deviceOS,
				$this->getConfiguration()->getExtraData()->DeviceOS,
				true) ||
			in_array(
				$deviceModel,
				$this->getConfiguration()->getExtraData()->DeviceModel,
				true);

		if ($deviceOS !== DeviceOS::WINDOWS_10 && isset($this->getUser()->clientData->getChainData()->extraData->titleId)) {
			$titleId = $this->getUser()->clientData->getChainData()->extraData->titleId;
			$pass = $pass || ($titleId === '896928775');
		}
		if ($pass) {
			$this->fail('LoginData is incorrect');
		}
	}
}