<?php
  
  /** php-form-helper is designed to help programmers to simplify the back-end process flow of data related to HTML form. 
* @link https://github.com/yannbelief/php-pdo-helper
* @author CHEN Yen Ming https://github.com/yannbelief/
* @copyright 2013 CHEN Yen Ming
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/
  
abstract class FormHelper {
	private $keyValues = array();
	
	function __construct() {
		$this->init();
	}
	
	function get($name) {
		return $this->keyValues[$name];
	
	}
	
	function getInt($name) {
		return (int)$this->get($name);
	}
	
	function set($name,$value) {
		$this->input($name,$value);
	}

	/* let formHelper keep information of an input name and its initial value */
	function input($name,$value=""){
		$this->keyValues[$name] = $value; 
	}

	/* render this name-value pair with html format*/
	function renderNameIntValue($name) {
		$value = (int)$this->keyValues[$name];
		echo " name=\"$name\" value=\"$value\" ";
	}
	
	/* render this name-value pair with html format*/
	function renderNameValue($name) {
		$value = str_replace('"','&quot;',$this->keyValues[$name]);
		echo " name=\"$name\" value=\"$value\" ";
	}
	
	
	/* get array of input names */
	private function get_input_names() {
		return array_keys($this->keyValues);
	}
	
	/* check whether $arr contains every input name of this form as its key, the corresponding value may be empty.*/
	function isEntirelyIn($arr) {
		$names = $this->get_input_names();
		foreach($names as $name) {
				//if(isset($arr[$name]) == false)
				//	echo $name;
			if(isset($arr[$name]) == false)
				return false;

		}
		return true;
	}
	
	/* check whether $arr contains at least one input name of this form as its key, the corresponding value may be empty.*/
	function isPartiallyIn($arr) {
		$names = $this->get_input_names();
		foreach($names as $name) {
			if(isset($arr[$name]) == true)
				return true;
		}
		return false;
	}
	
	function importFromArray($arr) {
		$this->setValues($arr);
	}
	
	/* set input values by an array*/
	function setValues($arr) {
		$names = $this->get_input_names();
		foreach($names as $name) {
			$this->input($name,$arr[$name]);
		}
		//var_dump($this);
	}
	function exportToArray() {
		return $this->getValues();
	}
	
	function getValues() {
		return $this->keyValues;
	}
	/* init input names of an form, it's for the use of setValues() method*/
	abstract function init(); 

	
	/* use given model to save the form data. While mapping input name to model property name, input prefix and suffix will be dropped if they are given.*/
	function exportToModel($className, $form_prefix = "", $form_suffix = "") {

		$model = new $className;
		$reflect = new ReflectionClass($model);
		$props   = $reflect->getProperties();
		
		foreach ($props as $prop) {
			$name = $prop->getName();
			$input = $form_prefix . $name . $form_suffix ;
			if(isset($this->keyValues[$input])) {
				$value = $this->keyValues[$input];
				$model->$name = $value;
			}
		}
		
		return $model;
	
	}
	function contains($input) {
		return array_key_exists($input, $this->keyValues);
	}
	
	/* use given model's property to fill the form data. While mapping model property name to input name, input prefix and suffix will be added if they are given.*/
	function importFromModel($model, $form_prefix = "", $form_suffix = "") {

		
		$reflect = new ReflectionClass($model);
		$props   = $reflect->getProperties();
		foreach ($props as $prop) {
			$name = $prop->getName();
			$input = $form_prefix . $name . $form_suffix ;
			$value = $model->$name;
			if($this->contains($input))
				$this->input($input,$value);
			
		}
	}
}
?>
