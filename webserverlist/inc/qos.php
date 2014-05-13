<?php

// memcached based
class QOSManager
{
  /** Class that limits the number of requests handled for one client IP. 
   * Two states: cool - we can accept jobs, hot - do not accept jobs
   * Transition from cool to hot done only if rate goes too high.
   * Transition from hot to cool is done after a cooldown time.
   * An instance of the class is created for all each incomming IP address.
   * The lifetime of the object is short - the same as the cooldown time.      
   **/
    
    private $m_nMaxJobs;
    private $m_minTime;
    private $m_coolDownTime;
    
    private $m_lastCoolTimestamp;
    private $m_nCurrentJobsCount;
    
    private $m_lastHotTimestamp;
    private $m_ip;

    private function __construct($ip)
    {
        $this->m_ip = $ip;
        
        // allow max 20 calls per second per IP
        $this->m_nMaxJobs = 20;
        $this->m_minTime = 1;
        $this->m_coolDownTime = 5;
        
        $this->m_lastCoolTimestamp = 0;
        $this->m_nCurrentJobsCount = 0;
        $this->m_lastHotTimestamp = 0;
    }
    
    /** Method that updates and returns the cool-hot status of the object.
     * @return TRUE if the object is hot, FALSE if the object is cool. */
    public function isHot()
    {
      $currentTime = microtime (true);
      // if we're too close to the last hot timestamp, then we're still hot.
      if ($currentTime - $this->m_lastHotTimestamp < $this->m_coolDownTime)
        return TRUE; // still hot, stay hot until cooldown time elapsed
      
      // state unknown. Increment the number of jobs handled  
      $this->m_nCurrentJobsCount++;
      // and if we ran more jobs than the max limit, 
      if ($this->m_nCurrentJobsCount >= $this->m_nMaxJobs)
      {
        // we wrap the count to 0, and determine status just by how much time has passed.
        $this->m_nCurrentJobsCount = 0;
        if ($currentTime - $this->m_lastCoolTimestamp < $this->m_minTime)
            $this->m_lastHotTimestamp = $currentTime; // we're hot!
        else // we reached the max count of jobs, but it took longer than the m_minTime,
            $this->m_lastCoolTimestamp = $currentTime; // we're cool
      }
      else // we didn't reach the maximum number of calls,
            $this->m_lastCoolTimestamp = $currentTime; // we're cool
      $this->save();
      
      // return TRUE if we're hot 
      return $this->m_lastHotTimestamp == $currentTime;
    }

  ////// TODO: refactor this and also serversDB ////////
  /** Factory method. It searches for the object corresponding to the input
   * IP address. If not found, creates one.
   * @param $ip the IP address that is being monitored
   * @return the instance that monitores the given IP, or FALSE in case of low memory */
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
/* this can be placed in all pages that need monitoring, but then again, it's easier
to write this once, and just include the file. */
$qos = QOSManager::getInstance($_SERVER['REMOTE_ADDR']);
if ($qos && $qos->isHot())
    die();
