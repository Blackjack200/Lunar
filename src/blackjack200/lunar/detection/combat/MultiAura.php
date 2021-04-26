<?php


namespace blackjack200\lunar\detection\combat;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;

class MultiAura extends DetectionBase {
	protected float $time;
	protected array $targets = [];
	protected $max;

	public function __construct(User $user, string $name, string $fmt, ?string $webhookFmt, $data) {
		parent::__construct($user, $name, $fmt, $webhookFmt, $data);
		$this->time = microtime(true);
		$this->max = $this->getConfiguration()->getExtraData()->MaxEntityHit;
	}

	public function check(...$data) : void {
		/** @var EntityDamageByEntityEvent $event */
		[$event] = $data;
		/** @var Player $damager */
		$damager = $event->getDamager();
		/** @var Player $damager */
		$victim = $event->getEntity();
		$distance = $damager->distance($victim);
		if ($distance <= 1.5) {
			return;
		}

		if (!in_array(spl_object_hash($victim), $this->targets, true)) {
			$this->targets[] = spl_object_hash($victim);
		}

		if (count($this->targets) >= $this->max && ($this->time + 0.20) >= microtime(true)) {
			$inTime = microtime(true) - ($this->time);
			$this->addVL(1);
			$this->alert(sprintf('AE=%s T=%s', count($this->targets), $inTime));
			if ($this->overflowVL()) {
				$this->fail('MultiAura Detected');
			}
		}

		if (($this->time + 0.25) <= microtime(true)) {
			$this->time = microtime(true);
			$this->targets = [];
		}
	}
}