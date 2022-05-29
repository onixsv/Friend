<?php

declare(strict_types=1);

namespace alvin0319\Friend\command;

use alvin0319\Friend\form\FriendForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class FriendCommand extends Command{

	public function __construct(){
		parent::__construct("친구", "친구 UI를 엽니다.");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$sender instanceof Player){
			return false;
		}
		$sender->sendForm(new FriendForm($sender));
		return true;
	}
}