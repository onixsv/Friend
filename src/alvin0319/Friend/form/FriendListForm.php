<?php

declare(strict_types=1);

namespace alvin0319\Friend\form;

use alvin0319\Friend\Friend;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function array_map;

class FriendListForm implements Form{

	protected Player $player;

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function jsonSerialize() : array{
		return [
			"type" => "form",
			"title" => "Friend - Master",
			"content" => "",
			"buttons" => array_map(function(string $name) : array{
				if(Friend::getInstance()->isOnline($name)){
					$name = "§a{$name}";
				}else{
					$name = "§c{$name}";
				}
				return ["text" => $name];
			}, Friend::getInstance()->getFriends($this->player))
		];
	}

	public function handleResponse(Player $player, $data) : void{
	}
}