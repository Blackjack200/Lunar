<?php


namespace blackjack200\lunar;


use blackjack200\lunar\configuration\DetectionConfiguration;
use blackjack200\lunar\detection\action\AutoClicker;
use blackjack200\lunar\detection\action\NukerA;
use blackjack200\lunar\detection\combat\KillAura;
use blackjack200\lunar\detection\combat\MultiAura;
use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\detection\movement\FlyA;
use blackjack200\lunar\detection\movement\FlyB;
use blackjack200\lunar\detection\movement\FlyE;
use blackjack200\lunar\detection\movement\SpeedA;
use blackjack200\lunar\detection\movement\SpeedC;
use blackjack200\lunar\detection\packet\BadPacketA;
use blackjack200\lunar\detection\packet\ClientDataFaker;
use blackjack200\lunar\user\User;

final class StandardDetectionRegistry {
	/** @var DetectionConfiguration[] */
	private static array $configurations = [];
	/** @var array<class-string<DetectionBase>> */
	private static array $detections = [];

	private function __construct() {
	}

	public static function initConfig() : void {
		self::$detections = [
			'ClientDataFaker' => ClientDataFaker::class,
			'NukerA' => NukerA::class,
			'AutoClicker' => AutoClicker::class,
			'KillAura' => KillAura::class,
			'MultiAura' => MultiAura::class,
			'SpeedA' => SpeedA::class,
			'SpeedC' => SpeedC::class,
			'FlyA' => FlyA::class,
			'FlyB' => FlyB::class,
			'FlyE' => FlyE::class,
		];

		foreach (self::$detections as $name => $class) {
			self::registerStandardDetectionConfiguration($name, false);
		}

		self::$detections['BadPacketA'] = BadPacketA::class;
		self::registerStandardDetectionConfiguration('BadPacketA', true);
	}

	private static function registerStandardDetectionConfiguration(string $name, bool $object) : void {
		self::$configurations[$name] = new DetectionConfiguration(Lunar::getInstance()->getConfig()->get($name), $object);
	}

	public static function getConfigurations() : array {
		return self::$configurations;
	}

	/**
	 * @return DetectionBase[]
	 */
	public static function getDetections(User $user) : array {
		$detections = [];
		foreach (self::$detections as $name => $class) {
			$detection = self::createDetection($user, $name, $class);
			if ($detection !== null) {
				$detections[] = $detection;
			}
		}
		return $detections;
	}

	/**
	 * @param class-string<DetectionBase> $class
	 */
	private static function createDetection(User $user, string $name, string $class) : ?DetectionBase {
		$data = self::$configurations[$name];
		if ($data instanceof DetectionConfiguration && $data->isEnable()) {
			return new $class(
				$user,
				$name,
				clone $data
			);
		}
		return null;
	}
}