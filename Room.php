<?php
class Room {
    private $roomId;
    private $playerArray;
    private $maxPlayer = 2;
    private $nowPlayer;
    function __construct($rmId,$max){
        $this->roomID = $rmId;
        $this->maxPlayer = $max;
        $this->nowPlayer = 0;
        $playerArray = array();
    }
    function getRoomId(){
        return $this->roomId;
    }
    function addPlayer($player){
        if ($room->nowPlayer >= $room->maxPlayer) {
            // room full of player
            return -2;
        }
        if ($room->playerArray[0] == $player) {
            // the player is already in this room
            return -3;
        }
        $this->playerArray[$this->nowPlayer] = $player;
        ++$this->nowPlayer;
        return 0;
    }
}
?>