<?php

namespace generalmc;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\ActorFallPacket;
use pocketmine\network\mcpe\protocol\ActorPickRequestPacket;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\BlockPickRequestPacket;
use pocketmine\network\mcpe\protocol\BookEditPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\ClientToServerHandshakePacket;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\network\mcpe\protocol\CommandRequestPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\CraftingEventPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacketV1;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerHotbarPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkRequestPacket;
use pocketmine\network\mcpe\protocol\ResourcePackClientResponsePacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\network\mcpe\protocol\SetLocalPlayerAsInitializedPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\ShowCreditsPacket;
use pocketmine\network\mcpe\protocol\SpawnExperienceOrbPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\timings\Timings;
use function base64_encode;
use function bin2hex;
use function implode;
use function json_decode;
use function json_last_error_msg;
use function preg_match;
use function preg_split;
use function strlen;
use function substr;
use function trim;

class Main extends PluginBase implements Listener {
	
	public function onEnable(){
	    $this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->GoldenEye = $this->getServer()->getPluginManager()->getPlugin("GoldenEye");
		if($this->GoldenEye === null){
			$this->getlogger()->notice("GoldenEye is not loaded.");
			$this->getlogger()->notice("Server will shutdown.");
			$this->getServer()->shutdown();
		}
	}
	
		public function handleDataPacket(DataPacket $packet){
		if(!$this->player->isConnected()){
			return;
		}
		$timings = Timings::getReceiveDataPacketTimings($packet);
		$timings->startTiming();
		$packet->decode();
		if(!$packet->feof() and !$packet->mayHaveUnreadBytes()){
			$remains = substr($packet->buffer, $packet->offset);
			$this->server->getLogger()->debug("Still " . strlen($remains) . " bytes unread in " . $packet->getName() . ": 0x" . bin2hex($remains));
		}
		$ev = new DataPacketReceiveEvent($this->player, $packet);
		$ev->call();
		if(!$ev->isCancelled() and !$packet->handle($this)){
			$this->server->getLogger()->debug("Unhandled " . $packet->getName() . " received from " . $this->player->getName() . ": " . base64_encode($packet->buffer));
		}
		$timings->stopTiming();
	}
	
	public function handleLogin(LoginPacket $packet) : bool{
		return $this->player->handleLogin($packet);
	}
	
	public function handleClientToServerHandshake(ClientToServerHandshakePacket $packet) : bool{
		return false; //TODO
	}
	
	public function handleResourcePackClientResponse(ResourcePackClientResponsePacket $packet) : bool{
		return $this->player->handleResourcePackClientResponse($packet);
	}
	
	public function handlePlayerAction(PlayerActionPacket $packet) : bool{
		return $this->player->handlePlayerAction($packet);
	}
}
