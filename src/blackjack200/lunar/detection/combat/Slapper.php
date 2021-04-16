<?php


namespace blackjack200\lunar\detection\combat;


use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;

class Slapper extends Human {
	protected $attackCooldown;
	private KillAuraA $killAura;
	private float $last;

	public function __construct(Level $level, CompoundTag $nbt, ?KillAuraA $killAura = null) {
		parent::__construct($level, $nbt);
		if ($killAura === null) {
			$this->close();
			return;
		}
		$this->last = 0;
		$this->killAura = $killAura;
		$this->attackCooldown = $this->killAura->getConfiguration()->getExtraData()->AttackCooldown;
	}

	public function setHealth(float $amount) : void {
		parent::setHealth($this->getMaxHealth());
	}

	public function attack(EntityDamageEvent $source) : void {
		if ($source instanceof EntityDamageByEntityEvent &&
			microtime(true) - $this->last > $this->attackCooldown &&
			$source->getDamager() === $this->killAura->getUser()->getPlayer()
		) {
			$this->killAura->addVL(1);
			$this->last = microtime(true);
			if ($this->killAura->overflowVL()) {
				$this->killAura->fail('HIT');
			}
			$source->setModifier(-$source->getFinalDamage(), EntityDamageEvent::MODIFIER_RESISTANCE);
		}
		parent::attack($source);
	}
}