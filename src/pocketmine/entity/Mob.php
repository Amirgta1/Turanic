<?php

/*
 *
 *    _______                    _
 *   |__   __|                  (_)
 *      | |_   _ _ __ __ _ _ __  _  ___
 *      | | | | | '__/ _` | '_ \| |/ __|
 *      | | |_| | | | (_| | | | | | (__
 *      |_|\__,_|_|  \__,_|_| |_|_|\___|
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Turanic
 *
*/

namespace pocketmine\entity;

use pocketmine\entity\behavior\Behavior;
use pocketmine\utils\Random;
use pocketmine\math\Vector3;

abstract class Mob extends Creature{

    public $behaviors = [];
    /** @var Behavior | null */
    public $currentBehavior = null;
    public $random;

    public function initEntity(){
        parent::initEntity();

        $this->random = new Random();
    }

    public function getHorizDir(){
        $vec = new Vector3;

        $pitch = 0;
        $yaw = $this->yaw;
        $vec->x = -sin($yaw) * cos($pitch);
        $vec->y = -sin($pitch);
        $vec->z = sin($yaw) * cos($pitch);

        return $vec->normalize();
    }

    public function onUpdate($tick){
        if($this->closed or !$this->isAlive()) return;

        $this->currentBehavior = $this->checkBehavior();

        if($this->currentBehavior != null){
            $this->currentBehavior->onTick();
        }

        return parent::onUpdate($tick);
    }

    protected function updateMovement(){
     $this->forceMovement();
     $this->forceMotion();
    }

    private function checkBehavior(){
        foreach($this->behaviors as $index => $behavior){
            if($behavior == $this->currentBehavior){
                if($behavior->canContinue()){
                    return $behavior;
                }

                $behavior->onEnd();
                $this->currentBehavior = null;
            }

            if($behavior->shouldStart()){
                if($this->currentBehavior == null or (array_search($this->currentBehavior, $this->behaviors)) > $index){
                    if($this->currentBehavior != null){
                        $this->currentBehavior->onEnd();
                    }
                    return $behavior;
                }
            }
        }
        return null;
    }

    public function getCurrentBehavior(){
        return $this->currentBehavior;
    }

    public function addBehavior(Behavior $behavior){
        $this->behaviors[] = $behavior;
    }

    public function removeBehavior($key){
        unset($this->behaviors[$key]);
    }

    public function forceMovement(){
        $this->level->addEntityMovement($this->x >> 4, $this->z >> 4, $this->id, $this->x, $this->y + $this->getEyeHeight(), $this->z, $this->yaw, $this->pitch);
    }

    public function forceMotion(){
        $this->level->addEntityMotion($this->x >> 4, $this->z >> 4, $this->id, $this->motionX, $this->motionY, $this->motionZ);
    }
}