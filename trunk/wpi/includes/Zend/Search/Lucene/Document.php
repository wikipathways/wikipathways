<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Document
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/** Zend_Search_Lucene_Field */
require_once 'Zend/Search/Lucene/Field.php';


/**
 * A Document is a set of fields. Each field has a name and a textual value.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Document
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Document
{

    /**
     * Associative array Zend_Search_Lucene_Field objects where the keys to the
     * array are the names of the fields.
     *
     * @var array
     */
    protected $_fields = array();

    public $boost = 1.0;


    /**
     * Proxy method for getFieldValue(), provides more convenient access to
     * the string value of a field.
     *
     * @param  $offset
     * @return string
     */
    public function __get($offset)
    {
        return $this->getFieldValue($offset);
    }


    /**
     * Add a field object to this document.
     *
     * @param Zend_Search_Lucene_Field $field
     * @return Zend_Search_Lucene_Document
     */
    public function addField(Zend_Search_Lucene_Field $field)
    {
        //$this->_fields[$field->name] = $field;

        if ($this->_fields[$field->name] === null) {
          $this->_fields[$field->name] = $field;
        } else if ($this->_fields[$field->name] instanceof Zend_Search_Lucene_Field ) {
          $_newArray = array();
          array_push($_newArray, $this->_fields[$field->name]);
          array_push($_newArray, $field);
          $this->_fields[$field->name] = $_newArray;
        } else if (is_array($this->_fields[$field->name])) {
          array_push($this->_fields[$field->name],  $field);
        } else {
          throw new Zend_Search_Lucene_Exception("unknown fieldType : " + getType($field));
        }
    }


    /**
     * Return an array with the names of the fields in this document.
     *
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->_fields);
    }


    /**
     * Returns Zend_Search_Lucene_Field object for a named field in this document.
     *
     * @param string $fieldName
     * @return Zend_Search_Lucene_Field
     */
    public function getField($fieldName)
    {
        //return $this->_fields[$fieldName];

        if (!array_key_exists($fieldName, $this->_fields)) {
            throw new Zend_Search_Lucene_Exception("Field name \"$fieldName\" not found in document.");
        } else if ($this->_fields[$fieldName] instanceof Zend_Search_Lucene_Field ) {
          return $this->_fields[$fieldName];
        } else if (is_array($this->_fields[$fieldName])) {
          return $this->_fields[$fieldName][0];
        } else  {
          throw new Zend_Search_Lucene_Exception("unknown fieldType : " + getType($field));
        }
    }


    /**
     * Returns the string value of a named field in this document.
     *
     * @see __get()
     * @return string
     */
    public function getFieldValue($fieldName)
    {
        return $this->getField($fieldName)->value;
    }

    /**
     * Returns the string value of a named field in UTF-8 encoding.
     *
     * @see __get()
     * @return string
     */
    public function getFieldUtf8Value($fieldName)
    {
        return $this->getField($fieldName)->getUtf8Value();
    }
    
    public function getFieldValues($fieldName) {
    	$fv = array();
    	foreach($this->getFields($fieldName) as $f) {
    		$fv[] = $f->value;
    	}
    	return $fv;
    }
    
    public function getFields($fieldName)
    {
        if (!array_key_exists($fieldName, $this->_fields)) {
            throw new Zend_Search_Lucene_Exception("Field name \"$fieldName\" not found in document.");
        } else if ($this->_fields[$fieldName] instanceof Zend_Search_Lucene_Field ) {
          return array($this->_fields[$fieldName]);
        } else if (is_array($this->_fields[$fieldName])) {
          return $this->_fields[$fieldName];
        } else  {
          throw new Zend_Search_Lucene_Exception("unknown fieldType : " + getType($field));
        }
    }  
}
