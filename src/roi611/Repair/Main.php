<?php

namespace roi611\Repair;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\event\Lisnener;
use pocketmine\utils\Config;

use pocketmine\item\Durable;

use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use onebone\economyapi\EconomyAPI;

use form\ModalForm;

class Main extends PluginBase {

    private $config;
    public function onEnable():void{
        
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, array(
            'money' => '15000'
        ));

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{

        if(!$sender instanceof Player){

            $sender->sendMessage("ゲーム内で実行してください");
            return true;

        }

        $api = EconomyAPI::getInstance();
        $pay = $this->config->get("money");
        $have = $api->myMoney($sender);

        if ($have < $pay) {
            $sender->sendMessage("§eお金が足りません");
            return true;
        }

        $item = $sender->getInventory()->getItemInHand();

        if(!$item instanceof Durable){
            $sender->sendMessage("§6修理可能なアイテムではありません");
            return true;
        }

        if($item->getDamage() < 0){
            $sender->sendMessage("§6修理の必要がありません");
            return true;
        }
                
        $form = new ModalForm;
        $form->setTitle("UIRepair");
        $unit = $api->getMonetaryUnit();
        $form->setContent("§e{$pay} {$unit}§r で修理しますか？\nあなたの所持金: §e{$have} {$unit}§r");
        $form->setButton1("はい");
        $form->setButton2("いいえ");
        $form->setCallable(function(Player $player,$data){

            if($data === null){
                return;
            }

            if($data){

                $pay = $this->config->get("money");
                $have = $api->myMoney($player);

                if ($have < $pay) {
                    $player->sendMessage("§eお金が足りません");
                }

                $item = $player->getInventory()->getItemInHand();

                if(!$item instanceof Durable){
                    $player->sendMessage("§6修理可能なアイテムではありません");
                }

                if($item->getDamage() < 0){
                    $player->sendMessage("§6修理の必要がありません");
                }

                $api->reduceMoney($player, $pay);
                $player->getInventory()->setItemInHand($player->getInventory()->getItemInHand()->setDamage(0));
                $unit = $api->getMonetaryUnit();
                $player->sendMessage("§e{$pay} {$unit}§r を使用して修理しました");

            } else {
                $player->sendMessage("キャンセルしました");
            }

        });

        $sender->sendForm($form);

        return true;

    }

}