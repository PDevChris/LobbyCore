<?php

namespace LC;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\world\Position;
use mysqli;

// Events
use LC\event\EventListener;
use LC\event\Protection;

// Commands
use LC\commands\HubCommand;
use LC\commands\ItemCommand;

// UI
use LC\ui\UI;

// Storage
use LC\storage\DataProvider;
use LC\storage\YamlProvider;
use LC\storage\MysqlProvider;

class LobbyCore extends PluginBase implements Listener {

    private static LobbyCore $instance;

    private Config $config;
    private ?mysqli $db = null;
    private DataProvider $provider;

    /* -------------------- CORE -------------------- */

    public static function getInstance(): LobbyCore {
        return self::$instance;
    }

    public function onLoad(): void {
        self::$instance = $this;
    }

    public function onEnable(): void {
        self::$instance = $this;

        $this->saveDefaultConfig();
        $this->config = $this->getConfig();

        $this->initStorage();

        // Events
        $pm = $this->getServer()->getPluginManager();
        $pm->registerEvents(new EventListener(), $this);
        $pm->registerEvents(new Protection(), $this);

        // Commands
        $this->getServer()->getCommandMap()->register("hub", new HubCommand());
        $this->getServer()->getCommandMap()->register("item", new ItemCommand());

        $this->getLogger()->info("LobbyCore enabled");
    }

    public function onDisable(): void {
        if ($this->db !== null) {
            $this->db->close();
        }
        $this->getLogger()->info("LobbyCore disabled");
    }

    /* -------------------- STORAGE -------------------- */

    private function initStorage(): void {
        $mysql = $this->config->get("mysql");

        if ($mysql["enabled"] === true && $this->connectMysql($mysql)) {
            $this->provider = new MysqlProvider($this, $this->db);
            $this->getLogger()->info("Using MySQL storage");
        } else {
            $this->provider = new YamlProvider($this);
            $this->getLogger()->warning("Using YAML storage (testing mode)");
        }
    }

    private function connectMysql(array $cfg): bool {
        try {
            $this->db = new mysqli(
                $cfg["host"],
                $cfg["user"],
                $cfg["password"],
                $cfg["database"],
                $cfg["port"]
            );

            if ($this->db->connect_error) {
                $this->db = null;
                return false;
            }
            return true;
        } catch (\Throwable) {
            $this->db = null;
            return false;
        }
    }

    public function getProvider(): DataProvider {
        return $this->provider;
    }

    public function getDB(): ?mysqli {
        return $this->db;
    }

    /* -------------------- UI -------------------- */

    public static function getUI(): UI {
        return new UI();
    }

    /* -------------------- CONFIG HELPERS -------------------- */

    public function getLobbySpawn(): Position {
        $spawn = $this->config->get("lobby")["spawn"];
        $world = Server::getInstance()->getWorldManager()->getWorldByName($spawn["world"]);

        return new Position(
            $spawn["x"],
            $spawn["y"],
            $spawn["z"],
            $world,
            $spawn["yaw"] ?? 0,
            $spawn["pitch"] ?? 0
        );
    }

    public function getLobbyItems(): array {
        return $this->config->get("items", []);
    }
}
