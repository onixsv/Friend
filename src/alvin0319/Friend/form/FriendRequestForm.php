<?php

declare(strict_types=1);

namespace alvin0319\Friend\form;

use alvin0319\Friend\Friend;
use OnixUtils\OnixUtils;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function trim;

class FriendRequestForm implements Form{

	public function jsonSerialize() : array{
		return [
			"type" => "custom_form",
			"title" => "Friend - Master",
			"content" => [
				[
					"type" => "input",
					"text" => "신청할 친구의 닉네임을 적어주세요.",
					"placeholder" => "ex) alvin0319"
				]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		$friend = $data[0] ?? "";
		if(trim($friend) === ""){
			return;
		}
		if(!Friend::getInstance()->hasData($friend)){
			OnixUtils::message($player, "해당 플레이어는 이 서버에 접속한 적이 없습니다.");
			return;
		}
		if(Friend::getInstance()->isFriend($player, $friend)){
			OnixUtils::message($player, "해당 플레이어와 이미 친구입니다.");
			return;
		}
		if(Friend::getInstance()->isQueue($friend, $player)){
			OnixUtils::message($player, "이미 해당 플레이어에게 친구 신청을 했습니다.");
			return;
		}
		Friend::getInstance()->addQueue($friend, $player);
		OnixUtils::message($player, "§d{$friend}§f님께 친구 요청을 보냈습니다.");
	}
}