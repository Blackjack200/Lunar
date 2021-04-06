<?php


namespace blackjack200\lunar\utils;


use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;

class AABB {
	/**
	 * @return Block[]
	 */
	public static function getCollisionBlocks(Level $level, AxisAlignedBB $bb, bool $targetFirst = false) : array {
		$minX = (int) floor($bb->minX - 1);
		$minY = (int) floor($bb->minY - 1);
		$minZ = (int) floor($bb->minZ - 1);
		$maxX = (int) floor($bb->maxX + 1);
		$maxY = (int) floor($bb->maxY + 1);
		$maxZ = (int) floor($bb->maxZ + 1);

		$collides = [];

		for ($z = $minZ; $z <= $maxZ; ++$z) {
			for ($x = $minX; $x <= $maxX; ++$x) {
				for ($y = $minY; $y <= $maxY; ++$y) {
					$block = $level->getBlockAt($x, $y, $z);
					$b = self::fromBlock($block);
					if (!$block->canPassThrough() && $b->intersectsWith($bb)) {
						$collides[] = $block;
					}
				}
			}
		}

		return $collides;
	}

	public static function fromBlock(Block $block) : AxisAlignedBB {
		return new AxisAlignedBB($block->getX(),
			$block->getY(),
			$block->getZ(),
			$block->getX() + 1,
			$block->getY() + 1,
			$block->getZ() + 1
		);
	}
}