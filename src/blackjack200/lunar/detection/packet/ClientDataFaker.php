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

		if ($pass) {
			$this->fail("LoginData Blocked OS=$deviceOS MODEL=$deviceModel");
			return;
		}
		$chainData = $loginData->getChainData();
		if (isset($chainData->extraData->titleId) && is_string($chainData->extraData->titleId)) {
			$titleIdPass = false;
			$titleId = $chainData->extraData->titleId;
			switch ($deviceOS) {
				case DeviceOS::WINDOWS_10:
					$titleIdPass = ($titleId !== '896928775');
					break;
				case DeviceOS::ANDROID:
				case 20:
					$titleIdPass = ($titleId !== '1739947436');
					break;
				case DeviceOS::IOS:
					$titleIdPass = ($titleId === '896928775');
					break;
				case DeviceOS::NINTENDO:
					$titleIdPass = ($titleId !== '2047319603');
					break;
			}

			if ($titleIdPass) {
				$this->fail("LoginData mismatched id=$titleId OS=$deviceOS MODEL=$deviceModel");
				return;
			}
		}
		$this->getUser()->trigger(AntiToolBox::class);
	}
}