<?php
require_once __DIR__.'/class_datasource.php';
require_once __DIR__.'/twig/vendor/autoload.php';
require_once __DIR__.'/class_base.php';
/**
 * helper class for doing MVC/MVVM software-architecture
 *
 * @package     MVC
 * @author      Antonio Scarfone
 * @copyright   2018 Coeln Concept 
 */

/**
 * Class MVC -> Model/View/Controller or MVVM -> Model/View/ViewModel -> switch is class property $clientRendering
 */
class MVC extends Base {
	/**
     * @var Database Connection PDO
     */
	public static $db = null;

	/**
     * @var bool
	 * false = MVC-Pattern, true = MVVM-Pattern
     */
	public static $clientRendering = true;

	/**
     * @var $twig
     */
	public  $twig = null;

	/**
     * @var $loader
     */
	public  $loader = null;

	/**
     * @var $templateEngine -> possible values: twig, underscore, nunjucks
     */
	public static $templateEngine = "twig";

	/**
     * @var $templateExt -> extension of template
     */
	public static $templateExt = "twig";

	/**
     * @var $callingClassInstance -> whick class has initialized this MVC class
     */
	public $callingClassInstance = null;

	/**
     * @var $callingClass -> name of calling class
     */
	public $callingClass = "";

	/**
     * @var template to render for client side
     */
	public static $template = "readRecords_{templateEngine}.twig";
	//public static $template = "{action}_{templateEngine}.twig";

	/**
     * @var templateRead to render for client side
     */
	public static $templateRead = "readRecord_{templateEngine}.twig";

	/**
     * @var templateNew to render for client side
     */
	public static $templateNew = "newRecord_{templateEngine}.twig";
	
	/**
     * @var $templateRef -> ref id of template script tag
     */
	public static $templateRef = "readRecords";

	/**
     * @var $templatePath -> path to templates
     */
	public static $templatePath = "ajax/classes/templates/";

	/**
     * @var $controller -> ajax controller script
     */
	public static $controller = "ajax/controller.php?action=";

	/**
     * @var $noCrudActions -> defines methods for non crud actions
     */
	public static $noCrudActions = array();


	
	/**
     * constructor
     *
     */
    public function __construct($action = "", $is_main_controller = true, $callingClassInstance = null) {
		parent::__construct();
		self::$db = Datasource::getConnection();
		self::$template = str_replace("{templateEngine}", self::$templateEngine, self::$template);
		self::$templateRead = str_replace("{templateEngine}", self::$templateEngine, self::$templateRead);
		self::$templateNew = str_replace("{templateEngine}", self::$templateEngine, self::$templateNew);
		//self::$template = str_replace("{action}", $action, str_replace("{templateEngine}", self::$templateEngine, self::$template));
		//$templateRef = $action;
		if (!self::$clientRendering) self::$templateEngine = "twig";
		
		$this->loader = new Twig_Loader_Filesystem(__DIR__.'/templates');
		$this->twig = new Twig_Environment($this->loader, array(
			// Uncomment the line below to cache compiled templates
			//'cache' => __DIR__.'/cache',
			//'debug' => true
		));
		//$this->twig->addExtension(new Twig_Extension_Debug());
		
		$this->callingClassInstance = $callingClassInstance;
		$this->callingClass = get_class($callingClassInstance);
		$this->performAction($action, $is_main_controller);
	}

	/**
     * perform controller action
     *
     */
    public function performAction($action = "", $call_controller = true, $call_instance_class = false) {
		if (!$call_controller) {
			if ($call_instance_class || 
				(!$call_instance_class && in_array($action, self::$noCrudActions))) {
				$_this = $this->callingClassInstance;
				$calling_instance = true;
			} else {
				$_this = $this;
				$calling_instance = false;
			}
			if (method_exists($_this, $action)) {
				if ($calling_instance) $_this->checkMVC();
				return $_this->$action();
			}
		} else {
			return $this->controller($action);
		}
	}
	
	/**
     * MVC -> Read all (read Records) -> controller
     *
     *  @return all User Records as Html-Table -> MVC - Pattern (M)odell = MVC::readRecords_model() , (V)iew = readRecords_{engine}.twig, (C)ontroller = CRUD::readRecords()
	 *  Normally you can separate model class and controller class, but here it is in one class App
	 ** this controller can handle both -> MVC-Pattern and MVVM-Pattern
	 ** (M)odel (V)iew (V)iew(M)odel -> (M)odel = CRUD::readRecords_model(), (V)iew = readRecords_{engine}.twig (wird vom client geladen, oder bei underscore vom Server übermittelt)
	 ** (V)iew(M)odel = rendering der vom Server übermittelten model_daten MVC::readRecords_model() mittels einer client-side javascript template engine
	 ** mit den vom Client oder Server bereitgestellten View
	 */
    public function controller($controller = "") {
		echo $this->modelView($controller);
	}
	
