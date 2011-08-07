<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . "libraries/core/ImpulseController.php");

class Switchview extends ImpulseController {

    public function settings($systemName=NULL) {
        $systemName = rawurldecode($systemName);
        try {
            $this->_load_system($systemName);
        }
        catch(ObjectNotFoundException $onfE) {
            $this->_error("Unable to find system \"$systemName\"");
        }

        try {
            $settings = $this->api->network->get->switchview_settings($systemName);
            $viewData = $this->load->view('network/switchview/view',array("settings"=>$settings),TRUE);
            $navModes['EDIT'] = "/switchview/edit/".rawurlencode(self::$sys->get_system_name());
            $navModes['DELETE'] = "/switchview/delete/".rawurlencode(self::$sys->get_system_name());
        }
        catch(ObjectNotFoundException $onfE) {
            $viewData = $this->_warning("Switchview not enabled on this device");
            $navModes['CREATE'] = "/switchview/create/".rawurlencode(self::$sys->get_system_name());
        }
        catch(DBException $dbE) {
            $this->_error($dbE->getMessage());
        }

        // Navbar
        $navOptions['System'] = "/systems/view/".rawurlencode(self::$sys->get_system_name());
        $navbar = new Navbar("Switchview Settings - ".self::$sys->get_system_name(), $navModes, $navOptions);

        // Load view data
        $info['header'] = $this->load->view('core/header',"",TRUE);
        $info['sidebar'] = $this->load->view('core/sidebar',"",TRUE);
        $info['title'] = "Switchview Settings - ".self::$sys->get_system_name();
        $info['navbar'] = $this->load->view('core/navbar',array("navbar"=>$navbar),TRUE);
        $info['data'] = $viewData;

        // Load the main view
        $this->load->view('core/main',$info);
    }

    public function create($systemName=NULL) {
        $systemName = rawurldecode($systemName);
        try {
            $this->_load_system($systemName);
        }
        catch(ObjectNotFoundException $onfE) {
            $this->_error("Unable to find system \"$systemName\"");
        }

        if($this->input->post('submit')) {
            try {
                $rwCommunity = NULL;
                if($this->input->post('rw_community')) {
                    $rwCommunity = $this->input->post('rw_community');
                }
                $this->api->network->create->switchview_settings(
                    $systemName,
                    $this->input->post('enable'),
                    $this->input->post('ro_community'),
                    $rwCommunity
                );
                redirect(base_url()."switchview/settings/".rawurlencode(self::$sys->get_system_name()),'location');
            }
            catch(Exception $e) {
                $this->_error($e->getMessage());
            }
        }
        else {
            // Navbar
            $navModes['CANCEL'] = "/switchview/settings/".rawurlencode(self::$sys->get_system_name());
            $navbar = new Navbar("Create Switchview Settings", $navModes, null);

            // Load view data
            $info['header'] = $this->load->view('core/header',"",TRUE);
            $info['sidebar'] = $this->load->view('core/sidebar',"",TRUE);
            $info['title'] = "Create Switchview Settings";
            $info['navbar'] = $this->load->view('core/navbar',array("navbar"=>$navbar),TRUE);
            $info['data'] = $this->load->view('network/switchview/create',null,TRUE);

            // Load the main view
            $this->load->view('core/main',$info);
        }
    }

    public function delete($systemName=NULL) {
        $systemName = rawurldecode($systemName);
        try {
            $this->_load_system($systemName);
            $this->api->network->remove->switchview_settings($systemName);
            redirect(base_url()."switchview/settings/".rawurlencode(self::$sys->get_system_name()),'location');
        }
        catch(ObjectNotFoundException $onfE) {
            $this->_error("Unable to find system \"$systemName\"");
        }
        catch(Exception $e) {
            $this->_error($e->getMessage());
        }
    }

    public function edit($systemName=NULL) {
        // @todo: waiting until I figure out NetworkSystem object and shit
    }

}
/* End of file switchview.php */
/* Location: ./application/controllers/switchview.php */