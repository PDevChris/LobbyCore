<?php

namespace LC\ui;

use pocketmine\player\Player;
use Vecnavium\FormsUI\SimpleForm;
use LC\LobbyCore;

class UI {

    private LobbyCore $plugin;

    public function __construct() {
        $this->plugin = LobbyCore::getInstance();
    }

    /**
     * Server selector (Boarding Ticket)
     */
    public function getGames(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;

            switch ($data) {
                case 0:
                    $player->sendMessage("Teleporting to UHC Solos...");
                    // Add your server transfer code here
                    break;
                case 1:
                    $player->sendMessage("Teleporting to UHC Clans...");
                    break;
                case 2:
                    $player->sendMessage("Teleporting to F1 Racing...");
                    break;
            }
        });

        $form->setTitle("§bSelect a Game");
        $form->setContent("Choose the game you want to play:");
        $form->addButton("UHC Solos");
        $form->addButton("UHC Clans");
        $form->addButton("F1 Racing");

        $player->sendForm($form);
    }

    /**
     * Staff menu (Airport Staff)
     */
    public function getStaffMenu(Player $player): void {
        if (!$player->hasPermission("lobbycore.staff")) {
            $player->sendMessage("§cYou do not have permission to access this menu.");
            return;
        }

        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            // Add staff actions here
            $player->sendMessage("Selected staff option: " . $data);
        });

        $form->setTitle("§5Staff Menu");
        $form->setContent("Choose an action:");
        $form->addButton("Manage Players");
        $form->addButton("Teleport to Event");
        $form->addButton("Settings");

        $player->sendForm($form);
    }

    /**
     * Cosmetics menu (Luggage)
     */
    public function getCosmetics(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    $player->sendMessage("Opening Costumes...");
                    break;
                case 1:
                    $player->sendMessage("Opening Trails...");
                    break;
                case 2:
                    $player->sendMessage("Opening Titles...");
                    break;
                case 3:
                    $player->sendMessage("Opening Pets...");
                    break;
            }
        });

        $form->setTitle("§bCosmetics");
        $form->setContent("Select a category:");
        $form->addButton("Costumes");
        $form->addButton("Trails");
        $form->addButton("Titles");
        $form->addButton("Pets");

        $player->sendForm($form);
    }

    /**
     * Shop menu (Access Card)
     */
    public function getShop(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            // Add shop actions here
            $player->sendMessage("Selected shop option: " . $data);
        });

        $form->setTitle("§aLobby Shop");
        $form->setContent("Purchase items and upgrades here:");
        $form->addButton("Coins");
        $form->addButton("Upgrades");
        $form->addButton("Misc");

        $player->sendForm($form);
    }

    /**
     * Profile / Friends / Inbox (Phone)
     */
    public function getProfile(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    $player->sendMessage("Opening Profile...");
                    break;
                case 1:
                    $player->sendMessage("Opening Friends List...");
                    break;
                case 2:
                    $player->sendMessage("Opening Inbox...");
                    break;
                case 3:
                    $player->sendMessage("Opening Experience...");
                    break;
            }
        });

        $form->setTitle("§dYour Phone");
        $form->setContent("Select an option:");
        $form->addButton("Profile");
        $form->addButton("Friends");
        $form->addButton("Inbox");
        $form->addButton("Experience");

        $player->sendForm($form);
    }
}
