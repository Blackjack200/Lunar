<?php

namespace blackjack200\lunar\listener;

use blackjack200\lunar\detection\combat\MultiAura;
use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\UserManager;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Player;

class DefaultListener implements Listener {
	/** @var LoginPacket[] */
	private array $dirtyLoginPacket = [];

	public function onPlayerPreJoin(PlayerPreLoginEvent $event) : void {
		$player = $event->getPlayer();
		UserManager::register($player);
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();
		$hash = spl_object_hash($player);
		$user = UserManager::get($player);
		foreach ($user->getProcessors() as $processor) {
			$processor->processClient($this->dirtyLoginPacket[$hash]);
		}
		unset($this->dirtyLoginPacket[$hash]);
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void {
		$user = UserManager::get($event->getPlayer());
		$user->close();
		UserManager::unregister($event->getPlayer());
		unset($this->dirtyLoginPacket[spl_object_hash($event->getPlayer())]);
	}

	public function onDataPacketSend(DataPacketSendEvent $event) : void {
		$user = UserManager::get($event->getPlayer());
		if ($user !== null) {
			$packet = $event->getPacket();
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
			UserManager::get($damager)->trigger(MultiAura::class, $event);
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
			}
		}
	}

	public function onPlayerJump(PlayerJumpEvent $event) : void {
		UserManager::get($event->getPlayer())->getMovementInfo()->lastJump = microtime(true);
	}

	public function onPlayerDamage(EntityDamageEvent $event) : void {
		$player = $event->getEntity();
		if ($player instanceof Player) {
			UserManager::get($player)->lastHurt = microtime(true);
		}
	}
}
