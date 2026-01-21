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
                    $player->sendMessage("§aSucessfully Boarding to Miami, Flordia...");
                    break;
                case 1:
                    $player->sendMessage("§aSucessfully Boarding to Tokyo, Japan...");
                    break;
            }
        });
    
        $form->setTitle("§6§lDepartures");
        $form->setContent("§eSelect your desired location. Each button shows the city you'll travel to:");
    
        // Buttons with images and labels
        $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/a/HlRUICl"); // Miami
        $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/a/RgIyerv"); // Tokyo
    
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
                $player->sendMessage("§bOpening Costumes menu...");
                // Implement Costume menu later
                break;
            case 1:
                $player->sendMessage("§bOpening Trails menu...");
                // Implement Trails menu later
                break;
            case 2:
                $player->sendMessage("§bOpening Titles menu...");
                // Implement Titles menu later
                break;
            case 3:
                $player->sendMessage("§bOpening Pets menu...");
                // Open the Pets plugin menu
                $player->getServer()->dispatchCommand($player, "pets");
                break;
        }
    });

    $form->setTitle("§l§6Your Luggage");
    $form->setContent("§eSelect a category:");

    // Buttons with text titles
    $form->addButton("§bYour Costumes");
    $form->addButton("§bYour Trails");
    $form->addButton("§bYour Titles");
    $form->addButton("§bYour Pets");

    $player->sendForm($form);
}

public function getAccessCard(Player $player): void {
    $form = new SimpleForm(function (Player $player, ?int $data) {
        if ($data === null) return;

        switch ($data) {
            // Online shops
            case 0: $this->getOnlineShop($player, "Costume Boutique"); break;
            case 1: $this->getOnlineShop($player, "Trail Studio"); break;
            case 2: $this->getOnlineShop($player, "Pet Emporium"); break;

            // Walk-to / in-person shops
            case 3: $this->getInPersonShop($player, "Witchs Hut"); break;
            case 4: $this->getInPersonShop($player, "Shift & Sip"); break;
            case 5: $this->getInPersonShop($player, "Skyline Sushi"); break;
            case 6: $this->getInPersonShop($player, "El Taqueria"); break;
            case 7: $this->getInPersonShop($player, "Leaderboard Lounge"); break;
        }
    });

    $form->setTitle("§6Access Card");
    $form->setContent("Tap a shop to go there:");

    // Buttons with images for visual appeal
    $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "hhttps://imgur.com/WGc1aZZ"); // Costume Boutique
    $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/NQsaNBR");   // Trail Studio
    $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/mq8Okkf");     // Pet Emporium
    $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/jTwZnDQ");  // Potion Station
    $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/Lk0jkFK");  // Coffee Corner
    $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/SNU2jTL");  // Snack Noodle Bar
    $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/vIFHD2N");    // Taco Stand
    $form->addButton("", SimpleForm::IMAGE_TYPE_URL, "https://imgur.com/5IrK9Wp");  // Leaderboard Lounge

    $player->sendForm($form);
}

    public function getInPersonShop(Player $player, string $shopName): void {
    $form = new SimpleForm(function (Player $player, ?int $data) use ($shopName) {
        if ($data === null) return;

        switch ($shopName) {
            case "Witchs Hut":
                $player->sendMessage("§aYou received a potion effect! (later: choose effect & duration)");
                break;

            case "Shift & Sip":
                switch($data){
                    case 0: $player->sendMessage("§6You selected Americano!"); break;
                    case 1: $player->sendMessage("§6You selected Espresso!"); break;
                    case 2: $player->sendMessage("§6You selected Latte!"); break;
                    case 3: $player->sendMessage("§6You selected Cappuccino!"); break;
                }
                break;

            case "Skyline Sushi":
                switch($data){
                    case 0: $player->sendMessage("§6You selected Ramen!"); break;
                    case 1: $player->sendMessage("§6You selected Udon!"); break;
                    case 2: $player->sendMessage("§6You selected Dumplings!"); break;
                    case 3: $player->sendMessage("§6You selected Sushi!"); break;
                }
                break;

            case "El Taqueria":
                switch($data){
                    case 0: $player->sendMessage("§6You selected Beef Taco!"); break;
                    case 1: $player->sendMessage("§6You selected Chicken Taco!"); break;
                    case 2: $player->sendMessage("§6You selected Veggie Taco!"); break;
                    case 3: $player->sendMessage("§6You selected Fish Taco!"); break;
                }
                break;
        }
    });

    $form->setTitle("§6" . $shopName);

    // Text buttons instead of images
    switch($shopName){
        case "Witchs Hut":
            $form->setContent("Tap to receive a potion effect:");
            $form->addButton("Get Random Potion Effect");
            break;

        case "Shift & Sip":
            $form->setContent("Select your coffee:");
            $form->addButton("Americano");
            $form->addButton("Espresso");
            $form->addButton("Latte");
            $form->addButton("Cappuccino");
            break;

        case "Skyline Sushi":
            $form->setContent("Select your dish:");
            $form->addButton("Ramen");
            $form->addButton("Udon");
            $form->addButton("Dumplings");
            $form->addButton("Sushi");
            break;

        case "El Taqueria":
            $form->setContent("Select your taco:");
            $form->addButton("Beef Taco");
            $form->addButton("Chicken Taco");
            $form->addButton("Veggie Taco");
            $form->addButton("Fish Taco");
            break;
    }

    $player->sendForm($form);
}



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

