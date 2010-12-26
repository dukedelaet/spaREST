<?php

//*****************************************************************
// 
//  sparest - A Simple Parsing Algorithm for REST 
//  Version: 0.1
//  Created On: 12.26.2010    
//  Pronounced "SPARE-est" this model is designed to make it
//	as easy as possible to set up a REST api for your database. 
//	built on the CodeIgniter framework with the ActiveRecord Pattern
//	Using RESTController for the REST implementation
//
//	4 easy steps;
//	1. Install this file (data model), SpareRestController, and RESTController files 
//	2. Setup your database
//	3. Configure your database in CodeIgniter
//	4. Check it out!  /api_controller/table_name(or alias_name)
//
//
//
// Copyright (C) 2010 by Duke DeLaet, Graphic Violence Design Studio
// under the MIT License
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
//*****************************************************************



class Sparest_model extends Model {
	var $info = array(); //Holds all the tableInfo from information_schema
	var $keys = array(); //Holds all the Field Names in the Table
	var $tableName = ''; //Holds the real Tablename. in the init(), we handle Aliasing against a config file
	var $primaryKey = ''; // Holds the table's primary key.

	//Creates the model
	function Sparest_model()
	{
		// Call the Model constructor
		parent::Model();
		$this->load->database();
	}

	//*******************
	//Helper functions
	//*******************
	//initializes the $keys Array with a "reflection" call on Table Key Names
	function initializeKeys(){
		//Get Table Keys from ActiveRecord (MySQL Syntax) TODO:Add "Reflections" for other DBMSs and move to plugin for CI
		$this->db->select('column_name AS name,column_type,is_nullable,column_key,column_default,extra');
		$this->db->from('information_schema.COLUMNS');
		$this->db->where('table_schema', $this->db->database ); //The Active Database (in Database.config)
		$this->db->where('table_name', $this->tableName);
		$query = $this->db->get();
		
		//Fill the Array
		$resultsArray = array();
		$this->info = $query->result();
		foreach ((array)$this->info as $row)
		{
			$resultsArray[] = $row->name;
			//initializes the $primaryKey against the info data
			if ($row->column_key == 'PRI'){
			    $this->primaryKey = $row->name;
			}
		}		
		$this->keys = $resultsArray;
	}
	//Initialize the app (call by itself, or by the constructor)
	function init($tableAlias)
	{
		//TODO: Handle Aliasing from a config file. in later versions, we'll need to do conceptual datasets in JOINs
		$this->tableName = $tableAlias; //right now, just pass the alias directly
		$this->initializeKeys();
	}
	//Gets an PHP array with all of the keys set to $this->keys
	function getRepArray()
	{
		//iterate throught the keys array and set the values to null in the resulting array;
		$repArray = array();
		foreach ($this->keys as $key) {
	 		$repArray[$key] = '';
		}
		return $repArray;
	}

	//*******************
	//SCHEMA functions
	//*******************
	//TODO:Return a JSON Schema definition
	function getJSONSchema(){

	}
	

	//*******************
	//CORE REST functions - CRUD
	//*******************

	//Performs an INSERT statement with a representational array by calling createupdate with only the first parameter
	//   Returns Boolean on whether or not the insert Succeeded
 	//Wire this to PUT method in the REST Controller
	function create($repArray){
		return $this->createupdate($repArray);
		//TODO:Add iterative support for Arrays of arrays?
	}


	//Performs a SELECT statement with a filter (formatted like CI's ActiveRecord where() function accepts) and a limit range
	//   Returns an array of Objects
 	//Wire this to POST method in the REST Controller
	function read($filter=NULL, $byPrimaryKey=NULL, $limitRange=NULL)
	{
		$selectClause = implode(', ', $this->keys);
		$this->db->select($selectClause);
		$this->db->from($this->tableName);
		//Select By Primary Key if there is one
		if (!empty($byPrimaryKey) && !empty($this->primaryKey)) {
		    $this->db->where($this->primaryKey, $byPrimaryKey);
		}
		//Set a Filter in assoc array style (like CI "Associative array method" in ActiveRecord does)
		//Optionally, you can use LIKE here when you call it so there's no implementation of db->like()
		if (!empty($filter)) {
			$this->db->where($filter);
		}
		//Set a limit (no offset support, why would I need it?)
		if (!empty($limitRange)) {
			$this->db->limit($limitRange);
		}
		$query = $this->db->get();
		return $query->result();
	}

	//Performs an UPDATE statement with a representational array (or array of arrays) by calling createupdate with only the first parameter
	//   Returns Boolean on whether or not the insert Succeeded
 	//Wire this to PUT method in the REST Controller
	function update($repArray, $byPrimaryKey){
		return $this->createupdate($repArray, $byPrimaryKey);
	}


	//Performs a DELETE statement with a primary key
	//   Returns Boolean on whether or not the Update Succeeded
 	//Wire this to PUT method in the REST Controller
	function delete($byPrimaryKey)
	{
		return $this->db->delete($this->tableName, array($this->primaryKey => $byPrimaryKey ));
	}

	//Performs an INSERT or UPDATE statement with a representational array, insert uses no primary key
	//   Returns Boolean on whether or not the Update Succeeded
 	//Wire this to PUT method in the REST Controller
	function createupdate($repArray, $byPrimaryKey)
	{
		//Drop the primary key in the $repArray so we don't have to worry about it screwing with the update/insert
		unset($repArray[$this->primaryKey]);				
		//The object should have valid keys, you can get an empty one from $this->getRepArray();
		if (!empty($byPrimaryKey)) {
			return $this->db->update($this->tableName, $repArray, array($this->primaryKey => $byPrimaryKey ));
		}
		else {		        
			return $this->db->insert($this->tableName, $repArray);
		}

	}


	//*******************
	//REFLECTION INFO FOR Autogenerating Views and stuff
	//*******************
	//TODO: Return $this->info in a way that makes sense for auto-genning views and jquery component handling (use the JSON schema stuff)


}

?>
