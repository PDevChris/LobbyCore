<?php

namespace LC;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\world\Position;

//Events
use LC\event\EventListener;
use LC\event\Protection;

//Commands
use LC\commands\HubCommand;
use LC\commands\ItemCommand;

//UIs
use LC\ui\UI;

class LobbyCore extends PluginBase implements Listener {

    private static LobbyCore $instance;
    private Config $config;

    public function onLoad() : void {
        self::$instance = $this;
    }

    public function onEnable(): void {
        $this->getLogger()->info("§aEnabled LobbyCore");

        // Load config.yml
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();

        // Register events
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new Protection(), $this);

        // Register commands
        $this->getServer()->getCommandMap()->register("hub", new HubCommand());
        $this->getServer()->getCommandMap()->register("item", new ItemCommand());
    }

    public function onDisable(): void {
        $this->getLogger()->info("§cDisabled LobbyCore");
    }

    public static function getInstance() : LobbyCore {
        return self::$instance;
    }

    public static function getUI() : UI {
        return new UI();
    }

    /**
     * Get lobby spawn location from config
     */
    public function getLobbySpawn() : Position {
        $spawn = $this->config->get("lobby")["spawn"];
        $world = $this->getServer()->getWorldManager()->getWorldByName($spawn["world"]);
        return new Position($spawn["x"], $spawn["y"], $spawn["z"], $world, $spawn["yaw"], $spawn["pitch"]);
    }

    /**
     * Get items config from config.yml
     */
    public function getLobbyItems() : array {
        return $this->config->get("items");
    }
}
