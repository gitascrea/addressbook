<?php
require_once __DIR__.'/class_mvc.php';
require_once __DIR__.'/class_base.php';
/**
 * helper class for doing CRUD and Database Conection
 *
 * @package     CRUD
 * @author      Antonio Scarfone
 * @copyright   2018 Coeln Concept 
 */

/**
 * Class CRUD -> Create, Read, Update, Delete
 */
class CRUD extends Base {
	/**
     * @var $crudActions -> defines methods for crud
     */
	public static $crudActions = array('readRecords','readRecord','newRecord','readContact','addRecord','updateRecord','deleteRecord');

	/**
     * constructor
     *
     */
    public function __construct($action = "") {
		parent::__construct();
		$is_main_controller = (in_array($action, self::$crudActions));
		$this->mvc = new MVC($action, $is_main_controller, $this);
	}

	/**
     * CRUD -> Read all (read Records) get model data logic
     *
	 * called from index.php ajax call for fragment display renders readRecords_{engine}.twig -> MVC methods in class MVC
	 * this method get the model(sql) and can implement some logic like for example filtering, pagination ...
	 * Convention to declare such a method is {prefix name of template}_model_logic -> for example readRecords_model_logic (index.php -> renders readRecords_{engine}.twig in html container)
	 * this methods are called from MVC class in method MVC::modelDataLogic()
     ** @return all readRecords_modelLogic get model data logic for readRecords_....twig page controller
     */
	public function readRecords() {
		//$model = "SELECT * FROM users;";
		$model = $this->getModel("SELECT","users");

		return $model;
	}

	/**
     * CRUD -> Read (read)
     *
     ** @return JSON Single User entry
     */
	public function readRecord() {
		// check request
		if(isset($_POST['id']) && isset($_POST['id']) != "") {
			$model = $this->getModel("SELECT","users","readRecord");
			return $model;
		} else {
			return "SELECT 'nodata' as 'nodata';";
		}
	}

	/**
     * CRUD -> Create (new)
     *
     ** @return Message String on success
     */
	public function newRecord() {
		$model = $this->getModel("ADD","users","newRecord");

		return $model;
	}

	public function readContact() {
		$model = "SELECT 'This is the contact content section!!! !!!' as 'contact';";

		return $model;
	}

	/**
     * CRUD -> get model
     *
     ** @return Message String on success
     */
	public function getModel($mode,$model,$add_field="") {
		$result = "select 'nodata' as 'nodata';";
		$this->mvc->preparePost();
		if ($model=="users") {
			$ref = "id";
			if (isset($_POST['id'])) {
				$id = $_POST['id'];
			} else {
				$id = "";
			}
			if (isset($_POST['first_name'])) {
				$first_name = $_POST['first_name'];
			} else {
				$first_name = "";
			}
			if (isset($_POST['last_name'])) {
				$last_name = $_POST['last_name'];
			} else {
				$last_name = "";
			}
			if (isset($_POST['email'])) {
				$email = $_POST['email'];
			} else {
				$email = "";
			}
			$fields = array("id","first_name","last_name","email");
			$values = array($id,$first_name,$last_name,$email);
			if ($mode=="INSERT") {
				$result = "$mode INTO $model(".implode(",", $fields).") VALUES('".implode("','", $values)."');";
			}
			if ($mode=="UPDATE") {
				$count_fields = count($fields);
				$set_update = "";
				for ($i=1;$i<$count_fields;$i++) {
					$set_update .= $fields[$i]." = '".$values[$i]."',";
				}
				$set_update = substr($set_update,0,strlen($set_update)-1);
				$result = "$mode $model SET $set_update  WHERE $ref = '$id';";
			}
			if ($mode=="DELETE") {
				$result = "$mode FROM $model WHERE $ref = '$id';";
			}
			if ($mode=="SELECT") {
				$WHERE = "";
				if ($add_field) $add_field = ",'".$add_field."' as 'ref'";
				if ($id) $WHERE = "WHERE $ref = '$id'";
				$result = "$mode * $add_field FROM $model $WHERE;";
			}
			if ($mode=="ADD") {
				if ($add_field) $add_field = "'".$add_field."' as 'ref'";
				$result = "SELECT $add_field;";
			}
			return $result;
		}
		return $result;
	}

	/**
     * CRUD -> Create (add)
     *
     ** @return Message String on success
     */
	public function addRecord() {
		if(isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email'])) {
			$model = $this->getModel("INSERT","users");
			return $model;
		} else {
			return "SELECT 'nodata' as 'nodata';";
		}
	}

	/**
     * CRUD -> Update (update)
     *
     */
	public function updateRecord() {
		// check request
		if(isset($_POST)) {
			$model = $this->getModel("UPDATE","users");
			return $model;
		} else {
			return "SELECT 'nodata' as 'nodata';";
		}
	}

	/**
     * CRUD -> Delete (delete)
     *
     */
	public function deleteRecord() {
		// check request
		if(isset($_POST['id']) && isset($_POST['id']) != "") {
			$model = $this->getModel("DELETE","users");
			return $model;
		} else {
			return "SELECT 'nodata' as 'nodata';";
		}
	}
	

}
?>