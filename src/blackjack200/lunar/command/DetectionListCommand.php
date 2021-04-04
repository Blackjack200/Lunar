<?php


namespace blackjack200\lunar\command;


use blackjack200\lunar\configuration\Boolean;
use blackjack200\lunar\StandardDetectionRegistry;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class DetectionListCommand extends Command {
	public function __construct() {
		parent::__construct('aclist', 'List Anticheat Detections', '/aclist');
		$this->setPermission('lunar.list');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if ($this->testPermission($sender)) {
			$str = "§r§7Detections:\n";
			foreach (StandardDetectionRegistry::getConfigurations() as $name => $configuration) {
				$str .= sprintf(" §r§f%s §7ENABLE=§f%s\n", $name, Boolean::btos($configuration->isEnable()));
			}

			$sender->sendMessage(rtrim($str));
		}
	}
}