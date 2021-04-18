<?php


namespace blackjack200\lunar\user;


use blackjack200\lunar\utils\Objects;
use Exception;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\utils\Utils;
use Throwable;

class LoginData {
	protected object $clientData, $chainData;

	/**
	 * @throws Exception
	 */
	public function __construct(LoginPacket $packet) {
		$packet = clone $packet;
		$this->clientData = Objects::convert($packet->clientData);
		try {
			$this->chainData = Objects::convert(Utils::decodeJWT($packet->chainData['chain'][2] ?? array_pop($packet->chainData['chain'])));
		} catch (Throwable $e) {
			throw new Exception('chain data is not exists');
		}
	}

	public function getClientData() : object { return $this->clientData; }

	public function getChainData() : object { return $this->chainData; }
}