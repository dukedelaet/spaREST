<?php defined('BASEPATH') OR exit('No direct script access allowed');
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
//	1. Install this file (SpaREST_Controller), sparest_model, and REST_Controller files 
//	2. Setup your database
//	3. Configure your database in CodeIgniter
//	4. Check it out!  www.yourdomain.com/api/table_name(or alias_name)
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

require(APPPATH.'/libraries/REST_Controller.php');  //Include the base REST_Controller so I don't have to rewrite the whole implementation

class spaREST_Controller extends REST_Controller {
	protected $autoDetect=NULL;
	protected $excludeAliases=NULL;
	protected $includeAliases=NULL;
	protected $sparestMethod='sparest';

	// Constructor function
	public function __construct()
	{
		parent::__construct();

		// Config this sucker!
		$this->load->config('sparest');
		//TODO:implement autodetect, include/exclude aliases
		$this->autoDetect = $this->config->item('CodeGenAutodetect');
		$this->excludeAliases = $this->config->item('CodeGenExclude');
		$this->includeAliases = $this->config->item('CodeGenInclude');
		$this->sparestMethod='sparest';
	}

	//Remap
	//Copied from REST_Controller... but we get to override to inject some functionality to support the spaREST system
	public function _remap($object_called)
	{
		// If the controller method doesn't exist.... assume it's a spaREST call. so route it back to the controller
		$controller_method = $object_called . '_' . $this->request->method;
		if (!method_exists($this, $controller_method)){
			$controller_method = $this->sparestMethod.'_'.$this->request->method;
			//but if the sparest method doesn't exist... kick a 404 (I like the 808 kick better. Just sayin')			
			if (method_exists($this, $controller_method)){
				$this->$controller_method();
			}
			else{
				$this->response(array('status' => 0, 'error' => 'Unknown method:'.$controller_method), 404);
				return;				
			}			
		}
		//default to normal REST_Controller behavior
		else{
			parent::_remap($object_called);
		}
		
	}

	//TODO:Finish this function
	public function parseSparestUri($uri){
	}
}
