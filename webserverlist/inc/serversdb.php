<?php

class serversDB
{
    private function open($mode)
    {
        return fopen("db/database.txt", $mode);
    }
    
    private function openRead()
    {
        return $this->open("r");
    }

    private function openWrite()
    {
        return $this->open("w");
    }
}
