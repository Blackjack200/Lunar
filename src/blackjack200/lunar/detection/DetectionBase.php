<?php


namespace blackjack200\lunar\detection;


use blackjack200\lunar\configuration\DetectionConfiguration;
use blackjack200\lunar\configuration\Punishment;
use blackjack200\lunar\GlobalBot;
use blackjack200\lunar\Lunar;
use blackjack200\lunar\user\User;
use blackjack200\lunar\utils\Objects;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Server;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\TextFormat;

abstract class DetectionBase implements Detection {
	protected float $preVL = 0;
	protected float $VL = 0;
	/** @var User */
	private $user;
	/** @var mixed */
	private $configuration;
	private string $name;
	private string $fmt;
	private ?string $webhookFmt;

	/**
	 * @param DetectionConfiguration $data
	 */
	public function __construct(User $user, string $name, string $fmt, ?string $webhookFmt, $data) {
		$this->user = $user;
		$this->name = $name;
		$this->fmt = $fmt;
		$this->webhookFmt = $webhookFmt;
		$this->configuration = $data;
	}

	/** @param numeric $VL */
	public function addVL($VL, ?string $message = null, bool $silent = false) : void {
		$this->VL += $VL;
		if ($message !== null) {
			$this->alert($message);
		}
		if (!$silent) {
			$this->alert("VL=$this->VL");
		}
	}

	public function alert(string $message) : void {
		$this->user->getPlayer()->sendMessage($this->format($this->fmt, $message));
	}

	final protected function format(string $fmt, string $message, bool $prefix = true) : string {
		$cfg = $this->getConfiguration();
		return sprintf(
			'%s%s',
			$prefix ? Lunar::getInstance()->getPrefix() . ' ' : '',
			Objects::replace($fmt, '[%s]',
				[
					'MSG' => $message,
					'DETECTION_NAME' => $this->name,
					'PLAYER_NAME' => $this->user->getPlayer()->getName(),
					'MAX_VL' => $cfg->getMaxVL(),
					'VL' => $this->VL,
					'PRE_VL' => $this->preVL,
					'PUNISHMENT' => $cfg->getPunishment(),
					'PUNISHMENT_STRING' => Punishment::toString($cfg->getPunishment())
				]
			)
		);
	}

	final public function getConfiguration() : DetectionConfiguration { return $this->configuration; }

	public function fail(string $message) : void {
		if ($this->webhookFmt !== null) {
			GlobalBot::send($this->format($this->webhookFmt, TextFormat::clean($message), false));
		}
		Lunar::getInstance()->getScheduler()->scheduleTask(new KickTask($message, $this));
	}

	final public function failImpl(string $message) : void {
		$this->log($message);
		switch ($this->getConfiguration()->getPunishment()) {
			case Punishment::BAN():
				Server::getInstance()->getNameBans()->addBan($this->getUser()->getPlayer()->getName(), $message);
				$this->kick($message);
				break;
			case Punishment::WARN():
				$msg = TextFormat::RED . TextFormat::BOLD . $message;
				$this->alertTitle($msg);
				$this->alert($msg);
				$this->reset();
				break;
			case Punishment::KICK():
				$this->kick($message);
				break;
		}
	}

	public function log(string $message) : void {
		$fmt = sprintf('[%s] NAME=%s DETECTION=%s MSG=%s', time(), $this->getUser()->getPlayer()->getName(), $this->name, $message);
		Lunar::getInstance()->getLogger()->info($fmt);
		Lunar::getInstance()->getDetectionLogger()->write($fmt);
	}

	final public function getUser() : User { return $this->user; }

	public function kick(string $message) : void {
		$this->getUser()->getPlayer()->kick($this->format($this->fmt, $message), false);
	}

	public function alertTitle(string $message) : void {
		$this->getUser()->getPlayer()->sendTitle('§g', $this->format($this->fmt, $message), 2, 3, 5);
	}

	public function reset() : void {
		$this->VL = 0;
		$this->preVL = 0;
	}

	public function overflowVL() : bool {
		$cfg = $this->getConfiguration();
		return $cfg->hasMaxVL() && $this->VL >= $cfg->getMaxVL();
	}

	final public function getName() : string { return $this->name; }

	public function handleClient(DataPacket $packet) : void { }

	public function handleServer(DataPacket $packet) : void { }

	public function debug(string $message) : void { }

	public function check(...$data) : void { }

	public function finalize() : void {

	}

	public function revertMovement() : void {
		if ($this->configuration->isSuppress()) {
			$user = $this->user;
			$pos = $user->getMovementInfo()->locationHistory->pop();
			if ($pos !== null) {
				$player = $user->getPlayer();
				$player->teleport($pos, $player->getYaw());
			}
		}
	}

	final public function getTimings() : TimingsHandler {
		return $this->configuration->getTimings();
	}
}