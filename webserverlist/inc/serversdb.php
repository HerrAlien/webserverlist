<?php

// memcached based
class serversDB
{
  private $m_timestampList;
  private $m_heartbeatTimeout;
  private $m_game;
  private $m_nMaxServers;
  private $m_nSleepBetweenRequests;
  private $m_nMaxServersPerIP;
  
  private function __construct()
  {
    $this->m_timestampList = array();
    // config time ...
    $this->m_heartbeatTimeout = 1800; // in seconds, 30 minutes
    $this->m_nMaxServers = 256;
    $this->m_nSleepBetweenRequests = 1;
    $this->m_nMaxServersPerIP = 8;
  }
  
  public function getHeartbeatTimeout()
  {
    return $this->m_heartbeatTimeout;
  }
  
  public function tickServer($ip, $port)
  {
    $key = $ip.":".$port;
    $this->m_timestampList[$key] = date("U");
    $this->cleanDeadServers();
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
      
      sleep ($this->m_nSleepBetweenRequests);
      
      $deadTime = $currentTime - $this->m_heartbeatTimeout;
      $keys = array_keys($this->m_timestampList);
      foreach ($keys as $key)
      {
          if($deadTime >= $this->m_timestampList[$key])
            unset ($this->m_timestampList[$key]);
      }
      
      $len = count ($this->m_timestampList);
      
      if ($len >= $this->m_nMaxServers)
        $this->cleanFakeServers();
      
      $this->save();
  }
  
  private function cleanFakeServers()
  {
    /* 
        A server is fake if
        - the IP has more than 8 servers listed for it
        - the timestamps between individual servers are close, under 5 seconds
    */
    
    $counts = array();
    foreach (array_keys($this->m_timestampList) as $key)
    {
        $ip = serversDB::getIPFromServerKey($key);
        if (!isset($counts[$ip]))
            $counts[$ip] = 1;
        else
            $counts[$ip]++
    }
    
    // remove legit IPs, leave only the offenders
    $keys = array_keys ($counts);
    $len = count ($counts);
    for ($i = $len - 1; $i >= 0; $i--)
    {
        if ($this->m_nMaxServersPerIP >= $counts[$keys[$i]])
            unset ($counts[$keys[$i]]);
    }
    
    $keys = array_keys($this->m_timestampList);
    $offenders = array_keys ($counts);
    foreach ($keys as $key)
    {
        $ip = serversDB::getIPFromServerKey ($key);
        if (in_array($ip, $offenders))
          unset ($this->m_timestampList[$key]);
    }
  }
  
  private static function getIPFromServerKey($key)
  {
    $ip = $key;
    // key is in form ip1.ip2.ip3.ip4:port
    $colonPos = strpos($key, ":");
    if ($colonPos > 0)
        $ip = substr ($key, 0, $colonPos);
    return $ip;
  } 
  
  private static function getCache($game)
  {
      return new Memcached ($game."-webseverlist-4F7ECB99-9216-40BA-BD3F-6879742D92C0");
  }
  
  private static function getServerDBKey()
  {
      return "serversDB";
  }
  
  public static function getInstance($game)
  {
    $cache = serversDB::getCache($game);
    $instance = $cache->get(serversDB::getServerDBKey());
    if ($instance === FALSE)
    {
        $instance = new serversDB();
        $instance->m_game = $game;
        $saved = $cache->set(serversDB::getServerDBKey(), $instance);
    }
    return $instance;
  }
  
  private function save()
  {
    $cache = serversDB::getCache($this->m_game);
    $cache->set(serversDB::getServerDBKey(), $this);
  }
  
}
