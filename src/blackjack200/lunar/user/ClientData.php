<?php


namespace blackjack200\lunar\user;


use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\utils\Utils;

class ClientData {
	protected object $clientData, $chainData;

	public function __construct(LoginPacket $packet) {
		$packet = clone $packet;
		$this->clientData = (object) $packet->clientData;
		$this->chainData = (object) Utils::decodeJWT($packet->chainData['chain'][2] ?? array_pop($packet->chainData['chain']));
	}

	public function getClientData() : object {
		return $this->clientData;
	}

	public function getChainData() : object {
		return $this->chainData;
	}
}