<?php

namespace blackjack200\lunar\listener;

use blackjack200\lunar\detection\action\FastBreakA;
use blackjack200\lunar\detection\action\NukerA;
use blackjack200\lunar\detection\combat\MultiAura;
use blackjack200\lunar\detection\combat\ReachA;
use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\Lunar;
use blackjack200\lunar\task\CleatDirtyDataTask;
use blackjack200\lunar\user\UserManager;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\Player;

class DefaultListener implements Listener {
	//TODO Improve this messy implementation
	/** @var LoginPacket[] */
	public array $dirtyLoginPacket = [];
	/** @var StartGamePacket[] */
	public array $dirtyStartGamePacket = [];

	public function __construct() {
		Lunar::getInstance()->getScheduler()->scheduleRepeatingTask(new CleatDirtyDataTask($this), 100);
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();
		$hash = spl_object_hash($player);
		$user = UserManager::register($player);
		$user->startGame = $this->dirtyStartGamePacket[$hash];
		foreach ($user->getProcessors() as $processor) {
			$processor->processClient($this->dirtyLoginPacket[$hash]);
		}
		unset($this->dirtyLoginPacket[$hash], $this->dirtyStartGamePacket[$hash]);
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void {
		$player = $event->getPlayer();
		$user = UserManager::get($player);
		$user->close();
		UserManager::unregister($player);
		$hash = spl_object_hash($player);
		unset($this->dirtyLoginPacket[$hash], $this->dirtyStartGamePacket[$hash]);
	}

	public function onDataPacketSend(DataPacketSendEvent $event) : void {
		$packet = $event->getPacket();
		if ($packet instanceof StartGamePacket) {
			$this->dirtyStartGamePacket[spl_object_hash($event->getPlayer())] = $packet;
		}
		$user = UserManager::get($event->getPlayer());
		if ($user !== null) {
			foreach ($user->getProcessors() as $processor) {
				$processor->processServerBond($packet);
			}

			foreach ($user->getDetections() as $detection) {
				if ($detection instanceof DetectionBase) {
					$detection->handleServer($packet);
				}
			}
		}
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) : void {
		$packet = $event->getPacket();
		if ($packet instanceof LoginPacket) {
			$this->dirtyLoginPacket[spl_object_hash($event->getPlayer())] = $packet;
		}

		$user = UserManager::get($event->getPlayer());
		if ($user !== null) {
			foreach ($user->getProcessors() as $processor) {
				$processor->processClient($packet);
			}

			foreach ($user->getDetections() as $detection) {
				if ($detection instanceof DetectionBase) {
					$detection->handleClient($packet);
				}
			}
		}
	}

	public function onEntityDamageByEntity(EntityDamageByEntityEvent $event) : void {
		$damager = $event->getDamager();
		$victim = $event->getEntity();

		if ($damager instanceof Player && $victim instanceof Player) {
			$user = UserManager::get($damager);
			$user->trigger(MultiAura::class, $event);
			$user->trigger(ReachA::class, $event);
		}
	}

	public function onEntityTeleport(EntityTeleportEvent $event) : void {
		$player = $event->getEntity();
		if ($player instanceof Player) {
			$user = UserManager::get($player);
			if ($user !== null) {
				$user->getMovementInfo()->lastTeleport = microtime(true);
			}
		}
	}

	public function onEntityMotion(EntityMotionEvent $event) : void {
		$player = $event->getEntity();
		if ($player instanceof Player) {
			$user = UserManager::get($player);
			if ($user !== null) {
				$user->getMovementInfo()->lastMotion = microtime(true);
				$user->getMovementInfo()->velocity = $event->getVector();
			}
		}
	}

	public function onPlayerDamage(EntityDamageEvent $event) : void {
		$player = $event->getEntity();
		if ($player instanceof Player) {
			UserManager::get($player)->lastHurt = microtime(true);
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void {
		$user = UserManager::get($event->getPlayer());
		$user->trigger(NukerA::class, $event);
		$user->trigger(FastBreakA::class, $event);
	}
}
