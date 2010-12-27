<?php
require(APPPATH.'/libraries/spaREST_Controller.php');  
  

class api extends spaREST_Controller 
{  

	//sparest code 
	function sparest_get()
	{
		try{
			//Parse Uri and get a table alias, primary key, filter, and limit
			$data = NULL;
			$tableAlias = $this->uri->segment(2);//TODO:filter these out in the controller against the config
			$this->load->model('Sparest_model');
			$this->Sparest_model->init($tableAlias);

			//Call to the uri parser to pull back an associative array of sparest specific commands
			$arrKeys = array('filter', 'format', 'user', 'group', 'pk', 'limit');
			$this->uri->keyval = array(); //Remove the URI cache in CodeIgniter to prevent caching errors
			$arrUri = $this->uri->uri_to_assoc(3, $arrKeys);

			$data = $this->Sparest_model->read($arrUri['filter'], $arrUri['pk'], $arrUri['limit']);
			$this->response($data, 200);
			//$this->response($arrUri, 200);
		}
		catch (Exception $e) {
			$this->response(array('status' => 0, 'error' => 'REST Exception:'.$e->getMessage()), 404);
		}
		//TODO:Catch Database Errors and issue a 404
	}

	//TODO:Implement post
	//TODO:Implement put
	//TODO:Implement delete






} 




 
?>
