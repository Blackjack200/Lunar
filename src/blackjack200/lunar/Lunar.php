<?php

namespace blackjack200\lunar;

use blackjack200\lunar\configuration\DetectionConfiguration;
use blackjack200\lunar\listener\DefaultListener;
use blackjack200\lunar\task\ProcessorSecondTrigger;
use blackjack200\lunar\task\ProcessorTickTrigger;
use pocketmine\plugin\PluginBase;

class Lunar extends PluginBase {
	private static self $instance;
	private array $configuration = [];

	public static function getInstance() : Lunar {
		return self::$instance;
	}

	public function onEnable() : void {
		self::$instance = $this;
		$this->getServer()->getPluginManager()->registerEvents(new DefaultListener(), $this);
		$this->saveResource('config.yml');
		$this->registerStandardDetectionConfiguration('ClientDataFaker');
		$this->registerStandardDetectionConfiguration('NukerA');
		$this->registerStandardDetectionConfiguration('AutoClicker');
		$this->getScheduler()->scheduleRepeatingTask(new ProcessorTickTrigger(), 1);
		$this->getScheduler()->scheduleRepeatingTask(new ProcessorSecondTrigger(), 20);
	}

	public function registerStandardDetectionConfiguration(string $name) : void {
		$this->configuration[$name] = new DetectionConfiguration($this->getConfig()->get($name));
	}

	public function getConfiguration() : array {
		return $this->configuration;
	}

	public function onDisable() : void {

	}
}
