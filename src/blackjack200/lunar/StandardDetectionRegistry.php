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
use blackjack200\lunar\detection\movement\FlyD;
use blackjack200\lunar\detection\movement\SpeedA;
use blackjack200\lunar\detection\movement\SpeedC;
use blackjack200\lunar\detection\packet\BadPacketA;
use blackjack200\lunar\detection\packet\ClientDataFaker;
use blackjack200\lunar\user\User;
use Exception;

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
			'FlyD' => FlyD::class,
			'BadPacketA' => BadPacketA::class
		];

		foreach (self::$detections as $name => $class) {
			self::registerStandardDetectionConfiguration($name);
		}
	}

	private static function registerStandardDetectionConfiguration(string $name) : void {
		self::$configurations[$name] = new DetectionConfiguration(Lunar::getInstance()->getConfig()->get($name), true);
	}

	public static function getConfigurations() : array {
		return self::$configurations;
	}

	/**
	 * @return DetectionBase[]
	 * @throws Exception
	 */
	public static function getDetections(User $user) : array {
		$detections = [];
		foreach (self::$detections as $name => $class) {
			$detections[] = self::createDetection($user, $class, $class);
		}
		return $detections;
	}

	/**
	 * @param class-string<DetectionBase> $class
	 * @throws Exception
	 */
	private static function createDetection(User $user, string $name, string $class) : DetectionBase {
		$data = self::$configurations[$name];
		if ($data instanceof DetectionConfiguration && $data->isEnable()) {
			return new $class(
				$user,
				$name,
				clone $data
			);
		}
		throw new Exception('Detection not exist');
	}
}