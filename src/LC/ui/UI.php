<?php

namespace LC\ui;

use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\effect\EffectInstance;
use Vecnavium\FormsUI\SimpleForm;
use LC\LobbyCore;

class UI {

    private LobbyCore $plugin;
    private Config $perkData;


    public function __construct() {
        $this->plugin = LobbyCore::getInstance();

          @mkdir($this->plugin->getDataFolder());
            $this->perkData = new Config(
                $this->plugin->getDataFolder() . "temp_perks.yml",
                Config::YAML
            );
    }

    /* =========================
     * SERVER SELECTOR
     * ========================= */
    public function getGames(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;

            switch ($data) {
                case 0:
                    $player->sendMessage("§aSuccessfully boarding to Miami, Florida...");
                    break;
                case 1:
                    $player->sendMessage("§aSuccessfully boarding to Tokyo, Japan...");
                    break;
            }
        });

        $form->setTitle("§6§lDepartures");
        $form->setContent("§eSelect your destination:");

        $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/a/HlRUICl");
        $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/a/RgIyerv");

        $player->sendForm($form);
    }

    /* =========================
     * STAFF MENU
     * ========================= */
    public function getStaffMenu(Player $player): void {
        if (!$player->hasPermission("lobbycore.staff")) {
            $player->sendMessage("§cNo permission.");
            return;
        }

        $form = new SimpleForm(function(Player $player, ?int $data){
            if ($data === null) return;
            $player->sendMessage("Selected option: $data");
        });

        $form->setTitle("§5Staff Menu");
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
                case 3:
                    $player->getServer()->dispatchCommand($player, "pets");
                    break;
            }
        });

        $form->setTitle("§6§lYour Luggage");
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

        $form->setTitle("§6Access Card");

        $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "hhttps://imgur.com/WGc1aZZ");
        $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/NQsaNBR");
        $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/mq8Okkf");
        $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/jTwZnDQ");
        $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/Lk0jkFK");
        $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/SNU2jTL");
        $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/vIFHD2N");
        $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/5IrK9Wp");

        $player->sendForm($form);
    }

    /* =========================
     * IN-PERSON SHOPS
     * ========================= */
    public function getInPersonShop(Player $player, string $shop): void {
        $form = new SimpleForm(function(Player $player, ?int $data) use ($shop){
            if ($data === null) return;

            switch ($shop) {
                case "Shift & Sip":
                    $this->giveFood($player, "Americano");
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

        $form->setTitle("§6$shop");

        if ($shop === "Witchs Hut") {
            $form->setContent("Spin the wheel for a random reward");
            $form->addButton("Spin Wheel");
        } else {
            $form->setContent("Select item");
            $form->addButton("Receive Item");
        }

        $player->sendForm($form);
    }

    /* =========================
     * FOOD GIVER
     * ========================= */
    private function giveFood(Player $player, string $name): void {
        $item = VanillaItems::BREAD();
        $item->setCustomName("§r§f$name");
        $player->getInventory()->addItem($item);
        $player->sendMessage("§aYou received $name");
    }

    /* =========================
     * WITCH WHEEL
     * ========================= */
    private function spinWitchWheel(Player $player): void {
    $name = strtolower($player->getName());
    $today = date("Y-m-d");

    $data = $this->perkData->get($name, [
        "last_spin" => "",
        "perks" => []
    ]);

    /* DAILY LIMIT CHECK */
    if ($data["last_spin"] === $today) {
        $player->sendMessage("§cYou already spun the wheel today.");
        return;
    }

    $data["last_spin"] = $today;

    $roll = mt_rand(1, 100);
    $now = time();

    /* COMMON – EFFECT */
    if ($roll <= 60) {
        $expires = $now + (60 * 60); // 1 hour
        $data["perks"]["speed"] = $expires;

        $player->sendMessage("§aSpeed boost unlocked for 1 hour");

    /* UNCOMMON – TEMP COSMETIC */
    } elseif ($roll <= 85) {
        $expires = $now + (24 * 60 * 60); // 1 day
        $data["perks"]["temp_cosmetic"] = $expires;

        $player->sendMessage("§bTemporary cosmetic unlocked for today");

    /* RARE – TEMP PET ACCESS */
    } else {
        $expires = $now + (24 * 60 * 60); // 1 day
        $data["perks"]["temp_pet"] = $expires;

        $player->sendMessage("§dTemporary pet access unlocked for today");
    }

    $this->perkData->set($name, $data);
    $this->perkData->save();

    $this->applyActivePerks($player);
}

    public function applyActivePerks(Player $player): void {
    $name = strtolower($player->getName());
    $data = $this->perkData->get($name);

    if (!$data || empty($data["perks"])) return;

    $now = time();
    $changed = false;

    foreach ($data["perks"] as $perk => $expires) {
        if ($expires <= $now) {
            unset($data["perks"][$perk]);
            $changed = true;
            continue;
        }

        switch ($perk) {
            case "speed":
                $player->getEffects()->add(
                    new EffectInstance(VanillaEffects::SPEED(), 20 * 60, 1)
                );
                break;

            case "temp_pet":
                // Example: allow pet command temporarily
                // Hook this into your pet plugin later
                break;

            case "temp_cosmetic":
                // Hook into costume/trail systems later
                break;
        }
    }

    if ($changed) {
        $this->perkData->set($name, $data);
        $this->perkData->save();
    }
}



    /* =========================
     * PLACEHOLDER ONLINE SHOP
     * ========================= */
    private function getOnlineShop(Player $player, string $name): void {
        $player->sendMessage("§e$name coming soon");
    }
}
