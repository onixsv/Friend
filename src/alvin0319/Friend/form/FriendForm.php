<?php

declare(strict_types=1);

namespace alvin0319\Friend\form;

use alvin0319\Friend\Friend;
use OnixUtils\OnixUtils;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function count;
use function is_int;

class FriendForm implements Form{
	/** @var Player */
	protected Player $player;

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function jsonSerialize() : array{
		return [
			"type" => "form",
			"title" => "Friend - Master",
			"content" => "§f현재 온라인인 친구는 §d" . count(Friend::getInstance()->getOnlineFriends($this->player)) . "§f명 입니다.",
			"buttons" => [
				["text" => "§l* 나가기\nUI를 나갑니다."],
				["text" => "§l* 친구 목록 보기\n내 친구 목록을 확인합니다."],
				["text" => "§l* 친구 신청하기\n친구를 신청합니다."],
				["text" => "§l* 친구 삭제하기\n친구를 삭제합니다."],
				["text" => "§l* 친구 신청 목록\n친구 신청 목록을 확인합니다."],
				["text" => "§l* 귓속말 모드\n귓속말 모드로 전환합니다."]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		switch($data){
			case 1:
				$player->sendForm(new FriendListForm($player));
				break;
			case 2:
				$player->sendForm(new FriendRequestForm());
				break;
			case 3:
				$player->sendForm(new FriendRemoveForm($player));
				break;
			case 4:
				$player->sendForm(new FriendRequestListForm($player));
				break;
			case 5:
				if(Friend::getInstance()->isFriendChat($player)){
					Friend::getInstance()->removeFriendChat($player);
					OnixUtils::message($player, "친구 채팅을 비활성화 했습니다.");
				}else{
					$player->sendForm(new FriendChatForm($player));
				}
				break;
		}
	}
}