<?php

namespace Pina;

class Place {
	static $data = array();
	static function set($type, $s)
	{
		if (!empty(self::$data[$type])) return;
        
		self::$data[$type] = $s;
	}
	
	static function get($type)
	{
		return self::$data[$type];
	}
}