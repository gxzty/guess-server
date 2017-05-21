<?php 
include_once 'Room.php';
class RoomManager {
    private static $m_instance;
    private $roomArray;
    private $roomCount;
    static function getInstance(){
        if (!$m_instance) {
            $m_instance = new RoomManager();
        }
        return $m_instance;
    }
    function __construct(){
        $this->roomArray = array();
        $this->roomCount = 0;
    }
    function createRoom(){
        do {
            $roomId = rand(111111,999999);
        } while(!$this->roomArray[$roomId]);
        $room = new Room($roomId,2);
        $this->roomArray[$roomId] = $room;
        ++$this->roomCount;
        return $room;
    }
    function isRoom($roomId){
        return $this->roomArray[$roomId] ? true : false;
    }
    function getRoomById($roomId){
        return $this->roomArray[$roomId];
    }
    function closeRoom($roomId){
        $this->roomArray[$roomId] = false;
        --$this->roomCount;
    }
    function joinRoom($roomId,$player){
        $room = getRoomById($roomId);
        if (!$room) {
            // no room like this
            return -1;
        }
        return $room->addPlayer($player);
    }
}
?>