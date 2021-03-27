<?php


namespace blackjack200\lunar\user\processor;


use blackjack200\lunar\detection\packet\ClientDataFaker;
use blackjack200\lunar\user\ClientData;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;

class LoginProcessor extends Processor {
	public function processClient(DataPacket $packet) : void {
		if ($packet instanceof LoginPacket) {
			$this->getUser()->clientData = new ClientData($packet);
			$this->getUser()->trigger(ClientDataFaker::class);
		}
	}
}