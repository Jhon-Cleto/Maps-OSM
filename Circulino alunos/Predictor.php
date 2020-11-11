<?php

class Predictor
{
    private $cur_time;
    private $cur_loc;
    private $last_wp;
    private $last_stop;
    private $cur_speed;
    private $s_units;
    private $timestamp;
    private $waypoints;
    private $busstops;
    private $route;
    
    function SetCurrentLocation($value) {
        $this->cur_loc = $value;
    }
    function GetCurrentLocation() {
        return $this->cur_loc;
    }
    function SetRoute($value) {
        $this->route = $value;
    }
    function GetRoute() {
        return $this->route;
    }
    
    public function update(){
        //TODO: receives data from circulino, new location as new_loc
        //sets current time 
        date_default_timezone_set('America/Sao_Paulo');
        $this->cur_time = date("H - i - s", time());
        
        //set speed average
        //$speed = (getDistance($this->cur_loc, $new_loc) / $this->cur_time - $this->timestamp);
        
        //set new location and updates other location data
        $this->cur_loc = $new_loc;
        $this->locationUpdate();
        
        //checks if new info is still on the last hour; if not, update hour
        if(($this->cur_time - $this->timestamp) < 3601){
            $this->hourUpdate();
        }
        
        //update current average speed and average calculation units
        $this->cur_speed = ($this->cur_speed*$this->units + $speed)/($this->units + 1);
        $this->units += 1;
        
        //set update timestamp
        $this->timestamp = $this->cur_time;
    }
    
    function hourUpdate(){
        //TODO:get new speed for this cur_time from database
        $this->s_units = 5;
    }
    
    function locationUpdate(){
        $last_wp_to_cur = $this->getDistance($this->waypoints[$this->last_wp], $this->cur_loc); 
        $wp_temp_dist = $this->getDistance($this->waypoints[$this->last_wp], $this->waypoints[($this->last_wp+1)]);
        while($last_wp_to_cur > $wp_temp_dist){
            $this->last_wp +=  1;
            $last_wp_to_cur -= $wp_temp_dist;
            $wp_temp_dist = $this->getDistance($this->waypoints[$this->last_wp], $this->waypoints[($this->last_wp+1)]);
        }
        
        $last_stop_to_cur = $this->getDistance($this->busstops[$this->last_stop], $this->cur_loc);
        $stop_temp_dist = $this->getDistance($this->busstops[$this->last_stop], $this->busstops[($this->last_stop+1)]);
        while($last_stop_to_cur > $stop_temp_dist){
            $this->last_stop += 1;
            $last_stop_to_cur -= $stop_temp_dist;
            $stop_temp_dist = $this->getDistance($this->busstops[$this->last_stop], $this->busstops[($this->last_stop+1)]);
        }
        
        
    }
    
    function getDistance($from, $to, $earthRadius = 6371000)
    {
        //CONVERTENDO GRAUS PARA RADIANOS
        $latFrom = deg2rad($from[0]);
        $lonFrom = deg2rad($from[1]);
        $latTo = deg2rad($to[0]);
        $lonTo = deg2rad($to[1]);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        return ($angle * $earthRadius);
    }

    
    /*
     * THIS IS JUST AN INSURANCE IN CASE I MESSED UP ALTERED GETDISTANCE FUNCION AND NEED TO GO BACK
     * 
    function getDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        //CONVERTENDO GRAUS PARA RADIANOS
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
  
        return ($angle * $earthRadius);
    }
    */


    
    public function readWaypoints(){
        // read each waypoint and add to $this->waypoints
        // format is such:
        // $waypoints[Id] = array(latitude, longitude, dist to next waypoint);
        // id is just a number ~index??
        $nome_arquivo = "Rotas/itinerario_rota$rota.txt";
        
        $arquivo = fopen ($nome_arquivo, 'r');
        
        $i = 0;
        
        while(!feof($arquivo))
        {
            //LENDO LINHA A LINHA DO ARQUIVO
            $linha = fgets($arquivo, 1024);
            $coordenadas = explode(',', $linha);
            $lat = $coordenadas[1];
            $lng = $coordenadas[0];
            $dist = $coordenadas[2];
            $this->waypoints[$i] = array($lat, $lng, $dist);
            $i++;
        }

    }
    
    public function readStops(){
        // same as read waypoints but with but stops as covered in the pdfs
        $nome_arquivo = "Rotas/itinerario_rota".$this->route.".txt";
        $arquivo = fopen ($nome_arquivo, 'r');       
        
        $i = 0;
        
        while(!feof($arquivo))
        {
            //LENDO LINHA A LINHA DO ARQUIVO
            $linha = fgets($arquivo, 1024);
            $coordenadas = explode(',', $linha);
            $lat = $coordenadas[1];
            $lng = $coordenadas[0];
            $dist = $coordenadas[2];
            $this->busstops[$i] = array($lat, $lng, $dist);
            $i++;
        }
    }
    
    private function ArrivalAux(){        
        $i = $this->last_stop+1;
        $d = $this->waypoints[$i];
        $o = $this->cur_loc;
        $this->cur_time = date("H - i - s", time());
        $t = $this->cur_time;
        
        while(i<count($this->waypoints)){
            $dist = $this->getDistance($o, $d);
            $time = $dist/$this->cur_speed;
            $eta = $time + $t;
            //TODO:$estimated[i] = $eta?
            $o = $d;
            $d = $this->waypoints[($i+1)];
            $t = $eta;
            $i++;
        }
        
        $i = 0;
        
        while(i<($this->last_stop+1)){
            $dist = $this->getDistance($o, $d);
            $time = $dist/$this->cur_speed;
            $eta = $time + $t;
            //TODO:$estimated[i] = $eta?
            $o = $d;
            $d = $this->waypoints[($i+1)];
            $t = $eta;
            $i++;
        }
        
        //TODO:return $estimated
        
        
        // $dist <- calculate distance to next bus stop
        // $time = $dist/$this->cur_speed calculate estimated time to next bus stop speed = dist/time-- speed/dist = 1/time -- dist/speed = time
        // $eta estimated time of arrival <- time to next bus stop + current time
        // FOR EACH FOLLOWING BUS STATION
        // get next bus station
        // calculate cruise time between stations at cur_speed
        // estimated time of arrival <- cruise time + estimated time of arrival for previous bus station
    }
    
    public function ArrivalTime($busstop){
        
        $estimated = $this->ArrivalAux();
        $time = array_search($busstop, $estimated);
        $this->cur_time = date("H - i - s", time());
        return($time - $this->cur_time);
        
        //calls arrival aux
        //gets time for specified bus stop
        //ETA - current time = time remaining 
        //return time remaining
    }
    
    public function BusLocation(){
        //TODO: THIS METHOD
        // get the time difference between current time and last calculated timestamp
        // cur_speed x time_dif  = dist_traveled
        // measure distance between cur_loc and next waypoint
        // 
    }
    
    
}

