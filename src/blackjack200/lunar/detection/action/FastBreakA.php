<?php


namespace blackjack200\lunar\detection\action;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\entity\Effect;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;

class FastBreakA extends DetectionBase {
	private float $breakTime;

	public function __construct(User $user, string $name, $data) {
		parent::__construct($user, $name, $data);
		$this->breakTime = microtime(true);
	}

	public function check(...$data) : void {
		$this->impl($data[0]);
	}

	private function impl(BlockBreakEvent $event) : void {
		if (!$event->getInstaBreak()) {
			$user = $this->getUser();
			$target = $event->getBlock();
			$item = $event->getItem();

			$prediction = ceil($target->getBreakTime($item) * 20);
			$prediction *= 1 - (0.2 * $user->getEffectLevel(Effect::HASTE));
			$prediction *= 1 + (0.3 * $user->getEffectLevel(Effect::MINING_FATIGUE));
			$prediction--;

			$current = ceil(microtime(true) * 20) - $this->breakTime;

			if ($current < $prediction) {
				if ($this->getConfiguration()->isSuppress()) {
					$event->setCancelled(true);
				}

				$this->addVL(1);
				if ($this->overflowVL()) {
					$this->fail('Try to break ' . $target->getName() . ' with tool= ' . $item->getVanillaName() . 'diff=' . number_format($prediction - $current, 5));
				}
			}
		}
	}

	public function handleClient(DataPacket $packet) : void {
		if (
			$packet instanceof PlayerActionPacket &&
			$packet->action === PlayerActionPacket::ACTION_START_BREAK
		) {
			$this->breakTime = floor(microtime(true) * 20);
		}
	}
}