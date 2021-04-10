<?php

namespace blackjack200\lunar;

use blackjack200\lunar\command\DetectionListCommand;
use blackjack200\lunar\detection\combat\Slapper;
use blackjack200\lunar\listener\DefaultListener;
use blackjack200\lunar\task\ProcessorSecondTrigger;
use blackjack200\lunar\task\ProcessorTickTrigger;
use blackjack200\lunar\utils\UnknownBlockAABBList;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use Throwable;

class Lunar extends PluginBase {
	private static self $instance;
	private string $prefix = '';

	public static function getInstance() : Lunar {
		return self::$instance;
	}

	public function getPrefix() : string {
		return $this->prefix;
	}

	public function onEnable() : void {
		self::$instance = $this;
		UnknownBlockAABBList::registerDefaults();
		$this->getServer()->getPluginManager()->registerEvents(new DefaultListener(), $this);
		$this->saveResource('config.yml');
		$this->prefix = $this->getConfig()->get("Prefix");
		Entity::registerEntity(Slapper::class, true, ['lunar_slapper']);
		try {
			StandardDetectionRegistry::initConfig();
		} catch (Throwable $e) {
			$this->getLogger()->warning('Configuration Error');
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$this->getScheduler()->scheduleRepeatingTask(new ProcessorTickTrigger(), 1);
		$this->getScheduler()->scheduleRepeatingTask(new ProcessorSecondTrigger(), 20);

		$command = new DetectionListCommand();
		$this->getServer()->getCommandMap()->register($command->getName(), $command);
	}
}
