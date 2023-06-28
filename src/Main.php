<?php
declare(strict_types=1);

namespace DavyCraft648\DimensionConverter;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\world\format\io\leveldb\LevelDB;
use function array_shift;
use function count;
use function strtolower;

class Main extends \pocketmine\plugin\PluginBase{

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(count($args) !== 4){
			throw new InvalidCommandSyntaxException();
		}

		$oldWorldName = array_shift($args);
		$this->getServer()->getWorldManager()->loadWorld($oldWorldName);
		$oldWorld = $this->getServer()->getWorldManager()->getWorldByName($oldWorldName);
		if($oldWorld === null){
			$sender->sendMessage("Old world not found");
			return true;
		}
		$oldLevelDB = $oldWorld->getProvider();
		/** @noinspection PhpConditionAlreadyCheckedInspection */
		if(!($oldLevelDB instanceof LevelDB)){
			$sender->sendMessage("Unsupported world $oldWorldName");
			return true;
		}

		$newWorldName = array_shift($args);
		$this->getServer()->getWorldManager()->loadWorld($newWorldName);
		$newWorld = $this->getServer()->getWorldManager()->getWorldByName($newWorldName);
		if($newWorld === null){
			$sender->sendMessage("New world not found");
			return true;
		}
		$newLevelDB = $newWorld->getProvider();
		/** @noinspection PhpConditionAlreadyCheckedInspection */
		if(!($newLevelDB instanceof LevelDB)){
			$sender->sendMessage("Unsupported world $newWorldName");
			return true;
		}

		$from = match(strtolower(array_shift($args))){
			"overworld" => DimensionIds::OVERWORLD,
			"nether" => DimensionIds::NETHER,
			"the_end" => DimensionIds::THE_END
		};
		$to = match(strtolower(array_shift($args))){
			"overworld" => DimensionIds::OVERWORLD,
			"nether" => DimensionIds::NETHER,
			"the_end" => DimensionIds::THE_END
		};
		if($from === $to){
			$sender->sendMessage('$from === $to');
			return true;
		}

		$sender->sendMessage("Converting...");
		$converter = new Converter($this->getLogger(), $oldLevelDB);
		$converter->execute($from, $to, $newLevelDB);
		$sender->sendMessage("Conversion completed");
		return true;
	}
}
