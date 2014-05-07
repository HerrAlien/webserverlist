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
    

    public function isHot()
    {
      $currentTime = microtime (true);
      if ($currentTime - $this->m_lastHotTimestamp < $this->m_coolDownTime)
        return FALSE; // still hot, stay hot until cooldown time elapsed
        
      $this->m_nCurrentJobs++;
      if ($this->m_nCurrentJobs >= $this->m_nCurrentJobsCount)
      {
        $this->m_nCurrentJobs = 0; // wrap the count
        if ($currentTime - $this->m_lastCoolTimestamp < $this->m_minTime)
            $this->m_lastHotTimestamp = $currentTime; // we're hot!
        else
            $this->m_lastCoolTimestamp = $currentTime; // we're cool
      }
      $this->save();
      
      // return TRUE if we're cool 
      return $this->m_lastHotTimestamp == $currentTime;
    }

  ////// TODO: refactor this and also serversDB ////////
  
  static public function getInstance()
  {
      // look it up in the cache
      $cache = QOSManager::getCache();
      if (!$cache)
        return FALSE;
        
      $instance = $cache->get(QOSManager::getKey());
      if (!$instance)
      {
        $instance = new QOSManager();
        $cache->set(QOSManager::getKey(), $instance);
      }
            
      return $instance;
  }

  static private function getCache()
  {
    return new Memcached('{01D27FC7-77D8-499C-9708-EF7B6EDAF6D7}');
  }
  
  static private function getKey()
  {
    return "QOSManager";
  }

  private function save()
  {
      $cache = QOSManager::getCache();
      if ($cache)
        $cache->set(QOSManager::getKey(), $this);
  }
    
}

$qos = QOSManager::getInstance();
if($qos && $qos->isHot())
  die();
