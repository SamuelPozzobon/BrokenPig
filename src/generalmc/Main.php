<?php

namespace generalmc;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

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