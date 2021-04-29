<?php

namespace blackjack200\lunar;

use blackjack200\lunar\command\DetectionListCommand;
use blackjack200\lunar\detection\combat\Slapper;
use blackjack200\lunar\listener\DefaultListener;
use blackjack200\lunar\task\ProcessorSecondTrigger;
use blackjack200\lunar\task\ProcessorTickTrigger;
use libbot\BotFactory;
use libbot\BotInfo;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\Config;
use Throwable;

class Lunar extends PluginBase {
	/** @var self */
	private static $instance;
	/** @var string */
	private $prefix;
	/** @var string */
	private $format;
	/** @var DetectionLogger */
	private $detectionLogger;
	/** @var string|null */
	private $webhookFormat;
	private TimingsHandler $handler;

	public static function getInstance() : Lunar { return self::$instance; }

	public function getHandler() : TimingsHandler { return $this->handler; }

	public function getDetectionLogger() : DetectionLogger { return $this->detectionLogger; }

	public function getPrefix() : string { return $this->prefix; }

	public function getFormat() : string { return $this->format; }

	public function getWebhookFormat() : ?string { return $this->webhookFormat; }

	public function onEnable() : void {
		self::$instance = $this;
		if (version_compare('7.4.0', PHP_VERSION) > 0) {
			$this->getLogger()->error('Required PHP Version >= 7.4.0');
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$this->handler = new TimingsHandler('Lunar Detections');
		$this->getServer()->getPluginManager()->registerEvents(new DefaultListener(), $this);
		$config = $this->getConfig();
		$this->saveResource('config.yml', $config->get('Replace'));
		$this->saveResource('webhook.yml');
		$this->reloadConfig();
		$this->prefix = $config->get('Prefix', true);
		$this->format = $config->get('Format', true);
		Entity::registerEntity(Slapper::class, true, ['lunar_slapper']);
		try {
			DetectionRegistry::initConfig();
		} catch (Throwable $e) {
			$this->getLogger()->logException($e);
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$this->getScheduler()->scheduleRepeatingTask(new ProcessorTickTrigger(), 1);
		$this->getScheduler()->scheduleRepeatingTask(new ProcessorSecondTrigger(), 20);

		$command = new DetectionListCommand();
		$this->getServer()->getCommandMap()->register($command->getName(), $command);
		$this->detectionLogger = new DetectionLogger($this->getDataFolder() . 'detections.log');
		$this->detectionLogger->start();

		$webhookConfig = new Config($this->getDataFolder() . 'webhook.yml');
		if ($webhookConfig->get('Enable') !== false) {
			$this->webhookFormat = $webhookConfig->get('Format');
			$info = new BotInfo();
			foreach ($webhookConfig->get('Constructor') as $k => $v) {
				$info->$k = $v;
			}
			GlobalBot::set(BotFactory::create($webhookConfig->get('Type'), $info));
			GlobalBot::start();
		}
	}

	public function onDisable() : void {
		$this->detectionLogger->shutdown();
		GlobalBot::set(null);
	}
}
