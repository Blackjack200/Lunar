<?php


namespace blackjack200\lunar\detection\combat;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;

class MultiAura extends DetectionBase {
	/** @var array[] */
	protected $queue;
	protected $max;

	public function __construct(User $user, string $name, $data) {
		parent::__construct($user, $name, $data);
		$this->queue = ["time" => microtime(true), "targets" => []];
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

		if (!in_array(spl_object_hash($victim), $this->queue['targets'], true)) {
			$this->queue[spl_object_hash($damager)]['targets'][] = spl_object_hash($victim);
		}

		if (count($this->queue['targets']) >= $this->max && ($this->queue['time'] + 0.20) >= microtime(true)) {
			$inTime = microtime(true) - ($this->queue['time']);
			$this->addVL(1);
			$this->alert(sprintf('AE=%s T=%s', count($this->queue['targets']), $inTime));
			if ($this->overflowVL()) {
				$this->fail('MultiAura Detected');
			}
		}

		if (($this->queue['time'] + 0.25) <= microtime(true)) {
			$this->queue[spl_object_hash($damager)] = ['time' => microtime(true), 'targets' => []];
		}
	}
}