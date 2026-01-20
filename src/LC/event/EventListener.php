<?php

namespace LC\event;

use LC\LobbyCore;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use pocketmine\Server;

class EventListener implements Listener {

    private LobbyCore $plugin;

    public function __construct() {
        $this->plugin = LobbyCore::getInstance();
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();

        // Remove default join message
        $event->setJoinMessage("");

        // Broadcast custom join message
        Server::getInstance()->broadcastMessage("§8[§b+§8]§a{$player->getName()}");

        // Teleport player to configured lobby spawn
        $player->teleport($this->plugin->getLobbySpawn());

        // Clear inventory & armor
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        // Give lobby items from config
        foreach ($this->plugin->getLobbyItems() as $itemConfig) {
            $item = ItemFactory::getInstance()->get((int)$itemConfig["id"], 0, 1);
            $item->setCustomName($itemConfig["name"]);
            $player->getInventory()->setItem($itemConfig["slot"] - 1, $item);
        }
    }

    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $event->setQuitMessage("");
        Server::getInstance()->broadcastMessage("§8[§c-§8]§c{$player->getName()}");
    }

    public function onClick(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $itemName = $player->getInventory()->getItemInHand()->getCustomName();
        $ui = LobbyCore::getUI();

        // Handle item actions
        switch ($itemName) {
            case "Boarding Ticket":
                $ui->getGames($player); // Server selector form
                break;
            case "Airport Staff":
                $ui->getStaffMenu($player); // Staff GUI
                break;
            case "Luggage":
                $ui->getCosmetics($player); // Cosmetics menu
                break;
            case "Access Card":
                $ui->getShop($player); // Shop menu
                break;
            case "Your Phone":
                $ui->getProfile($player); // Profile/friends/inbox
                break;
        }
    }
}
