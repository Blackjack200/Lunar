<?php


namespace blackjack200\lunar\user\processor;


use blackjack200\lunar\user\User;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\NetworkChunkPublisherUpdatePacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;

class NetworkLatencyProcessor extends Processor {
	private NetworkStackLatencyPacket $chunk;
	private NetworkStackLatencyPacket $ping;
	private float $lastSent;

	public function __construct(User $user) {
		parent::__construct($user);
		$this->chunk = new NetworkStackLatencyPacket();
		$this->chunk->needResponse = true;
		$this->chunk->timestamp = random_int(100, 1000000) * 1000;

		$this->ping = new NetworkStackLatencyPacket();
		$this->ping->needResponse = true;
		$this->ping->timestamp = random_int(0, 99) * 1000;

		$this->lastSent = microtime(true);

	}

	public function processClient(DataPacket $packet) : void {
		$user = $this->getUser();
		if ($packet instanceof NetworkStackLatencyPacket) {
			if ($packet->timestamp === $this->chunk->timestamp) {
				$user->getLatencyInfo()->receiveChunk = true;
				$this->chunk->timestamp = random_int(100, 1000000) * 1000;
				$this->chunk->encode();
			} elseif ($packet->timestamp === $this->ping->timestamp) {
				$this->ping->timestamp = random_int(0, 99) * 1000;
				$this->ping->encode();
				$ms = (microtime(true) - $this->lastSent) * 1000;
				$this->getUser()->getPlayer()->dataPacket($this->ping);
				$this->lastSent = microtime(true);
			}
		}

		if ($packet instanceof MovePlayerPacket) {
			$this->getUser()->getPlayer()->dataPacket($this->ping);
		}
	}

	public function processServer(DataPacket $packet) : void {
		if ($packet instanceof NetworkChunkPublisherUpdatePacket) {
			$this->getUser()->getPlayer()->dataPacket($this->chunk);
			$this->getUser()->getLatencyInfo()->receiveChunk = false;
		}
	}
}