<?php

namespace blackjack200\lunar;

use blackjack200\lunar\configuration\DetectionConfiguration;
use blackjack200\lunar\listener\DefaultListener;
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
		$new = [];
		$new['ClientDataFaker'] = new DetectionConfiguration($this->getConfig()->get('ClientDataFaker'));
		$this->configuration = $new;
	}

	public function getConfiguration() : array {
		return $this->configuration;
	}

	public function onDisable() : void {

	}
}
