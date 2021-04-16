<?php


namespace blackjack200\lunar\user\processor;


use blackjack200\lunar\detection\packet\ClientDataFaker;
use blackjack200\lunar\user\LoginData;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use Throwable;

class LoginProcessor extends Processor {
	public function processClient(DataPacket $packet) : void {
		if ($packet instanceof LoginPacket) {
			try {
				//TODO HACK
				$this->getUser()->loginData = new LoginData($packet);
				$this->getUser()->trigger(ClientDataFaker::class);
			} catch (Throwable $e) {

			}
		}
	}
}