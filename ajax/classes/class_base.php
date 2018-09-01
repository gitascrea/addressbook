<?php
/**
 * helper class for doing abstract base class
 *
 * @package     Base
 * @author      Antonio Scarfone
 * @copyright   2018 Coeln Concept 
 */

/**
 * Abstract Class Base -> for extending CRUD,App,Datasource and MVC classes
 */
abstract class Base {
	  /**
     * @var Base
     */
	  public static $_instance;
	
	  /**
     * @var $mvc -> mvc class instance
     */
	  public $mvc = null;
	
	  /**
     * constructor
     *
     */
    public function __construct() {
		    self::$_instance = $this;
	  }
	
	  /**
     * singleton getter
     *
     ** @return Base
     */
    public static function getInstance() {
		    $called_class = get_called_class();
        return self::$_instance === null ? new $called_class() : self::$_instance;
	  }

	  /**
     * Base -> check mvc instance
     *
	   * this method gets called from MVC class in method MVC::performAction()
     */
	  public function checkMVC() {
		    if ($this->mvc == null) {
			    $this->mvc = MVC::getInstance();
		    }
	  }



}
?>