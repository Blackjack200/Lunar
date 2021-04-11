<?php


namespace blackjack200\lunar\detection\packet;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\network\mcpe\protocol\types\DeviceOS;

class ClientDataFaker extends DetectionBase {
	public function check(...$data) : void {
		$loginData = $this->getUser()->loginData;
		$clientData = $loginData->getClientData();
		$deviceOS = $clientData->DeviceOS;
		$deviceModel = $clientData->DeviceModel;

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

		$chainData = $loginData->getChainData();
		if (isset($chainData->extraData->titleId)) {
			$titleId = $chainData->extraData->titleId;
			switch ($deviceOS) {
				case DeviceOS::WINDOWS_10:
					$pass = ($titleId !== '896928775');
					break;
				case DeviceOS::ANDROID:
				case 20:
					$pass = ($titleId !== '1739947436');
					break;
				case DeviceOS::IOS:
					$pass = ($titleId !== '1810924247');
					break;
				case DeviceOS::NINTENDO:
					$pass = ($titleId !== '2047319603');
					break;
			}
		}
		if ($pass) {
			$this->fail('LoginData is incorrect id=' . ($titleId ?? 'unknown') . 'data=' . $deviceOS . ' ' . $deviceModel);
		}
	}
}