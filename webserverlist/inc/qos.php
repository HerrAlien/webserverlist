<?php

// memcached based
class QOSManager
{
  /** Class that limits the number of requests handled 
   * Two states: cool - we can accept jobs, hot - do not accept jobs
   * Transition from cool to hot done only if rate goes too high
   * Transition from hot to cool is done after a cooldown time
   **/
    
    private $m_nMaxJobs;
    private $m_minTime;
    private $m_coolDownTime;
    
    private $m_lastCoolTimestamp;
    private $m_nCurrentJobsCount;
    
    private $m_lastHotTimestamp;
    private $m_ip;

    public function __construct($ip)
    {
        $this->m_ip = $ip;
        
        // allow max 20 calls per second per IP
        $this->m_nMaxJobs = 20;
        $this->m_minTime = 10;
        $this->m_coolDownTime = 2;
        
        $this->m_lastCoolTimestamp = 0;
        $this->m_nCurrentJobsCount = 0;
        $this->m_lastHotTimestamp = 0;
    }
    
    public function getCurrentJobsCount

    public function isHot()
    {
      $currentTime = microtime (true);
      if ($currentTime - $this->m_lastHotTimestamp < $this->m_coolDownTime)
        return TRUE; // still hot, stay hot until cooldown time elapsed
        
      $this->m_nCurrentJobs++;
      if ($this->m_nCurrentJobs >= $this->m_nCurrentJobsCount)
      {
        $this->m_nCurrentJobs = 0; // wrap the count
        if ($currentTime - $this->m_lastCoolTimestamp < $this->m_minTime)
            $this->m_lastHotTimestamp = $currentTime; // we're hot!
        else // we reached the max count of jobs, but it took longer than the m_minTime,
            $this->m_lastCoolTimestamp = $currentTime; // we're cool
      }
      else // we didn't reach the maximum number of calls,
            $this->m_lastCoolTimestamp = $currentTime; // we're cool
      $this->save();
      
      // return TRUE if we're cool 
      return $this->m_lastCoolTimestamp != $currentTime;
    }

  ////// TODO: refactor this and also serversDB ////////
  
  static public function getInstance($ip)
  {
      // look it up in the cache
      $cache = QOSManager::getCache();
      if (!$cache)
        return FALSE;
        
      $instance = $cache->get(QOSManager::getKey($ip));
      if (!$instance)
      {
        $instance = new QOSManager($ip);
        $instance->save();
      }
            
      return $instance;
  }

  static private function getCache()
  {
    return new Memcached('{01D27FC7-77D8-499C-9708-EF7B6EDAF6D7}');
  }
  
  static private function getKey($ip)
  {
    return "QOSManager-".$ip;
  }

  private function save()
  {
      $cache = QOSManager::getCache();
      if ($cache) // does it really make sense to keep it for more than the m_minTime?
        $cache->set(QOSManager::getKey($this->m_ip), $this, time() + $this->m_coolDownTime + 1); 
  }
    
}

$qos = QOSManager::getInstance($_SERVER['REMOTE_ADDR']);
if ($qos && $qos->isHot())
    die("hot!");
