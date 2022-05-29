<?php

declare(strict_types=1);

namespace alvin0319\Friend\form;

use alvin0319\Friend\Friend;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function array_map;
use function is_int;

class FriendChatForm implements Form{
	/** @var Player[] */
	protected array $friends = [];
	/** @var Player */
	protected Player $player;

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function jsonSerialize() : array{
		$this->friends = Friend::getInstance()->getOnlineFriends($this->player);
		return [
			"type" => "form",
			"title" => "Friend - Master",
			"content" => "채팅할 친구를 선택해주세요.",
			"buttons" => array_map(function(Player $player) : array{
				return ["text" => "§d{$player->getName()}§f님"];
			}, $this->friends)
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		if(!isset($this->friends[$data])){
			return;
		}
		Friend::getInstance()->setFriendChat($player, $this->friends[$data]->getName());
	}
}