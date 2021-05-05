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

	public function __construct(User $user, string $name, string $fmt, ?string $webhookFmt, $data) {
		parent::__construct($user, $name, $fmt, $webhookFmt, $data);
		$this->breakTime = floor(microtime(true) * 20);
	}

	public function check(...$data) : void {
		$this->impl($data[0]);
	}

	private function impl(BlockBreakEvent $event) : void {
		if (!$event->getInstaBreak()) {
			$player = $event->getPlayer();
			$target = $event->getBlock();
			$item = $event->getItem();

			$expectedTime = ceil($target->getBreakTime($item) * 20);

			if ($player->hasEffect(Effect::HASTE)) {
				$expectedTime *= 1 - (0.2 * $player->getEffect(Effect::HASTE)->getEffectLevel());
			}

			if ($player->hasEffect(Effect::MINING_FATIGUE)) {
				$expectedTime *= 1 + (0.3 * $player->getEffect(Effect::MINING_FATIGUE)->getEffectLevel());
			}

			--$expectedTime; //1 tick compensation

			$actualTime = ceil(microtime(true) * 20) - $this->breakTime;

			if ($actualTime < $expectedTime) {
				if ($this->getConfiguration()->isSuppress()) {
					$event->setCancelled(true);
				}

				$this->addVL(1);
				if ($this->overflowVL()) {
					$this->fail('Try to break ' . $target->getName() . ' with tool= ' . $item->getVanillaName() . 'diff=' . number_format($actualTime - $expectedTime, 5));
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