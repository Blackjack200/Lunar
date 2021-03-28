<?php

namespace blackjack200\lunar;

use blackjack200\lunar\configuration\DetectionConfiguration;
use blackjack200\lunar\detection\combat\Slapper;
use blackjack200\lunar\listener\DefaultListener;
use blackjack200\lunar\task\ProcessorSecondTrigger;
use blackjack200\lunar\task\ProcessorTickTrigger;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;

class Lunar extends PluginBase {
	private static self $instance;
	private array $configuration = [];
	private string $prefix = '';

	public static function getInstance() : Lunar {
		return self::$instance;
	}

	public function getPrefix() : string {
		return $this->prefix;
	}

	public function onEnable() : void {
		self::$instance = $this;
		$this->getServer()->getPluginManager()->registerEvents(new DefaultListener(), $this);
		$this->saveResource('config.yml');
		$this->prefix = $this->getConfig()->get("Prefix");
		Entity::registerEntity(Slapper::class, true, ['lunar_slapper']);
		$this->registerStandardDetectionConfiguration('ClientDataFaker', false);
		$this->registerStandardDetectionConfiguration('NukerA', false);
		$this->registerStandardDetectionConfiguration('AutoClicker', false);
		$this->registerStandardDetectionConfiguration('KillAura', true);
		$this->registerStandardDetectionConfiguration('MultiAura', false);
		$this->registerStandardDetectionConfiguration('SpeedA', false);
		$this->registerStandardDetectionConfiguration('SpeedC', false);
		$this->getScheduler()->scheduleRepeatingTask(new ProcessorTickTrigger(), 1);
		$this->getScheduler()->scheduleRepeatingTask(new ProcessorSecondTrigger(), 20);
	}

	public function registerStandardDetectionConfiguration(string $name, bool $fullObject) : void {
		$this->configuration[$name] = new DetectionConfiguration($this->getConfig()->get($name), $fullObject);
	}

	public function getConfiguration() : array {
		return $this->configuration;
	}

	public function onDisable() : void {

	}
}
