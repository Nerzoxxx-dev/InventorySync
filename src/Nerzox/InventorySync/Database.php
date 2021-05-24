<?php

namespace Nerzox\InventorySync;

use pocketmine\utils\Config;
use pocketmine\Player;

use Nerzox\InventorySync\Loader;


class Database {

    /**
     * @var $config
     */
    private static $config;

    public function __construct(){
        self::$config = new Config(Loader::getInstance()->getDataFolder() . 'config.yml', Config::YAML);
    }

    /**
     * @return Config
     */
    private static function getConfig() :Config { 
        return self::$config = new Config(Loader::getInstance()->getDataFolder() . 'config.yml', Config::YAML);; 
    }

    /**
     * @return \MySQLi
     */
    private static function getDatabase() :\MySQLi {
        $all = self::getConfig()->getAll();
        return new \MySQLi($all['mysql_host'], $all['mysql_user'], $all['mysql_password'], $all['mysql_database']);
    }

    /**
     * @return void
     */
    public static function init() :void{
        $db = self::getDatabase();

        $db->query("CREATE TABLE IF NOT EXISTS inventory(playername VARCHAR(255), inventorycontents TEXT, inventoryarmor TEXT)");
        $db->close();
    }

    /**
     * @return bool
     */

    public static function isRegisted(Player $player) :bool{
        $db = self::getDatabase();
        $name = strtolower($player->getName());

        $req = $db->query("SELECT * FROM inventory WHERE playername = '$name'");
        $arr = $req->fetch_array();

        if(empty($arr)){
            return false;
        }else{
            return true;
        }
        return false;
    }

    /**
     * @return void
     */
    public static function setInventoryInDb(Player $player) :void {
        $db = self::getDatabase();
        $invcontents = $player->getInventory()->getContents();
        $invarmor = $player->getArmorInventory()->getContents();
        $name = strtolower($player->getName());

        $inv64 = [];
        $armor64 = [];

        foreach($invcontents as $slot => $contents){
            $inv64[$slot] = $contents;
        }

        foreach($invarmor as $slot => $contents){
            switch($slot){
                case 0:
                    $armor64["helmet"] = $contents;
                    break;
                case 1:
                    $armor64['chestplate'] = $contents;
                    break;
                case 2:
                    $armor64['leggings'] = $contents;
                    break;
                case 3:
                    $armor64['boots'] = $contents;
                    break;
            }
        }

        $inv64 = base64_encode(serialize($inv64));
        $armor64 = base64_encode(serialize($armor64));

        if(self::isRegisted($player)){
            $db->query("UPDATE inventory SET inventorycontents = '$inv64', inventoryarmor = '$armor64' WHERE playername = '$name'");
        }else{
            $db->query("INSERT INTO inventory(playername, inventorycontents, inventoryarmor) VALUES ('$name', '$inv64', '$armor64')");
        }
        $db->close();
    }

    /**
     * @return array
     */
    public static function getInventoryContents(Player $player) :array{
        $db = self::getDatabase();
        $name = strtolower($player->getName());

        $req = $db->query("SELECT * FROM inventory WHERE playername = '$name'");
        $arr = $req->fetch_array();
        $db->close();

        $inv = unserialize(base64_decode($arr['inventorycontents']));
        $armor = unserialize(base64_decode($arr['inventoryarmor']));

        return ['inv' => $inv, 'armor' => $armor];
    }

    /**
     * @return void
     */
    public static function restoreInventory(Player $player) :void {
        $db = self::getDatabase();
        $name = strtolower($player->getName());

        $inventory = $player->getInventory();
        $inventoryarmor = $player->getArmorInventory();

        $inventory->clearAll();
        $inventoryarmor->clearAll();

        $armor = self::getInventoryContents($player)['armor'];

        foreach(self::getInventoryContents($player)['inv'] as $slot => $item){
            $inventory->setItem($slot, $item);
        }
        if(isset($armor['helmet'])) $inventoryarmor->setHelmet($armor['helmet']);
        if(isset($armor['chestplate'])) $inventoryarmor->setChestplate($armor['chestplate']);
        if(isset($armor['leggings'])) $inventoryarmor->setLeggings($armor['leggings']);
        if(isset($armor['boots'])) $inventoryarmor->setHelmet($armor['boots']);
    }
}