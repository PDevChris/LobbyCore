<?php

namespace LC;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\world\Position;
use mysqli;
use LC\ui\UI;
use LC\event\EventListener;
use LC\event\Protection;

class LobbyCore extends PluginBase implements Listener {

    private static LobbyCore $instance;
    private Config $perkData;
    private Config $coinsData;
    private Config $dailyLimits;

    private ?mysqli $db = null;
    private bool $usingMySQL = false;

    public function onLoad(): void {
        self::$instance = $this;
    }

    public function onEnable(): void {
        self::$instance = $this;

        @mkdir($this->getDataFolder());

        // Load YAML files for fallback
        $this->perkData = new Config($this->getDataFolder() . "temp_perks.yml", Config::YAML);
        $this->coinsData = new Config($this->getDataFolder() . "temp_coins.yml", Config::YAML);
        $this->dailyLimits = new Config($this->getDataFolder() . "daily_limits.yml", Config::YAML);

        // Attempt MySQL connection
        $host = "localhost";
        $user = "root";
        $pass = "";
        $dbName = "lobbycore";

        $this->db = @new mysqli($host, $user, $pass, $dbName);
        if ($this->db && !$this->db->connect_error) {
            $this->usingMySQL = true;
            $this->getLogger()->info("Connected to MySQL!");
        } else {
            $this->getLogger()->warning("MySQL not available. Using YAML storage.");
        }

        // Register events
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new Protection(), $this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        // Commands
        $this->getServer()->getCommandMap()->register("hub", new \LC\commands\HubCommand());
        $this->getServer()->getCommandMap()->register("item", new \LC\commands\ItemCommand());

        $this->getLogger()->info("LobbyCore Enabled");
    }

    public function onDisable(): void {
        $this->getLogger()->info("LobbyCore Disabled");
        $this->perkData->save();
        $this->coinsData->save();
        $this->dailyLimits->save();
    }

    public static function getInstance(): LobbyCore {
        return self::$instance;
    }

    public static function getUI(): UI {
        return new UI();
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        UI::applyActivePerks($player);
    }

    /* =========================
     * PERK STORAGE METHODS
     * ========================= */
    public function getPerksData(Player $player): array {
        $name = strtolower($player->getName());
        return $this->perkData->get($name, ["last_spin" => "", "perks" => []]);
    }

    public function setPerksData(Player $player, array $data): void {
        $name = strtolower($player->getName());
        $this->perkData->set($name, $data);
        $this->perkData->save();
    }

    /* =========================
     * COIN STORAGE METHODS
     * ========================= */
    public function getCoins(Player $player): int {
        $name = strtolower($player->getName());
        return $this->coinsData->get($name, 0);
    }

    public function setCoins(Player $player, int $amount): void {
        $name = strtolower($player->getName());
        $this->coinsData->set($name, $amount);
        $this->coinsData->save();
    }

    public function addCoins(Player $player, int $amount): void {
        $coins = $this->getCoins($player);
        $this->setCoins($player, $coins + $amount);
    }

    /* =========================
     * DAILY LIMITS
     * ========================= */
    public function getDailyLimit(Player $player, string $type): int {
        $name = strtolower($player->getName());
        $limits = $this->dailyLimits->get($name, []);
        return $limits[$type] ?? 0;
    }

    public function setDailyLimit(Player $player, string $type, int $value): void {
        $name = strtolower($player->getName());
        $limits = $this->dailyLimits->get($name, []);
        $limits[$type] = $value;
        $this->dailyLimits->set($name, $limits);
        $this->dailyLimits->save();
    }

    public function resetDailyLimits(): void {
        foreach ($this->dailyLimits->getAll() as $name => $limits) {
            $this->dailyLimits->set($name, []);
        }
        $this->dailyLimits->save();
    }

    /* =========================
     * LOBBY SPAWN & ITEMS
     * ========================= */
    public function getLobbySpawn(): Position {
        $spawn = $this->getConfig()->get("lobby")["spawn"];
        $world = $this->getServer()->getWorldManager()->getWorldByName($spawn["world"]);
        return new Position($spawn["x"], $spawn["y"], $spawn["z"], $world, $spawn["yaw"], $spawn["pitch"]);
    }

    public function getLobbyItems(): array {
        return $this->getConfig()->get("items", []);
    }

}
