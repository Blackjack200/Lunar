<?php


namespace blackjack200\lunar\task;


use blackjack200\lunar\detection\action\AutoClicker;
use blackjack200\lunar\user\processor\InGameProcessor;
use blackjack200\lunar\user\UserManager;
use pocketmine\scheduler\Task;

class ProcessorSecondTrigger extends Task {
	public function onRun(int $currentTick) : void {
		foreach (UserManager::getUsers() as $user) {
			//TODO This shouldn't be hardcoded
			$user->trigger(AutoClicker::class);
			$user->triggerProcessor(InGameProcessor::class, null);
		}
	}
}