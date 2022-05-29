<?php

declare(strict_types=1);

namespace alvin0319\Friend\form;

use alvin0319\Friend\Friend;
use OnixUtils\OnixUtils;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function is_bool;

class FriendAcceptConfirmForm implements Form{

	protected Player $player;

	protected string $friend;

	protected int $index;

	public function __construct(Player $player, string $friend, int $index){
		$this->player = $player;
		$this->friend = $friend;
		$this->index = $index;
	}

	public function jsonSerialize() : array{
		return [
			"type" => "modal",
			"title" => "Friend - Master",
			"content" => "정말 " . $this->friend . "님의 친구 신청을 수락하시겠습니까?",
			"button1" => "네",
			"button2" => "아니요"
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_bool($data)){
			return;
		}
		Friend::getInstance()->removeQueue($player, $this->index);
		if($data){
			Friend::getInstance()->addFriend($player, $this->friend);
			OnixUtils::message($player, "§d{$this->friend}§f님의 친구 신청을 §a수락§f했습니다.");
		}else{
			OnixUtils::message($player, "§d{$this->friend}§f님의 친구 신청을 §c거절§f했습니다.");
		}
	}
}