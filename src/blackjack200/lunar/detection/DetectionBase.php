<?php


namespace blackjack200\lunar\detection;


use blackjack200\lunar\configuration\DetectionConfiguration;
use blackjack200\lunar\configuration\Punishment;
use blackjack200\lunar\Lunar;
use blackjack200\lunar\user\User;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

abstract class DetectionBase implements Detection {
	protected float $preVL = 0;
	protected float $VL = 0;
	/** @var User */
	private $user;
	/** @var mixed */
	private $configuration;
	private string $name;

	/**
	 * @param DetectionConfiguration $data
	 */
	public function __construct(User $user, string $name, $data) {
		$this->user = $user;
		$this->name = $name;
		$this->configuration = $data;
	}

	public function overflowVL() : bool {
		return $this->getConfiguration()->hasMaxVL() && $this->VL >= $this->getConfiguration()->getMaxVL();
	}

	final public function getConfiguration() : DetectionConfiguration {
		return $this->configuration;
	}

	/** @param numeric $VL */
	public function addVL($VL, ?string $message = null, bool $silent = false) : void {
		$this->VL += $VL;
		if ($message !== null) {
			$this->alert($message);
		}
		if (!$silent) {
			$this->alert("VL={$this->VL}");
		}
	}

	public function alert(string $message) : void {
		$this->getUser()->getPlayer()->sendMessage($this->formatMessage($message));
	}

	final public function getUser() : User {
		return $this->user;
	}

	/**
	 * @param string $message
	 * @return string
	 */
	final protected function formatMessage(string $message) : string {
		return sprintf("%s %s: %s", Lunar::getInstance()->getPrefix(), $this->name, $message);
	}

	public function fail(string $message) : void {
		Lunar::getInstance()->getScheduler()->scheduleTask(new ClosureTask(function (int $tick) use ($message) : void {
			$this->failImpl($message);
		}));
	}

	final protected function failImpl(string $message) : void {
		$this->log($message);
		switch ($this->getConfiguration()->getPunishment()) {
			case Punishment::BAN():
				Server::getInstance()->getNameBans()->addBan($this->getUser()->getPlayer()->getName(), $message);
				$this->kick($message);
				break;
			case Punishment::WARN():
				$this->alertTitle(TextFormat::RED . TextFormat::BOLD . $message);
				$this->reset();
				break;
			case Punishment::KICK():
				$this->kick($message);
				break;
		}
	}

	/**
	 * @param string $message
	 */
	public function log(string $message) : void {
		$fmt = sprintf('[%s] NAME=%s DETECTION=%s MSG=%s', time(), $this->getUser()->getPlayer()->getName(), $this->name, $message);
		Lunar::getInstance()->getLogger()->info($fmt);
		Lunar::getInstance()->getDetectionLogger()->write($fmt);
	}

	public function kick(string $message) : void {
		$this->getUser()->getPlayer()->kick($this->formatMessage($message), false);
	}

	public function alertTitle(string $message) : void {
		$this->getUser()->getPlayer()->sendTitle('§g', $this->formatMessage($message), 2, 3, 5);
	}

	public function reset() : void {
		$this->VL = 0;
		$this->preVL = 0;
	}

	final public function getName() : string {
		return $this->name;
	}

	public function handleClient(DataPacket $packet) : void {

	}

	public function handleServer(DataPacket $packet) : void {

	}

	public function debug(string $message) : void {

	}

	final public function __destruct() {
		$this->destruct();
	}

	public function destruct() : void {
		$this->user = null;
	}

	public function check(...$data) : void {

	}

	public function close() : void {

	}

	public function revertMovement() : void {
		if ($this->configuration->isSuppress()) {
			$user = $this->getUser();
			$pos = $user->getMovementInfo()->locationHistory->pop();
			if ($pos !== null) {
				$player = $user->getPlayer();
				$player->teleport($pos, $player->getYaw());
			}
		}
	}
}