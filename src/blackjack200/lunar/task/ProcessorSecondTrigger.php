<?php


namespace blackjack200\lunar\task;


use blackjack200\lunar\user\processor\InGameProcessor;
use blackjack200\lunar\user\UserManager;
use pocketmine\scheduler\Task;

class ProcessorSecondTrigger extends Task {
	public function onRun(int $currentTick) : void {
		foreach (UserManager::getUsers() as $user) {
			//TODO This shouldn't be hardcoded
			$user->triggerProcessor(InGameProcessor::class, null);
		}
	}
}