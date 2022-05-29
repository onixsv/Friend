<?php

declare(strict_types=1);

namespace alvin0319\Friend;

use alvin0319\Friend\command\FriendCommand;
use OnixUtils\OnixUtils;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use function array_filter;
use function array_map;
use function array_search;
use function array_values;
use function count;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function in_array;
use function json_decode;
use function json_encode;
use function strtolower;

function convert($player) : string{
	return $player instanceof Player ? strtolower($player->getName()) : strtolower($player);
}

class Friend extends PluginBase{
	use SingletonTrait;

	protected array $db = [];

	protected array $chat = [];

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		if(file_exists($file = $this->getDataFolder() . "FriendData.json")){
			$this->db = json_decode(file_get_contents($file), true);
		}

		$this->getServer()->getPluginManager()->registerEvent(PlayerJoinEvent::class, function(PlayerJoinEvent $event) : void{
			$player = $event->getPlayer();
			if(!$this->hasData($player)){
				$this->createData($player);
			}
			foreach($this->getOnlineFriends($player) as $friend){
				OnixUtils::message($friend, "친구 §d{$player->getName()}§f님이 접속했습니다.");
			}

			if(count($queues = $this->getQueues($player)) > 0){
				OnixUtils::message($player, "대기중인 친구 신청이 §d" . count($queues) . "§f개 있습니다.");
			}
		}, EventPriority::NORMAL, $this);

		$this->getServer()->getPluginManager()->registerEvent(PlayerQuitEvent::class, function(PlayerQuitEvent $event) : void{
			$player = $event->getPlayer();
			foreach($this->getOnlineFriends($player) as $friend){
				OnixUtils::message($friend, "친구 §d{$player->getName()}§f님이 퇴장했습니다.");
			}
		}, EventPriority::NORMAL, $this);

		$this->getServer()->getPluginManager()->registerEvent(PlayerChatEvent::class, function(PlayerChatEvent $event) : void{
			$player = $event->getPlayer();
			if(isset($this->chat[convert($player)])){
				$event->cancel();
				$friend = $this->chat[convert($player)];
				if(($friend = $this->getServer()->getPlayerExact($friend)) !== null){
					$friend->sendMessage("§d<§f{$player->getName()} -> 나§d> §f" . $event->getMessage());
					$player->sendMessage("§d<§f나 -> {$friend->getName()}§d> §f" . $event->getMessage());
					foreach($this->getServer()->getOnlinePlayers() as $op){
						if($op->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
							$op->sendMessage("§d<§f{$player->getName()} -> {$friend->getName()}§d> §f" . $event->getMessage());
						}
					}
					$this->getServer()->getLogger()->info("§d<§f{$player->getName()} -> {$friend->getName()}§d> §f" . $event->getMessage());
				}else{
					OnixUtils::message($player, "친구가 나가서 자동 전체채팅 모드로 전환됐습니다.");
					$this->removeFriendChat($player);
				}
			}
		}, EventPriority::HIGHEST, $this);

		$this->getServer()->getCommandMap()->register("friend", new FriendCommand());
	}

	protected function onDisable() : void{
		file_put_contents($this->getDataFolder() . "FriendData.json", json_encode($this->db));
	}

	public function createData($player) : void{
		if(!$this->hasData($player)){
			$this->db[convert($player)] = [
				"friends" => [],
				"queue" => []
			];
		}
	}

	public function addFriend($player, $target) : void{
		if(!$this->hasData($player))
			$this->createData($player);
		if(!$this->hasData($target)){
			$this->createData($target);
		}
		$this->db[convert($player)]["friends"][] = convert($target);
		$this->db[convert($target)]["friends"][] = convert($player);
	}

	public function removeFriend($player, int $index) : void{
		if(!$this->hasData($player))
			return;
		$friend = $this->db[convert($player)]["friends"][$index];
		unset($this->db[convert($player)]["friends"][$index]);
		$this->db[convert($player)]["friends"] = array_values($this->db[convert($player)]["friends"]);
		unset($this->db[convert($friend)]["friends"][array_search(convert($player), $this->db[convert($friend)]["friends"])]);
		$this->db[convert($friend)]["friends"] = array_values($this->db[convert($friend)]["friends"]);
	}

	public function isFriend($player, $target) : bool{
		if(!$this->hasData($player)){
			return false;
		}
		return in_array(convert($target), $this->db[convert($player)]["friends"]);
	}

	public function addQueue($player, $sender) : void{
		if(!$this->hasData($player)){
			$this->createData($player);
		}
		$this->db[convert($player)]["queue"][] = convert($sender);
	}

	public function removeQueue($player, int $index) : void{
		if(!$this->hasData($player)){
			return;
		}
		unset($this->db[convert($player)]["queue"][$index]);
		$this->db[convert($player)]["queue"] = array_values($this->db[convert($player)]["queue"]);
	}

	public function isQueue($player, $target) : bool{
		if(!$this->hasData($player)){
			return false;
		}
		if(!$this->hasData($target)){
			return false;
		}
		return in_array(convert($target), $this->db[convert($player)]["queue"]);
	}

	public function getFriends($player) : array{
		if(!$this->hasData($player)){
			return [];
		}
		return $this->db[convert($player)]["friends"];
	}

	/**
	 * @return Player[]
	 */
	public function getOnlineFriends($player) : array{
		if(!$this->hasData($player)){
			return [];
		}
		return array_values(array_filter(array_map(function(string $name) : ?Player{
			return $this->getServer()->getPlayerExact($name);
		}, $this->db[convert($player)]["friends"]), function(?Player $player) : bool{
			return $player !== null;
		}));
	}

	public function getQueues($player) : array{
		if(!$this->hasData($player)){
			return [];
		}
		return $this->db[convert($player)]["queue"];
	}

	public function isOnline($player) : bool{
		return $this->getServer()->getPlayerExact(convert($player)) !== null;
	}

	public function hasData($player) : bool{
		return isset($this->db[convert($player)]);
	}

	public function setFriendChat($player, $target) : void{
		$this->chat[convert($player)] = convert($target);
	}

	public function isFriendChat($player) : bool{
		return isset($this->chat[convert($player)]);
	}

	public function removeFriendChat($player) : void{
		unset($this->chat[convert($player)]);
	}
}