<?php

declare(strict_types=1);

namespace alvin0319\Friend\form;

use alvin0319\Friend\Friend;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function array_map;
use function is_int;

class FriendRequestListForm implements Form{

	protected Player $player;

	protected array $friendRequests = [];

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function jsonSerialize() : array{
		$this->friendRequests = Friend::getInstance()->getQueues($this->player);
		return [
			"type" => "form",
			"title" => "Friend - Master",
			"content" => "",
			"buttons" => array_map(function(string $name) : array{
				return ["text" => (string) $name];
			}, $this->friendRequests)
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		$player->sendForm(new FriendAcceptConfirmForm($player, $this->friendRequests[$data], $data));
	}
}