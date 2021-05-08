<?php


namespace blackjack200\lunar;


use blackjack200\lunar\configuration\DetectionConfiguration;
use blackjack200\lunar\detection\action\AutoClicker;
use blackjack200\lunar\detection\action\FastBreakA;
use blackjack200\lunar\detection\action\NukerA;
use blackjack200\lunar\detection\combat\KillAuraA;
use blackjack200\lunar\detection\combat\KillAuraB;
use blackjack200\lunar\detection\combat\MultiAura;
use blackjack200\lunar\detection\combat\ReachA;
use blackjack200\lunar\detection\combat\velocity\VelocityB;
use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\detection\movement\AirSwim;
use blackjack200\lunar\detection\movement\AntiImmobile;
use blackjack200\lunar\detection\movement\fly\FlyA;
use blackjack200\lunar\detection\movement\fly\FlyB;
use blackjack200\lunar\detection\movement\fly\FlyE;
use blackjack200\lunar\detection\movement\motion\MotionB;
use blackjack200\lunar\detection\movement\speed\SpeedA;
use blackjack200\lunar\detection\movement\speed\SpeedC;
use blackjack200\lunar\detection\packet\BadPacketA;
use blackjack200\lunar\detection\packet\BadPacketB;
use blackjack200\lunar\detection\packet\BadPacketC;
use blackjack200\lunar\detection\packet\ClientDataFaker;
use blackjack200\lunar\user\User;

final class DetectionRegistry {
	/** @var DetectionConfiguration[] */
	private static array $configurations = [];

	private function __construct() { }

	public static function initConfig() : void {
		$detections = [
			'AutoClicker' => AutoClicker::class,
			'FastBreakA' => FastBreakA::class,
			'NukerA' => NukerA::class,

			'MultiAura' => MultiAura::class,
			'ReachA' => ReachA::class,
			'VelocityB' => VelocityB::class,
			'KillAuraA' => KillAuraA::class,
			'KillAuraB' => KillAuraB::class,

			'AirSwim' => AirSwim::class,
			'AntiImmobile' => AntiImmobile::class,
			'SpeedA' => SpeedA::class,
			'SpeedC' => SpeedC::class,
			'MotionB' => MotionB::class,
			'FlyA' => FlyA::class,
			'FlyB' => FlyB::class,
			'FlyE' => FlyE::class,

			'BadPacketA' => BadPacketA::class,
			'BadPacketB' => BadPacketB::class,
			'BadPacketC' => BadPacketC::class,
			'ClientDataFaker' => ClientDataFaker::class,
		];

		foreach ($detections as $name => $class) {
			self::register($class, $name, false);
		}
	}

	private static function register(string $class, string $name, bool $object) : void {
		self::$configurations[$class] = new DetectionConfiguration(
			$class,
			$name,
			Lunar::getInstance()->getConfig()->get($name),
			$object
		);
	}

	public static function getConfigurations() : array {
		return self::$configurations;
	}

	/**
	 * @return DetectionBase[]
	 */
	public static function getDetections(User $user) : array {
		$detections = [];
		foreach (self::$configurations as $configuration) {
			$detection = self::create($user, $configuration);
			if ($detection !== null) {
				$detections[$configuration->getClass()] = $detection;
			}
		}
		return $detections;
	}

	private static function create(User $user, DetectionConfiguration $configuration) : ?DetectionBase {
		if ($configuration->isEnable()) {
			$class = $configuration->getClass();
			return new $class(
				$user,
				$configuration->getName(),
				Lunar::getInstance()->getFormat(),
				Lunar::getInstance()->getWebhookFormat(),
				clone $configuration
			);
		}
		return null;
	}

	public static function unregister(string $class) : void {
		unset(self::$configurations[$class]);
	}
}