<?php


namespace blackjack200\lunar\utils;


use Generator;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;

class AABB {
	/**
	 * @return Generator<Block>
	 */
	public static function getCollisionBlocks(Level $level, AxisAlignedBB $bb) : Generator {
		$minX = (int) ceil($bb->minX - 1);
		$minY = (int) ceil($bb->minY - 1);
		$minZ = (int) ceil($bb->minZ - 1);
		$maxX = (int) ceil($bb->maxX + 1);
		$maxY = (int) ceil($bb->maxY + 1);
		$maxZ = (int) ceil($bb->maxZ + 1);

		for ($z = $minZ; $z <= $maxZ; ++$z) {
			for ($x = $minX; $x <= $maxX; ++$x) {
				for ($y = $minY; $y <= $maxY; ++$y) {
					$block = $level->getBlockAt($x, $y, $z);
					if (!$block->canPassThrough() && self::fromBlock($block)->intersectsWith($bb)) {
						yield $block;
					}
				}
			}
		}
	}

	public static function fromBlock(Block $block) : AxisAlignedBB {
		return UnknownBlockAABBList::get($block->getId(), $block->getDamage()) ??
			$block->getBoundingBox() ??
			new AxisAlignedBB($block->getX(),
				$block->getY(),
				$block->getZ(),
				$block->getX() + 1,
				$block->getY() + 1,
				$block->getZ() + 1
			);
	}
}