<?php
class DataFormatterBehavior extends ModelBehavior {
	
	public function afterFind($model, $data, $primary = false){
		if (is_array($data)) {
            if($primary) $this->utf8Change($data);
            $data = $this->_changeDate($data, 2); 
        }
        return $data;
	}
	
	function beforeSave($model) {
    $model->data = $this->_changeDate($model->data, 1);
    return true;
  }

	
	public function utf8_encode(&$data, $nothing = null){
		$data = utf8_encode($data);
	}
	
	public function utf8Change(&$something, $nothing=NULL, $operation='utf8_encode') {
		$operation = (in_array($operation, array('utf8_encode', 'utf8_decode'))) ? $operation : 'utf8_encode';
		if (is_array($something)) {
		  array_walk_recursive($something, array($this, 'utf8Change'), $operation);
		} elseif (is_object($something)) {
		  foreach ($something as $propName => $propValue) {
				$something->$propName = $this->utf8Change($propValue);
		  }
		} else {
		  if ($operation == 'utf8_encode' && !$this->is_utf8($something)) {
				$something = $operation($something);
		  }
		}
		return $something;
	}
	
	public function is_utf8($str)  {
		$c=0; $b=0;
		$bits=0;
		$len=strlen($str);
		for($i=0; $i<$len; $i++){
			$c=ord($str[$i]);
			if($c > 128){
				switch(true){
					case ($c >= 254): return false; break;
					case ($c >= 252): $bits=6; break;
					case ($c >= 248): $bits=5; break;
					case ($c >= 240): $bits=4; break;
					case ($c >= 224): $bits=3; break;
					case ($c >= 192): $bits=2; break;
					default: return false; break;
				}
				if(($i+$bits) > $len) return false;
					while($bits > 1){
						$i++;
						$b=ord($str[$i]);
						if($b < 128 || $b > 191) return false;
						$bits--;
				}
			}
		}
		return true;
	}
	
	   
    /**
     * Class Vars
     * All these variables can be set from Configure class
     */
    //Data format for humans
    var $dateFormat = 'dd/mm/yyyy';
    //Dataformat for database
    var $databaseFormat = 'yyyy-mm-dd';
    //delimeted for humans
    var $delimiterDateFormat = '/';
    //delimiter for database
    var $delimiterDatabaseFormat = '-';
    /**
     * Empty Setup Function
    */
    function setup(&$model) {
        //Getting user defined vars
        $dateFormat = Configure::read('DateBehaviour.dateFormat');
        if($dateFormat != null){
            $this->dateFormat = $dateFormat;
        }
        $databaseFormat = Configure::read('DateBehaviour.databaseFormat');
        if($databaseFormat != null){
            $this->databaseFormat = $databaseFormat;
        }
        $delimiterDateFormat = Configure::read('DateBehaviour.delimiterDateFormat');
        if($delimiterDateFormat != null){
            $this->delimiterDateFormat = $delimiterDateFormat;
        }
        $delimiterDatabaseFormat = Configure::read('DateBehaviour.delimiterDatabaseFormat');
        if($delimiterDatabaseFormat != null){
            $this->delimiterDatabaseFormat = $delimiterDatabaseFormat;
        }
        $this->model = $model;
    }
   
    function _convertDateTime($date, $format1, $format2, $delimiterDateFormat, $delimiterDatabaseFormat, $direction){
    	if($date == null OR $date == ''){
            return '';
        }
        $date_array = explode(' ', $date);
      if (!isset($date_array[1])) $date_array[1] = '';
		return $this->_convertDate($date_array[0], $format1, $format2, $delimiterDateFormat, $delimiterDatabaseFormat, $direction).' '.$date_array[1];
    }
   
    /**
     * Function which convert one date from format1 to format2
     * basically this function play with those three elements of the date - dd, mm, yyyy
     * with delimiter you define which one of the elements is where
     *
     * @param string $date date string formated with format1
     * @param string $format1 format in which is formatted the $date variable by if it's comming from database is yyyy-mm-dd
     * @param string $format2 new format for the date.
     * @param char $delimiter separater between different elements of the date string /i.e. dash (-), dot(.), space ( ), etc/
     * @return string date formated with $format2
     * @access restricted
     */
    function _convertDate($date, $format1, $format2, $delimiterDateFormat, $delimiterDatabaseFormat, $direction){
        if($date == null OR $date == ''){
            return '';
        }
        //robadinha pra funcionar com data gerada automaticamente (campos created e modified)
        if(!strpos($date, '/') && strpos($format1, '/')){
            return $date;
        }
        
        //roubadinha para funcionar o formato mes/ano
        if(strlen($date) == 7) {
            $format1 = substr($format1, 0, 7);
            $format2 = substr($format2, 3, strlen($format2));
        }
        
        $date_array = explode($delimiterDateFormat, $date);
        $format1_array = explode($delimiterDateFormat, $format1);
        $format2_array = explode($delimiterDatabaseFormat, $format2);
        //
        $current_array = array_combine($format1_array, $date_array);
        $new_array = array_combine($format2_array, $date_array);
        foreach($new_array as $key=>$value){
            $new_array[$key] = $current_array[$key];
        }

        return implode($delimiterDatabaseFormat, $new_array);
    }
   
