<?php


namespace blackjack200\lunar\command;


use blackjack200\lunar\DetectionRegistry;
use blackjack200\lunar\utils\Boolean;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class DetectionListCommand extends Command {
	public function __construct() {
		parent::__construct('aclist', 'List Anticheat Detections', '/aclist');
		$this->setPermission('lunar.list');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if ($this->testPermission($sender)) {
			$str = "§r§7Detections:\n";
			foreach (DetectionRegistry::getConfigurations() as $configuration) {
				$e = $configuration->isEnable();
				$data = $e ? TextFormat::GREEN : TextFormat::RED;
				$data .= Boolean::btos($e);
				$str .= sprintf(" §r§f%s §7ENABLE=§f%s\n", $configuration->getName(), $data);
			}

			$sender->sendMessage(rtrim($str));
		}
	}
}