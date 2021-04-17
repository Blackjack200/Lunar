<?php

namespace blackjack200\lunar;

use blackjack200\lunar\command\DetectionListCommand;
use blackjack200\lunar\detection\combat\Slapper;
use blackjack200\lunar\listener\DefaultListener;
use blackjack200\lunar\task\ProcessorSecondTrigger;
use blackjack200\lunar\task\ProcessorTickTrigger;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use Throwable;

class Lunar extends PluginBase {
	/** @var self */
	private static $instance;
	/** @var string */
	private $prefix = '';
	/** @var DetectionLogger */
	private $detectionLogger;

	public static function getInstance() : Lunar {
		return self::$instance;
	}

	public function getPrefix() : string {
		return $this->prefix;
	}

	public function onEnable() : void {
		self::$instance = $this;
		if (version_compare('7.4.0', PHP_VERSION) > 0) {
			$this->getLogger()->error('Required PHP Version >= 7.4.0');
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$this->getServer()->getPluginManager()->registerEvents(new DefaultListener(), $this);
		$this->saveResource('config.yml', $this->getConfig()->get('Replace'));
		$this->reloadConfig();
		$this->prefix = $this->getConfig()->get('Prefix', true);
		Entity::registerEntity(Slapper::class, true, ['lunar_slapper']);
		try {
			DetectionRegistry::initConfig();
		} catch (Throwable $e) {
			$this->getLogger()->logException($e);
			$this->getLogger()->warning('Error when Configuration');
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$this->getScheduler()->scheduleRepeatingTask(new ProcessorTickTrigger(), 1);
		$this->getScheduler()->scheduleRepeatingTask(new ProcessorSecondTrigger(), 20);

		$command = new DetectionListCommand();
		$this->getServer()->getCommandMap()->register($command->getName(), $command);
		$this->detectionLogger = new DetectionLogger($this->getDataFolder() . 'detections.log');
		$this->detectionLogger->start();
	}

	public function getDetectionLogger() : DetectionLogger {
		return $this->detectionLogger;
	}

	public function onDisable() : void {
		$this->detectionLogger->shutdown();
	}
}
