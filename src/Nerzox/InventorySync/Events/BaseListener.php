<?php

namespace Nerzox\InventorySync\Events;

use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\Listener;

use Nerzox\InventorySync\Loader;
use Nerzox\InventorySync\Database;

class BaseListener implements Listener{

    /**
     * @var Loader $c
     */
    public $c;

    public function __construct(Loader $c){
        $this->c = $c;
    }

    /**
     * @return void
     */
    public function onLogin(PlayerLoginEvent $e) :void {
        $player = $e->getPlayer();

        if(Database::isRegisted($player)){
            Database::restoreInventory($player);
        }
    }

    /**
     * @return void
     */
    public function onQuit(PlayerQuitEvent $e) :void {
        $player = $e->getPlayer();

        Database::setInventoryInDb($player);
    }
}
