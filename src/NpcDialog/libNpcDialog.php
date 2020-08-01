<?php

namespace NpcDialog;

use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\NpcRequestPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class libNpcDialog extends PluginBase implements Listener{
    /** @var mixed[] */
    private $responsePool = [];

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvent(DataPacketReceiveEvent::class, function(DataPacketReceiveEvent $event) : void{
            $player = $event->getOrigin()->getPlayer();
            if($player === null)
                return;

            $packet = $event->getPacket();
            if(!$packet instanceof NpcRequestPacket)
                return;

            $target = $player->getWorld()->getEntity($packet->entityRuntimeId);
            if($target === null)
                return;

            $username = $player->getName();
            $logger = Server::getInstance()->getLogger();

            switch($packet->requestType){
                case NpcRequestPacket::REQUEST_EXECUTE_ACTION:
                    $this->responsePool[$username] = $packet->actionType;
                    break;
                case NpcRequestPacket::REQUEST_EXECUTE_CLOSING_COMMANDS:
                    $form = DialogFormStore::getFormByEntity($target);
                    if($form !== null){
                        $form->handleResponse($player, $this->responsePool[$username] ?? null);
                        unset($this->responsePool[$username]);
                    }else{
                        $logger->warning("Unhandled NpcRequestPacket for $username because there wasn't a registered form on the store");
                    }
                    break;
            }
        }, EventPriority::NORMAL, $this);
    }
}{}
