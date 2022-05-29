<?php

declare(strict_types=1);

namespace alvin0319\Friend\form;

use alvin0319\Friend\Friend;
use OnixUtils\OnixUtils;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function array_map;
use function array_values;
use function is_int;

class FriendRemoveForm implements Form{

	protected array $friends = [];

	protected Player $player;

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function jsonSerialize() : array{
		$this->friends = array_values(Friend::getInstance()->getFriends($this->player));
		return [
			"type" => "form",
			"title" => "Friend - Master",
			"content" => "삭제할 친구를 선택해주세요.",
			"buttons" => array_map(function(string $name) : array{
				return ["text" => "§d{$name}§f님"];
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
		Friend::getInstance()->removeFriend($player, $data);
		OnixUtils::message($player, "{$this->friends[$data]}님을 친구 목록에서 제거했습니다.");
	}
}