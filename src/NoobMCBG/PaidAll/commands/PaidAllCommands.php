<?php

declare(strict_types=1);

namespace NoobMCBG\PaidAll\commands;

use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use onebone\economyapi\EconomyAPI;
use NoobMCBG\PaidAll\PaidAll;

class PaidAllCommands extends Command implements PluginOwned {

	private PaidAll $plugin;

	public function __construct(PaidAll $plugin){
		$this->plugin = $plugin;
		parent::__construct("paidall", "Commands paid all players online", null, ["pa", "payall"]);
		$this->setPermission("paidall.paid");
	}

	public function execute(CommandSender $sender, string $label, array $args){
		if(!$sender instanceof Player){
			if(isset($args[0])){
                if(!is_numeric($args[0])){
		        	$sender->sendMessage("§cUsage:§7 /paidall <money>");
		        	return true;
		        }
		        if(count($this->getOwningPlugin()->getServer()->getOnlinePlayers()) == 0){
		        	$sender->sendMessage("There is 0 players online !");
		        }else{
		            $money = (int)$args[0];
                    $count = count($this->getOwningPlugin()->getServer()->getOnlinePlayers());
		        	$amount = (int)$args[0]/$count;
		            $this->getOwningPlugin()->payAll($money);
		            if($this->getOwningPlugin()->getConfig()->getAll()["broadcast-paid"]["broadcast"] == true){
                        $this->getOwningPlugin()->getServer()->broadcastMessage(str_replace(["{money}", "{player}"], [$amount, "CONSOLE"], strval($this->getOwningPlugin()->getConfig()->getAll()["broadcast-paid"]["msg-broadcast-paid"])));
                    }
		        }
		    }else{
		    	$sender->sendMessage("§cUsage:§7 /paidall <money>");
		    }
		}else{
			if(isset($args[0])){
		        if(!is_numeric($args[0])){
		        	$sender->sendMessage("§cUsage:§7 /paidall <money>");
		        	return true;
		        }
		        foreach($this->getOwningPlugin()->getServer()->getOnlinePlayers() as $player){
		        	if($player instanceof Player){
		        		if(EconomyAPI::getInstance()->myMoney($sender) >= (int)$args[0]){
		        		    $count = count($this->getOwningPlugin()->getServer()->getOnlinePlayers());
		        		    $amount = (int)$args[0]/$count;
		        		    EconomyAPI::getInstance()->reduceMoney($sender, $args[0]);
                            EconomyAPI::getInstance()->addMoney($player, $amount);
                            if($this->getOwningPlugin()->getConfig()->getAll()["paid-successfully"]["msg"] == true){
                                $sender->sendMessage(str_replace(["{money}"], [$amount], strval($this->getOwningPlugin()->getConfig()->getAll()["paid-successfully"]["msg-paid-successfully"])));
                            }
                            if($this->getOwningPlugin()->getConfig()->getAll()["broadcast-paid"]["broadcast"] == true){
                                $this->getOwningPlugin()->getServer()->broadcastMessage(str_replace(["{money}", "{player}"], [$amount, $sender->getName()], strval($this->getOwningPlugin()->getConfig()->getAll()["broadcast-paid"]["msg-broadcast-paid"])));
                            }
                        }else{
                        	$price = (int)$args[0] - EconomyAPI::getInstance()->myMoney($sender);
                        	if($this->getOwningPlugin()->getConfig()->getAll()["paid-fallied"]["msg"] == true){
                                $sender->sendMessage(str_replace(["{price}"], [$price], strval($this->getOwningPlugin()->getConfig()->getAll()["paid-fallied"]["msg-paid-fallied"])));
                            }
                        }
		        	}
		        }
		    }else{
		    	$sender->sendMessage("§cUsage:§7 /paidall <money>");
		    }
		}
	}

	public function getOwningPlugin() : PaidAll {
		return $this->plugin;
	}
}