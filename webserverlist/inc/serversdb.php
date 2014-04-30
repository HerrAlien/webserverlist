<?php

// memcached based
class serversDB
{
  private $m_timestampList;
  private $m_heartbeatTimeout;
  private $m_game;
  
  private function __construct()
  {
    $this->m_timestampList = array();
    $this->m_heartbeatTimeout = 1800; // in seconds, 30 minutes
  }
  
  public function tickServer($ip, $port)
  {
    $key = $ip." ".$port;
    $this->m_timestampList[$key] = date("U");
    echo "saving ".$key." ...<br>";

    echo count($this->m_timestampList)." servers <br>";
    
    $this->cleanDeadServers();
    echo count($this->m_timestampList)." servers after clean <br>";
  }
  
  // unset to delete
  public function getServers()
  {
    $this->cleanDeadServers();
    return array_keys($this->m_timestampList);
  }
  
  private function cleanDeadServers()
  {
      $currentTime = date("U");
      $deadTime = $currentTime - $this->m_heartbeatTimeout;
      $len = count ($this->m_timestampList);
      for ($i = $len - 1; $i >=0; $i--)
      {
          if($deadTime >= $this->m_timestampList[$i])
            unset ($this->m_timestampList[$i]);
      }
      $this->save();
  }
  
  public static function getInstance($game)
  {
    $cache = new Memcached ($game."-webseverlist-4F7ECB99-9216-40BA-BD3F-6879742D92C0");
    $instance = $cache->get("serversDB");
    if ($instance === FALSE)
    {
        echo 'did not find "serversDB", creating one<br>';
      $instance = new serversDB();
        $instance->m_game = $game;
      $saved = $cache->set("serversDB", $instance);
        echo 'saved='.$saved.'<br>';
    }
    return $instance;
  }
  
  private function save()
  {
    $cache = new Memcached ($this->m_game."-webseverlist-4F7ECB99-9216-40BA-BD3F-6879742D92C0");
    $cache->set("serversDB", $this);
  }
  
}
