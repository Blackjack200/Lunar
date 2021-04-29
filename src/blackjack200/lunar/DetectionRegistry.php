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
use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\detection\movement\fly\FlyA;
use blackjack200\lunar\detection\movement\fly\FlyB;
use blackjack200\lunar\detection\movement\fly\FlyE;
use blackjack200\lunar\detection\movement\motion\MotionA;
use blackjack200\lunar\detection\movement\motion\MotionB;
use blackjack200\lunar\detection\movement\speed\SpeedA;
use blackjack200\lunar\detection\movement\speed\SpeedC;
use blackjack200\lunar\detection\packet\BadPacketA;
use blackjack200\lunar\detection\packet\ClientDataFaker;
use blackjack200\lunar\user\User;
use pocketmine\timings\TimingsHandler;

final class DetectionRegistry {
	/** @var DetectionConfiguration[] */
	private static array $configurations = [];
	/** @var array<class-string<DetectionBase>> */
	private static array $detections = [];
	/** @var TimingsHandler[] */
	private static array $timings = [];

	private function __construct() { }

	public static function initConfig() : void {
		self::$detections = [
			'ClientDataFaker' => ClientDataFaker::class,
			'NukerA' => NukerA::class,
			'FastBreakA' => FastBreakA::class,
			'AutoClicker' => AutoClicker::class,
			'KillAuraA' => KillAuraA::class,
			'KillAuraB' => KillAuraB::class,
			'MultiAura' => MultiAura::class,
			'ReachA' => ReachA::class,
			'SpeedA' => SpeedA::class,
			'SpeedC' => SpeedC::class,
			'FlyA' => FlyA::class,
			'FlyB' => FlyB::class,
			'FlyE' => FlyE::class,
			'MotionA' => MotionA::class,
			'MotionB' => MotionB::class,
		];

		foreach (self::$detections as $name => $class) {
			self::registerStandardDetectionConfiguration($name, false);
		}

		self::$detections['BadPacketA'] = BadPacketA::class;
		self::registerStandardDetectionConfiguration('BadPacketA', true);
	}

	private static function registerStandardDetectionConfiguration(string $name, bool $object) : void {
		$cfg = new DetectionConfiguration(Lunar::getInstance()->getConfig()->get($name), $object, self::registerDetectionTimings($name));
		self::$configurations[$name] = $cfg;
	}

	private static function registerDetectionTimings(string $name) : TimingsHandler {
		if (!isset(self::$timings[$name])) {
			$handler = new TimingsHandler("Lunar_$name", Lunar::getInstance()->getHandler());
			self::$timings[$name] = $handler;
			return $handler;
		}
		return self::$timings[$name];
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
				Lunar::getInstance()->getFormat(),
				Lunar::getInstance()->getWebhookFormat(),
				clone $data
			);
		}
		return null;
	}

	public static function getTimings() : array {
		return self::$timings;
	}
}