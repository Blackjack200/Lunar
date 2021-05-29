<?php


namespace blackjack200\lunar\detection\packet;


use blackjack200\lunar\detection\DetectionBase;

class AntiToolBox extends DetectionBase {
	public function check(...$data) : void {
		$loginData = $this->getUser()->loginData;
		$clientData = $loginData->getClientData();
		$deviceOS = $clientData->DeviceOS;
		$deviceModel = $clientData->DeviceModel;
		if ($deviceOS !== 1) {
			return;
		}
		if ((int) $clientData->ClientRandomId === 0) {
			$this->log('data=' . json_encode($clientData));
			$this->fail('ClientDataIncorrect ZIZ');
			return;
		}

		$name = explode(' ', $deviceModel);
		if (!isset($name[0])) {
			return;
		}
		$check = $name[0];
		$check = strtoupper($check);
		if ($check !== $name[0]) {
			$this->log('data=' . json_encode($clientData));
			$this->fail('ClientDataIncorrect ZMZ');
		}
	}
}