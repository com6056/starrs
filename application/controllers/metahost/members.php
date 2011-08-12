<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . "libraries/core/ImpulseController.php");

class Members extends ImpulseController {

	public static $mHost;

	public function index() {
		redirect(base_url()."metahosts/owned",'location');
	}
	
	public function view($metahostName=NULL) {
		if($metahostName == NULL) {
			exit($this->_error("No metahost specified"));
		}
		$this->_load_metahost($metahostName);
		
		// Navbar
		$navModes['CREATE'] = "/metahosts/members/create/".rawurlencode(self::$mHost->get_name());
		$navOptions['Overview'] = '/metahosts/view/'.rawurlencode(self::$mHost->get_name());
		$navOptions['Members'] = '/metahosts/members/view/'.rawurlencode(self::$mHost->get_name());
		$navOptions['Rules'] = '/metahosts/rules/view/'.rawurlencode(self::$mHost->get_name());
		$navbar = new Navbar(self::$mHost->get_name()." - Members", $navModes, $navOptions);
		
		// Load the view data
		$info['header'] = $this->load->view('core/header',"",TRUE);
		$info['sidebar'] = $this->load->view('core/sidebar',"",TRUE);
		$info['navbar'] = $this->load->view('core/navbar',array("navbar"=>$navbar),TRUE);
		$info['data'] = $this->_load_members();
		$info['title'] = "Metahost Members - ".self::$mHost->get_name();
		
		// Load the main view
		$this->load->view('core/main',$info);
	}
	
	public function delete($metahostName=NULL,$address=NULL) {
		if($metahostName == NULL) {
			exit($this->_error("No metahost specified"));
		}
		if($address == NULL) {
			exit($this->_error("No address specified"));
		}
		try {
			self::$mHost = $this->api->firewall->get->metahost($metahostName,true);

			$membr = self::$mHost->get_member($address);
				
			$this->api->firewall->remove->metahost_member($membr);
			self::$mHost = $this->api->firewall->get->metahost($metahostName,true);
			self::$sidebar->reload();
			redirect(base_url()."metahosts/members/view/".rawurlencode(self::$mHost->get_name()),'location');
		}
		catch (DBException $dbE) {
			exit($this->_error($dbE->getMessage()));
		}
		catch (AmbiguousTargetException $atE) {
			exit($this->_error($atE->getMessage()));
		}
		catch (ObjectNotFoundException $onfE) {
			exit($this->_error($onfE->getMessage()));
		}
	}
	
	public function create($metahostName=NULL) {
		if($this->input->post('submit')) {
			try {
				self::$mHost = $this->api->firewall->get->metahost($metahostName,true);
				$membr = $this->_create();
				self::$mHost = $this->api->firewall->get->metahost($metahostName,true);
				self::$sidebar->reload();
				redirect(base_url()."members/view/".rawurlencode($membr->get_name()),'location');
			}
			catch (DBException $dbE) {
				exit($this->_error($dbE->getMessage()));
			}
			catch (AmbiguousTargetException $atE) {
				exit($this->_error($atE->getMessage()));
			}
			catch (ObjectNotFoundException $onfE) {
				exit($this->_error($onfE->getMessage()));
			}
		}
		else {
			// Navbar
            $navModes['CANCEL'] = "";
            $navbar = new Navbar("Create Metahost Member", $navModes, null);

			// Load the view data
			$info['header'] = $this->load->view('core/header',"",TRUE);
			$info['sidebar'] = $this->load->view('core/sidebar',"",TRUE);
			$info['navbar'] = $this->load->view('core/navbar',array("navbar"=>$navbar),TRUE);
			
			// Get the preset form data for dropdown lists and things
			$form['user'] = $this->impulselib->get_username();
			$form['addrs'] = $this->api->systems->get->owned_addresses($this->impulselib->get_username());
			if($this->api->isadmin() == TRUE) {
				$form['admin'] = TRUE;
			}
			
			// Continue loading view data
			$info['data'] = $this->load->view('metahosts/member/create',$form,TRUE);
			$info['title'] = "Create Metahost";
			
			// Load the main view
			$this->load->view('core/main',$info);
		}
	}
	
	private function _load_members() {
		$viewData = "";
		foreach(self::$mHost->get_members() as $membr) {
			$viewData .= $this->load->view('metahosts/member/view',array("membr"=>$membr),TRUE);
		}
		
		if($viewData == "") {
			return $this->_warning("No members found!");
		}
		return $viewData;
	}
	
	private function _create() {
		$membr = $this->api->firewall->create->metahost_member($this->input->post('address'),self::$mHost->get_name());
		self::$mHost->add_member($membr);
		return $membr;
	}
}
/* End of file members.php */
/* Location: ./application/controllers/metahosts/members.php */
