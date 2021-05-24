<?php

namespace Nerzox\InventorySync;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\Listener;

use Nerzox\InventorySync\Database;

class Loader extends PluginBase implements Listener{

    /**
     * @var static $i
     */
    public static $i;

    /**
     * @var private $lang
     */
    private static $lang;

    /**
     * @var private $config
     */
    private static $config;

    /**
     * @return void
     */
    public function onEnable() :void {
        self::$i = $this;

        $this->initConfig();

        Database::init();

        $this->getServer()->getPluginManager()->registerEvents(new Events\BaseListener($this), $this);

        $this->getLogger()->info($this->getLangFile()['enable-message']);
    }

    /**
     * @return Loader
     */
    public static function getInstance() :Loader {
        return self::$i;
    }

    /**
     * @return void
     */
    public function onDisable() :void {
        $this->getLogger()->info($this->getLangFile()['disable-message']);
    }

    /**
     * @return void
     */
    public function initConfig() :void {
        $this->saveResource('lang.yml');
        $this->saveResource('config.yml');
        self::$config = new Config($this->getDataFolder() . 'config.yml', Config::YAML);
        self::$lang = new Config($this->getDataFolder() . 'lang.yml', Config::YAML);
    }

    /**
     * @return Config
     */
    public static function getConfigFile() :array {
        self::$config = new Config(self::getInstance()->getDataFolder() . 'config.yml', Config::YAML);
        return self::$config->getAll();
    }

    /**
     * @return Config
     */
    public static function getLangFile() :array {
        self::$lang = new Config(self::getInstance()->getDataFolder() . 'lang.yml', Config::YAML);
        return self::$lang->getAll();
    }
}