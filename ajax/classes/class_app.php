<?php
require_once __DIR__.'/class_mvc.php';
require_once __DIR__.'/class_base.php';
/**
 * helper class for doing App
 *
 * @package     App
 * @author      Antonio Scarfone
 * @copyright   2018 Coeln Concept 
 */

/**
 * Class App -> Create App
 */
class App extends Base {
	/**
     * constructor
     *
     */
    public function __construct($action = "") {
		parent::__construct();
		$is_main_controller = true;
		$this->mvc = new MVC($action, $is_main_controller, $this);
	}
	
	/**
     * App -> index_model get model data logic for index page controller
     *
	 * called from index.php renders index.twig -> MVC methods in class MVC
	 * this method get the model(sql) and can implement some logic like for example filtering, pagination ...
	 * Convention to declare such a method is {name of php file}_model_logic -> for example index_model_logic (index.php) or contact_model.logic (contact.php)
	 * this methods are called from MVC class in method MVC::modelDataLogic()
     ** @return model index data logic
     */
	public function index() {
		//model source must be here a sql select
		$model = "SELECT '".$this->mvc::$clientRendering."' as 'clienRendering', 
						 'readRecords' as 'action', 
						 '".($this->mvc::$clientRendering ? $this->mvc::$template : '')."' as 'template', 
						 '".$this->mvc::$templateRead."' as 'templateRead', 
						 '".$this->mvc::$templateNew."' as 'templateNew', 
						 '".$this->mvc::$templateEngine."' as 'templateEngine', 
						 '".$this->mvc::$templateRef."' as 'templateRef', 
						 '".$this->mvc::$templatePath."' as 'templatePath', 
						 '".$this->mvc::$controller."' as 'controller';";
					

		return $model;
	}

	public function contact() {
		//model source must be here a sql select
		$template_name = "readContact_".$this->mvc::$templateEngine.".".$this->mvc::$templateExt;
		$model = "SELECT '".$this->mvc::$clientRendering."' as 'clienRendering', 
						 'readContact' as 'action', 
						 '".($this->mvc::$clientRendering ? $template_name : '')."' as 'template', 
						 '' as 'templateRead', 
						 '' as 'templateNew', 
						 '".$this->mvc::$templateEngine."' as 'templateEngine', 
						 'readContact' as 'templateRef', 
						 '".$this->mvc::$templatePath."' as 'templatePath', 
						 '".$this->mvc::$controller."' as 'controller';";
					

		return $model;
	}
	
	

}
?>