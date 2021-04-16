<?php


namespace blackjack200\lunar\task;


use blackjack200\lunar\detection\action\NukerA;
use blackjack200\lunar\detection\combat\KillAuraA;
use blackjack200\lunar\user\processor\MovementProcessor;
use blackjack200\lunar\user\UserManager;
use pocketmine\scheduler\Task;

class ProcessorTickTrigger extends Task {
	public function onRun(int $currentTick) : void {
		foreach (UserManager::getUsers() as $user) {
			//TODO This shouldn't be hardcoded
			$user->trigger(NukerA::class);
			$user->trigger(KillAuraA::class);
			$user->triggerProcessor(MovementProcessor::class);
		}
	}
}