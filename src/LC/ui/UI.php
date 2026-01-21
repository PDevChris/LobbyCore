<?php

namespace LC\ui;

use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use Vecnavium\FormsUI\SimpleForm;
use LC\LobbyCore;

class UI {

    private LobbyCore $plugin;

    public function __construct() {
        $this->plugin = LobbyCore::getInstance();
    }

    /* =========================
     * SERVER SELECTOR
     * ========================= */
    public function getGames(Player $player): void {
        $form = new SimpleForm(function(Player $player, ?int $data){
            if($data === null) return;
            switch($data){
                case 0: $player->sendMessage("Successfully boarding to Miami, Florida"); break;
                case 1: $player->sendMessage("Successfully boarding to Tokyo, Japan"); break;
            }
        });

        $form->setTitle("Departures");
        $form->setContent("Select your destination:");

        $form->addButton("Miami");
        $form->addButton("Tokyo");

        $player->sendForm($form);
    }

    /* =========================
     * STAFF MENU
     * ========================= */
    public function getStaffMenu(Player $player): void {
        if (!$player->hasPermission("lobbycore.staff")) {
            $player->sendMessage("No permission.");
            return;
        }

        $form = new SimpleForm(function(Player $player, ?int $data){
            if($data === null) return;
            $player->sendMessage("Selected option: $data");
        });

        $form->setTitle("Staff Menu");
        $form->addButton("Manage Players");
        $form->addButton("Teleport to Event");
        $form->addButton("Settings");

        $player->sendForm($form);
    }

    /* =========================
     * COSMETICS MENU
     * ========================= */
    public function getCosmetics(Player $player): void {
        $form = new SimpleForm(function(Player $player, ?int $data){
            if($data === null) return;
            switch($data){
                case 0: $player->sendMessage("Opening Costumes..."); break;
                case 1: $player->sendMessage("Opening Trails..."); break;
                case 2: $player->sendMessage("Opening Titles..."); break;
                case 3: $player->getServer()->dispatchCommand($player, "pets"); break;
            }
        });

        $form->setTitle("Your Luggage");
        $form->addButton("Your Costumes");
        $form->addButton("Your Trails");
        $form->addButton("Your Titles");
        $form->addButton("Your Pets");

        $player->sendForm($form);
    }

    /* =========================
     * ACCESS CARD
     * ========================= */
    public function getAccessCard(Player $player): void {
        $form = new SimpleForm(function(Player $player, ?int $data){
            if($data === null) return;
            switch($data){
                case 0: $this->getOnlineShop($player, "Costume Boutique"); break;
                case 1: $this->getOnlineShop($player, "Trail Studio"); break;
                case 2: $this->getOnlineShop($player, "Pet Emporium"); break;
                case 3: $this->getInPersonShop($player, "Witchs Hut"); break;
                case 4: $this->getInPersonShop($player, "Shift & Sip"); break;
                case 5: $this->getInPersonShop($player, "Skyline Sushi"); break;
                case 6: $this->getInPersonShop($player, "El Taqueria"); break;
                case 7: $this->getInPersonShop($player, "Leaderboard Lounge"); break;
            }
        });

        $form->setTitle("Access Card");

        // For now, image buttons replaced with text
        $form->addButton("Costume Boutique");
        $form->addButton("Trail Studio");
        $form->addButton("Pet Emporium");
        $form->addButton("Witchs Hut");
        $form->addButton("Shift & Sip");
        $form->addButton("Skyline Sushi");
        $form->addButton("El Taqueria");
        $form->addButton("Leaderboard Lounge");

        $player->sendForm($form);
    }

    /* =========================
     * IN-PERSON SHOPS
     * ========================= */
    public function getInPersonShop(Player $player, string $shop): void {
        $form = new SimpleForm(function(Player $player, ?int $data) use ($shop){
            if($data === null) return;

            switch($shop){
                case "Shift & Sip":
                    $this->giveFood($player, "Coffee");
                    break;
                case "Skyline Sushi":
                    $this->giveFood($player, "Ramen Bowl");
                    break;
                case "El Taqueria":
                    $this->giveFood($player, "Beef Taco");
                    break;
                case "Witchs Hut":
                    $this->spinWitchWheel($player);
                    break;
            }
        });

        $form->setTitle($shop);

        if($shop === "Witchs Hut"){
            $form->setContent("Spin the wheel for a daily reward");
            $form->addButton("Spin Wheel");
        } else {
            $form->setContent("Receive item");
            $form->addButton("Receive Item");
        }

        $player->sendForm($form);
    }

    /* =========================
     * FOOD GIVER
     * ========================= */
    private function giveFood(Player $player, string $name): void {
        $item = VanillaItems::BREAD();
        $item->setCustomName($name);
        $player->getInventory()->addItem($item);
        $player->sendMessage("You received $name!");
    }

    /* =========================
     * WITCH WHEEL
     * ========================= */
    private function spinWitchWheel(Player $player): void {
        $core = LobbyCore::getInstance();
        $name = strtolower($player->getName());
        $today = date("Y-m-d");

        $data = $core->getPerksData($player);

        if(($data["last_spin"] ?? "") === $today){
            $player->sendMessage("You already spun the wheel today.");
            return;
        }

        $data["last_spin"] = $today;
        $roll = mt_rand(1,100);
        $now = time();

        if($roll <= 60){
            $expires = $now + 3600;
            $data["perks"]["speed"] = $expires;
            $player->sendMessage("Speed boost unlocked for 1 hour!");
        } elseif($roll <= 85){
            $expires = $now + 86400;
            $data["perks"]["temp_cosmetic"] = $expires;
            $player->sendMessage("Temporary cosmetic unlocked for today!");
        } else {
            $expires = $now + 86400;
            $data["perks"]["temp_pet"] = $expires;
            $player->sendMessage("Temporary pet access unlocked for today!");
        }

        $core->setPerksData($player, $data);
        $this->applyActivePerks($player);
    }

    public function applyActivePerks(Player $player): void {
        $core = LobbyCore::getInstance();
        $data = $core->getPerksData($player);
        if(empty($data["perks"])) return;

        $now = time();
        $changed = false;

        foreach($data["perks"] as $perk => $expires){
            if($expires <= $now){
                unset($data["perks"][$perk]);
                $changed = true;
                continue;
            }

            switch($perk){
                case "speed":
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20*60, 1));
                    break;
                case "temp_pet":
                case "temp_cosmetic":
                    // Hook into pet/cosmetic system later
                    break;
            }
        }

        if($changed){
            $core->setPerksData($player, $data);
        }
    }

    /* =========================
     * ONLINE SHOPS
     * ========================= */
    private function getOnlineShop(Player $player, string $name): void {
        $player->sendMessage("$name coming soon!");
    }
}
