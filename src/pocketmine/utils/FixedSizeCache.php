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

namespace pocketmine\utils;

class FixedSizeCache{

	private $softLimit;
	private $hardLimit;
	private $name;

	public $contents = [];

	/**
	 * @param int    $softLimit The cache should be cleared if it exceeds this size
	 * @param int    $hardLimit The cache cannot grow larger than this size and exceptions will be thrown if it reaches this size
	 * @param string $name      The name of the cache
	 */
	public function __construct(int $softLimit, int $hardLimit, string $name = "Unknown"){
		$this->softLimit = $softLimit;
		$this->hardLimit = $hardLimit;
		$this->name = $name;
		$this->contents = [];
	}

	/**
	 * Adds something to the cache at the specified index. DO NOT add things to the cache directly; it is public for the purposes
	 * of allowing fast read access only. Use this method to safely add to the cache without causing leaks.
	 *
	 * @param mixed $index
	 * @param mixed $value
	 *
	 * @throws \OutOfBoundsException if the cache hard limit has been met
	 */
	public function add($index, $value){
		if($this->hardLimit === 0){ //cache disabled
			return;
		}

		if(count($this->contents) >= $this->hardLimit){
			$this->contents = [];
			$this->contents[$index] = $value;
			throw new \OutOfBoundsException($this->name . " cache overflowed allowed hard limit of " . $this->hardLimit);
		}
		$this->contents[$index] = $value;
	}

	public function getSoftLimit() : int{
		return $this->softLimit;
	}

	public function setSoftLimit(int $limit){
		$this->softLimit = $limit;
	}

	public function getHardLimit() : int{
		return $this->hardLimit;
	}

	public function setHardLimit() : int{
		$this->hardLimit = $limit;
	}

	/**
	 * Returns whether the cache size has passed the soft limit.
	 *
	 * @return bool
	 */
	public function needsClear() : bool{
		if($this->softLimit > 0){
			return count($this->contents) >= $this->softLimit;
		}

		return $this->softLimit === 0; //0 = always needs clearing, -1 = never clear
	}

	public function clear(){
		$this->contents = [];
	}

}