    /**
     *Function which handle the convertion of the data arrays from database to user defined format and up side down
     * @param array $data data array from and to database
     * @param int $direction with 2 possible values '1' determine that data is going to database, '2' determine that data is pulled from database
     * @return array converted array;
     * @access restricted
     */
    function _changeDate($data, $direction, $recursive = false){
        //just return false if the data var is false
        if($data == false){
            return false;
        }
        //Detecting the direction
        switch($direction){
            case 1:
                $format1 = $this->dateFormat;
                $format2 = $this->databaseFormat;
                $delimiterDateFormat = $this->delimiterDateFormat;
                $delimiterDatabaseFormat = $this->delimiterDatabaseFormat;
                break;
            case 2:
                $format1 = $this->databaseFormat;
                $format2 = $this->dateFormat;
                $delimiterDateFormat = $this->delimiterDatabaseFormat;
                $delimiterDatabaseFormat = $this->delimiterDateFormat;
                break;
            default:
                return false;
        }
		//result model
        foreach($data as $key=>$value){
            if($direction == 2){
            	$columns = array();
                foreach($value as $key1=>$value1){
                    if($this->model->name == $key1){ //if it's current model;
                    	$validate_schema = $this->model->schema();
                    	if(!empty($validate_schema))
                    		$columns = $this->model->getColumnTypes();
                    } else {
                        //Fix for loading models on the fly
                        if(isset($this->model->{$key1})){
                        	$validate_schema = $this->model->{$key1}->schema();
                            if(!empty($validate_schema))
                    			$columns = $this->model->{$key1}->getColumnTypes();
                        } else {
                            if($key1 != 'Parent'){
                                if(App::import('Model', $key1)){
                                	$model_on_the_fly = new $key1();
                                	$validate_schema = $model_on_the_fly->schema();
                                	if(!empty($validate_schema))
                    					$columns = $model_on_the_fly->getColumnTypes();
                                }
                            }
                        }
                    }
                    foreach($value1 as $k=>$val){   
                        if(!is_array($val)){
                            if(in_array($k, array_keys($columns))){
                                if($columns[$k] == 'date'){
                                    if($val == '0000-00-00' || $val == ''){ //also clear the empty 0000-00-00 values
                                        $data[$key][$key1][$k] = null;
                                    } else {
                                        $data[$key][$key1][$k] = $this->_convertDate($val, $format1, $format2, $delimiterDateFormat, $delimiterDatabaseFormat, $direction);
                                    }
                                } else if($columns[$k] == 'datetime'){
                                	if($val == '0000-00-00 00:00:00' || $val == ''){ //also clear the empty 0000-00-00 values
                                        $data[$key][$key1][$k] = null;
                                    } else {
                                        $data[$key][$key1][$k] = $this->_convertDateTime($val, $format1, $format2, $delimiterDateFormat, $delimiterDatabaseFormat, $direction);
                                    }
                                } 
                            }
                        }
                    }   
                }
            } else {
            	$columns = array();
                if($this->model->name == $key){ //if it's current model;
                	$validate_schema = $this->model->schema();
                	if(!empty($validate_schema))
                    	$columns = $this->model->getColumnTypes();
                } else {
                    //Fix for loading models on the fly
                    if(isset($this->model->{$key})){
                    	$validate_schema = $this->model->{$key}->schema();
                    	if(!empty($validate_schema))
                        	$columns = $this->model->{$key}->getColumnTypes();
                    } else {
                        if($key != 'Parent'){
                            if(App::import('Model', $key)){
                            	$model_on_the_fly = new $key();
                            	$validate_schema = $model_on_the_fly->schema();
                            	if(!empty($validate_schema))
                            		$columns = $model_on_the_fly->getColumnTypes();
                            }
                        }
                    }
                }
                foreach($value as $k=>$val){   
                    if(!is_array($val)){
                        if(in_array($k, array_keys($columns))){
                            if($columns[$k] == 'date'){
                                if($val == '0000-00-00' || $val == ''){ //also clear the empty 0000-00-00 values
                                    $data[$key][$k] = null;
                                } else {
                                    $data[$key][$k] = $this->_convertDate($val, $format1, $format2, $delimiterDateFormat, $delimiterDatabaseFormat, $direction);
                                }
                            } else if($columns[$k] == 'datetime'){
                                if($val == '0000-00-00 00:00:00' || $val == ''){ //also clear the empty 0000-00-00 values
                                    $data[$key][$k] = null;
                                } else {
                                    $data[$key][$k] = $this->_convertDateTime($val, $format1, $format2, $delimiterDateFormat, $delimiterDatabaseFormat, $direction);
                                }
                            } else if($columns[$k] == 'float' || $columns[$k] == 'money' ){
                                if (strpos($val, '.')>0 && strpos($val, ',')>0) {
                                    $val = str_replace('.', '', $val);
                                }
                               	$data[$key][$k] = str_replace(',', '.', $val);
                            }
                        }
                    }
                }
            }
        }
        //tentando corrigir data de subitens que s�o array...
        /*if(isset($data[0]['Cliente'])){
	        foreach($data as $k1=>$ap){
	        	foreach($ap as $model_key=>$model){
	        		//melhorar para n�o funcionar apenas quando for Cliente->ObservacoesCliente
	        		if(!$recursive && $model_key === 'ObservacoesCliente'){
	        			$novas = array();
	        			foreach($model as $k2=>$m2){
	        				$formatado = $this->_changeDate(array('0'=>array($model_key=>$m2)), $direction, true);
	        				$novas[] = $formatado['0'][$model_key];
	        			}
	        			$data[$k1][$model_key] = $novas;
	        		}
	        	}
	        }
        }*/
        return $data;
    }
}
?>