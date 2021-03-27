<?php


namespace blackjack200\lunar\detection;


use blackjack200\lunar\configuration\DetectionConfiguration;
use blackjack200\lunar\configuration\Punishment;
use blackjack200\lunar\Lunar;
use blackjack200\lunar\user\User;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

abstract class DetectionBase implements Detection {
	/** @var User */
	private $user;
	/** @var mixed */
	private $configuration;
	private string $name;
	//TODO Violation Level
	private float $VL = 0;
	private float $preVL = 0;

	/** @return numeric */
	public function getVL() {
		return $this->VL;
	}

	public function overflowVL() : bool {
		return $this->getConfiguration()->hasMaxVL() ? $this->VL >= $this->getConfiguration()->getMaxVL() : false;
	}

	/** @return numeric */
	public function getPreVL() {
		return $this->preVL;
	}

	public function reset() : void {
		$this->setVL(0);
		$this->setPreVL(0);
	}

	/** @param numeric $preVL */
	public function setPreVL($preVL) : void {
		$this->preVL = $preVL;
	}

	/** @param numeric $VL */
	public function setVL($VL) : void {
		$this->VL = $VL;
	}

	/** @param numeric $preVL */
	public function addPreVL($preVL) : void {
		$this->preVL += $preVL;
	}

	/** @param numeric $VL */
	public function addVL($VL, bool $silent = false) : void {
		$this->VL += $VL;
		if (!$silent) {
			$this->alert("VL={$this->VL}");
		}
	}

	/** @param numeric $val */
	public function rewardPreVL($val) : void {
		$this->preVL *= $val;
	}

	/** @param numeric $val */
	public function rewardVL($val) : void {
		$this->VL *= $val;
	}

	/**
	 * @param DetectionConfiguration $data
	 */
	public function __construct(User $user, string $name, $data) {
		$this->user = $user;
		$this->name = $name;
		$this->configuration = $data;
	}

	public function alert(string $message) : void {
		$this->getUser()->getPlayer()->sendMessage(sprintf("%s %s: %s", Lunar::getInstance()->getPrefix(), $this->name, $message));
	}

	protected function getUser() : User {
		return $this->user;
	}

	public function fail(string $message) : void {
		switch ($this->getConfiguration()->getPunishment()) {
			case Punishment::BAN():
				Server::getInstance()->getNameBans()->addBan($this->getUser()->getPlayer()->getName(), $message);
				$this->kick($message);
				break;
			case Punishment::WARN():
				$this->alert(TextFormat::RED . TextFormat::BOLD . $message);
				$this->reset();
				break;
			case Punishment::KICK():
				$this->kick($message);
				break;
		}
	}

	public function kick(string $message) : void {
		$this->getUser()->getPlayer()->kick(Lunar::getInstance()->getPrefix() . ' ' . $message, false);
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