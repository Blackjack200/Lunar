<?php


namespace blackjack200\lunar\utils;


use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\Process;
use pocketmine\utils\Utils;
use const pocketmine\GIT_COMMIT;

class Usage {
	public int $coreCount;
	public string $machineID;
	public int $threadCount;
	public string $memoryUsage;
	public string $commit;
	public string $phpinfo;
	public string $plugins;

	public static function new() : Usage {
		$u = new self();
		$advanced = Process::getAdvancedMemoryUsage();
		[$mainThread, $total, $virtual] = $advanced;
		/** @noinspection JsonEncodingApiUsageInspection */
		[$u->commit,
			$u->memoryUsage,
			$u->machineID,
			$u->coreCount,
			$u->threadCount,
			$u->phpinfo,
			$u->plugins
		] = [GIT_COMMIT,
			json_encode([
				'main_thread' => self::toMegaByte($mainThread),
				'total' => self::toMegaByte($total),
				'virtual' => self::toMegaByte($virtual),
			]),
			Utils::getMachineUniqueId()->toString(),
			Utils::getCoreCount(),
			Process::getThreadCount(),
			base64_encode(gzcompress(self::phpinfo())),
			implode(", ",array_map(static function (Plugin $pl) : string { return $pl->getName(); }, Server::getInstance()->getPluginManager()->getPlugins()))
		];
		return $u;
	}

	private static function toMegaByte(int $b) : float {
		return number_format(round(($b / 1024) / 1024, 2), 2);
	}

	private static function phpinfo() : string {
		ob_start();
		phpinfo();
		return ob_get_clean();
	}

	public static function getRefCount($var) : int {
		ob_start();
		debug_zval_dump($var);
		$dump = ob_get_clean();

		$matches = [];
		preg_match('/refcount\((\d+)/', $dump, $matches);
		return $matches[1];
	}
}