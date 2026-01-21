<?php

namespace LC\ui;

use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\effect\EffectInstance;
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
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;

            switch ($data) {
                case 0: $player->sendMessage("Successfully boarding to Miami, Florida..."); break;
                case 1: $player->sendMessage("Successfully boarding to Tokyo, Japan..."); break;
            }
        });

        $form->setTitle("Departures");
        $form->setContent("Select your destination:");

        // Image buttons replaced with normal buttons for now
        $form->addButton("Miami, Florida");
        $form->addButton("Tokyo, Japan");

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
            if ($data === null) return;
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
            if ($data === null) return;

            switch ($data) {
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
        $coins = $this->plugin->getCoins($player);

        $form = new SimpleForm(function(Player $player, ?int $data){
            if ($data === null) return;

            switch ($data) {
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
        $form->setContent("Coins: $coins\nSelect a shop:");

        // Image buttons replaced with simple buttons for testing
        $form->addButton("Costume Boutique");
        $form->addButton("Trail Studio");
        $form->addButton("Pet Emporium");
        $form->addButton("Witchs Hut (Spin Wheel)");
        $form->addButton("Shift & Sip (Coffee)");
        $form->addButton("Skyline Sushi (Food)");
        $form->addButton("El Taqueria (Taco)");
        $form->addButton("Leaderboard Lounge");

        $player->sendForm($form);
    }

    /* =========================
     * IN-PERSON SHOPS
     * ========================= */
    public function getInPersonShop(Player $player, string $shop): void {
        $coins = $this->plugin->getCoins($player);

        $form = new SimpleForm(function(Player $player, ?int $data) use ($shop){
            if ($data === null) return;

            switch($shop){
                case "Shift & Sip":
                    $this->giveFood($player, "Americano");
                    $this->plugin->addCoins($player, -10);
                    break;

                case "Skyline Sushi":
                    $this->giveFood($player, "Ramen Bowl");
                    $this->plugin->addCoins($player, -40);
                    break;

                case "El Taqueria":
                    $this->giveFood($player, "Beef Taco");
                    $this->plugin->addCoins($player, -30);
                    break;

                case "Witchs Hut":
                    $this->spinWitchWheel($player);
                    break;
            }
        });

        $form->setTitle($shop);
        if ($shop === "Witchs Hut") {
            $form->setContent("Coins: $coins\nSpin the wheel for a random reward (daily limit)");
            $form->addButton("Spin Wheel");
        } else {
            $form->setContent("Coins: $coins\nSelect your item");
            $form->addButton("Receive Item");
        }

        $player->sendForm($form);
    }

    /* =========================
     * GIVE FOOD / COFFEE / CUSTOM ITEMS
     * ========================= */
    private function giveFood(Player $player, string $name): void {
        $item = VanillaItems::BREAD();
        $item->setCustomName($name);
        $player->getInventory()->addItem($item);
        $player->sendMessage("You received $name!");
    }

    /* =========================
     * WITCH WHEEL WITH DAILY LIMITS
     * ========================= */
    private function spinWitchWheel(Player $player): void {
        $data = $this->plugin->getPerksData($player);
        $today = date("Y-m-d");

        if (($data["last_spin"] ?? "") === $today) {
            $player->sendMessage("You already spun the wheel today!");
            return;
        }

        $data["last_spin"] = $today;
        $roll = mt_rand(1, 100);
        $now = time();

        if ($roll <= 60) {
            $data["perks"]["speed"] = $now + 3600;
            $player->sendMessage("Speed boost unlocked for 1 hour!");
        } elseif ($roll <= 85) {
            $data["perks"]["temp_cosmetic"] = $now + 86400;
            $player->sendMessage("Temporary cosmetic unlocked for today!");
        } else {
            $data["perks"]["temp_pet"] = $now + 86400;
            $player->sendMessage("Temporary pet access unlocked for today!");
        }

        $this->plugin->setPerksData($player, $data);
        $this->applyActivePerks($player);
    }

    /* =========================
     * APPLY ACTIVE PERKS
     * ========================= */
    public function applyActivePerks(Player $player): void {
        $data = $this->plugin->getPerksData($player);
        if (empty($data["perks"])) return;

        $now = time();
        $changed = false;

        foreach ($data["perks"] as $perk => $expires) {
            if ($expires <= $now) {
                unset($data["perks"][$perk]);
                $changed = true;
                continue;
            }

            switch($perk){
                case "speed":
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 1200, 1));
                    break;
                case "temp_pet":
                    // Hook into pet plugin
                    break;
                case "temp_cosmetic":
                    // Hook into costume/trail system
                    break;
            }
        }

        if ($changed) $this->plugin->setPerksData($player, $data);
    }

    /* =========================
     * ONLINE SHOP PLACEHOLDER
     * ========================= */
    private function getOnlineShop(Player $player, string $name): void {
        $player->sendMessage("$name coming soon!");
    }
}
