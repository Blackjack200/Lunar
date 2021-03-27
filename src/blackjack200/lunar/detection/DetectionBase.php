<?php


namespace blackjack200\lunar\detection;


use blackjack200\lunar\configuration\DetectionConfiguration;
use blackjack200\lunar\configuration\Punishment;
use blackjack200\lunar\user\User;
use pocketmine\Server;

abstract class DetectionBase implements Detection {
	/** @var User */
	private $user;
	/** @var mixed */
	private $configuration;
	private string $name;
	//TODO Violation Level

	/**
	 * @param DetectionConfiguration $data
	 */
	public function __construct(User $user, string $name, $data) {
		$this->user = $user;
		$this->name = $name;
		$this->configuration = $data;
	}

	//TODO Improve TextFormat
	public function alert(string $message) : void {
		$this->getUser()->getPlayer()->sendMessage($message);
	}

	protected function getUser() : User {
		return $this->user;
	}

	public function fail(string $message) : void {
		switch ($this->getConfiguration()->getPunishment()) {
			case Punishment::BAN():
				Server::getInstance()->getNameBans()->addBan($this->getUser()->getPlayer()->getName(), $message);
				$this->getUser()->getPlayer()->kick($message);
				break;
			case Punishment::WARN():
				$this->getUser()->getPlayer()->sendMessage($message);
				break;
			case Punishment::KICK():
				$this->getUser()->getPlayer()->kick($message);
				break;
		}
	}

	protected function getConfiguration() : DetectionConfiguration {
		return $this->configuration;
	}

	public function debug(string $message) : void {
		// TODO: Implement debug() method.
	}

	public function destruct() : void {
		$this->user = null;
	}

	public function getName() : string {
		return $this->name;
	}
}