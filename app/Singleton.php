<?php

namespace App;


trait Singleton
{
	protected static $instance;
	
	/**
	 * Allow only one instance of this class exist
	 * for a single request
	*/
	public static function instance()
	{
		$called = get_called_class();

		if(self::$instance instanceof self)
		{
			return self::$instance;
		}
		return self::$instance = new self;
	}


}

/**
 * Coded tunez by dammyammy
 */
// abstract class Singleton{


	// private static $instances;

 //    final public static function instance() {
 //        $called = get_called_class();

 //        if(isset(self::$instances[$called]) == false) {
 //            self::$instances[$called] = new static();
 //        }
 //        return self::$instances[$called];
 //    }
// }