	/**
     * MVC -> ModelView
     *
     *  @return all User Records as Html-Table -> MVC - Pattern (M)odell = MVC::readRecords_model() , (V)iew = readRecords_{engine}.twig, (C)ontroller = CRUD::readRecords()
	 *  Normally you can separate model class and controller class, but here it is in one class App
	 ** this controller can handle both -> MVC-Pattern and MVVM-Pattern
	 ** (M)odel (V)iew (V)iew(M)odel -> (M)odel = MVC::readRecords_model(), (V)iew = readRecords_{engine}.twig (wird vom client geladen, oder bei underscore vom Server übermittelt)
	 ** (V)iew(M)odel = rendering der vom Server übermittelten model_daten CRUD::readRecords_model() mittels einer client-side javascript template engine
	 ** mit den vom Client oder Server bereitgestellten View
	 */
	public function modelView($controller) { //(C)ontroller part of MVC or MVVM pattern see MVC::controller()

		if ($this->callingClass=="App") {
			//index.twig
			$view = $controller . '.' . self::$templateExt;
			$twig_var = "data";
		} else {
			//readRecords_{engine}.twig
			$view = $controller . '_' . self::$templateEngine . '.' . self::$templateExt; 
			$twig_var = "records";
		}
		//readRecords_model
		$model = $controller; 
		//MVC::modelController() call method
		$model_data = $this->modelController($model);
		

	    if (self::$clientRendering && $this->callingClass!="App") {
			//render on client side with underscore.js, twig.js or nunjucks.js -> (V)iew(M)odel part of MVVM-Pattern
			//MVVM Pattern -> (M)odell part of MVVM-Pattern , (V)iew part is loaded from template javascript engine on the client from server -> output model_data as jSON-Object to client
			return json_encode($model_data); 
			//(V)iew part for underscore.js engine is submitted by server in index.twig->javascript.twig in class App:index()
		} else {
			// render on server with twig -> marriage of (V)iew with (M)odel by PHP-Twig Template rendering engine through this method (C)ontroller CRUD::readRecords() and output rendered html to client
			return  $this->twig->render($view, array(
				$twig_var => $model_data,
			));//MVC Pattern
		}
	}

	/**
     * MVC -> Read all (read Records) get model data
     *
     ** @return all modelController get model data for readRecords_....twig page controller
     */
	public function modelController($controller_model) {
		return $this->modelDataLogic($controller_model);
	}

	/**
     * MVC -> readRecords_model get model data logic for readRecords page controller
     *
     ** @return model readRecords model data logic
     */
	public function modelDataLogic($controller_model) {
		//index_model_logic
		$model_logic = $controller_model; 
		//MVC::readRecords_model_logic() call method or App::index_model_logic()
		$model = $this->performAction($model_logic, false, true);
		
		if ($this->callingClass=="App") {
			$multiple_rows = false;
		} else {
			$multiple_rows = true;
		}
		
		$model_data = $this->modelData($model, $multiple_rows);

		
		return $model_data;
	}
	
	/**
     * MVC -> Read all (read Records) get model data
     *  @param $multipleRows bool -> if should be multi dimension array model_sata output or not
     ** @return model model data
     */
	public function modelData($model, $multipleRows = false) {
		
		$model_data = array();

		if (!$result = mysqli_query(self::$db, $model)) {
			exit(mysqli_error(self::$db));
		}
		
		// if query results contains rows then featch those rows 
		$command = strtoupper(substr($model,0,6));
		if ($command=="REPLAC") $command.="E";
		if ($command=="SELECT") {
			$num_rows = mysqli_num_rows($result);
		} else {
			$num_rows = mysqli_affected_rows(self::$db);
		}
		if($num_rows > 0) {
			if ($command=="SELECT") {
				while($row = mysqli_fetch_assoc($result)) {
					if ($multipleRows) {
						$model_data[] = $this->prepareRecord($row);
					} else {
						$model_data = $this->prepareRecord($row);
					}
				}
			} else {
				$res = array("command" => $command, "result" => "success", "not" => "");
				if ($multipleRows) {
					$model_data[] = $res;
				} else {
					$model_data = $res;
				}
			}
		} else {
			$res = array("command" => $command, "result" => "info", "not" => "not");
			if ($multipleRows) {
				$model_data[] = $res;
			} else {
				$model_data = $res;
			}
		}

		return $model_data;
		 
	}

	/**
     * Sanitize POST Data for db 
     *
     */
	public function preparePost() {
		foreach ($_POST as $key => $val) {
			$_POST[$key] = utf8_decode(str_replace("'", "\'", $val));
		}
	}

	/**
     * Sanitize Records from DB for Client output 
     *
     */
	public function prepareRecord($record) {
		$rec = array();
		foreach ($record as $key => $val) {
			$rec[$key] = utf8_encode($val);
		}
		return $rec;
	}

	

}
?>