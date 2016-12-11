<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\block;

use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\generator\object\TallGrass as TallGrassObject;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Random;

class Grass extends Solid{

	protected $id = self::GRASS;

	public function __construct(){

	}

	public function canBeActivated(){
		return true;
	}

	public function getName(){
		return "Grass";
	}

	public function getHardness(){
		return 0.6;
	}

	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}

	public function getDrops(Item $item){
		return [
			[Item::DIRT, 0, 1],
		];
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_RANDOM){
			if(!($up = $this->getSide(Vector3::SIDE_UP, 1, false))->isTransparent() or $up instanceof Liquid){
				//Block on top of the grass, kill it
				$this->level->getServer()->getPluginManager()->callEvent($ev = new BlockSpreadEvent($this, $this, new Dirt()));
				if(!$ev->isCancelled()){
					$this->level->setBlock($this, $ev->getNewState());
				}
			}elseif($this->level->getFullLightAt($this->x, $this->y + 1, $this->z) >= 9){
				$x = mt_rand($this->x - 1, $this->x + 1);
				$y = mt_rand($this->y - 3, $this->y + 1);
				$z = mt_rand($this->z - 1, $this->z + 1);

				//Use primitive methods for more performance.
				//TODO: change this to full light once skylight has been implemented
				if($this->level->getBlockIdAt($x, $y, $z) === Block::DIRT and $this->level->getBlockLightAt($x, $y + 1, $z) /*$this->level->getFullLightAt($x, $y + 1, $z)*/ >= 4){
					$block = $this->level->getBlock(new Vector3($x, $y, $z), true, false);
					if(($up = $block->getSide(Vector3::SIDE_UP, 1, false))->isTransparent() and !($up instanceof Liquid)){
						$this->level->getServer()->getPluginManager()->callEvent($ev = new BlockSpreadEvent($block, $this, new Grass()));
						if(!$ev->isCancelled()){
							$this->level->setBlock($block, $ev->getNewState());
						}
					}
				}
			}
			
			return Level::BLOCK_UPDATE_RANDOM;
		}

		return false;
	}

	public function onActivate(Item $item, Player $player = null){
		if($item->getId() === Item::DYE and $item->getDamage() === 0x0F){
			$item->count--;
			TallGrassObject::growGrass($this->getLevel(), $this, new Random(mt_rand()), 8, 2);

			return true;
		}elseif($item->isHoe()){
			$item->useOn($this);
			$this->getLevel()->setBlock($this, new Farmland());

			return true;
		}elseif($item->isShovel() and $this->getSide(1)->getId() === Block::AIR){
			$item->useOn($this);
			$this->getLevel()->setBlock($this, new GrassPath());

			return true;
		}

		return false;
	}
}
