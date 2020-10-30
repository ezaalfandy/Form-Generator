<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
    
	protected  $form_config = array(
		"table" => "kontingen",
		"link" => 'Welcome/submit-kontingen'
	);


    public function __construct(){
        parent::__construct();
        $this->load->model('Home_model');
    }
    
    public function index(){
        $data['list_tables'] = $this->Home_model->get_all_table();
        
        if($this->uri->segment(3) == null){
            $table = $data['list_tables'][0];
        }else{
            $table = $this->uri->segment(3);
        }

        $data['field_data'] = $this->Home_model->get_field_data($table);
        
        $config = array(
            "table" => $table, 
            "controller" => $this->uri->segment(4),
            "session_key" => $this->uri->segment(5),
            "session_value" => $this->uri->segment(6)
        );
		$this->form_generator->initialize($config);
        
		$data['form'] = $this->form_generator->generate_form();
		$data['edit_modal'] = $this->form_generator->generate_edit_modal();
        $data['form_config_json'] = json_encode($this->form_generator->retreive_form_config());
        $data['form_config'] = $this->form_generator->retreive_form_config();
        
        $data['insert_controller'] = $this->form_generator->generate_insert_controller();
        $data['delete_controller'] = $this->form_generator->generate_delete_controller();
        $data['edit_controller'] = $this->form_generator->generate_edit_controller();
        $data['get_specific_controller'] = $this->form_generator->generate_get_specific_controller();
        $data['view_controller'] = $this->form_generator->generate_view_controller();
        
        $data['model'] = $this->form_generator->generate_array_model();
        $data['table'] = $this->form_generator->generate_table();
        $data['javascript'] = $this->form_generator->generate_javascript();
        $this->load->view('home', $data);
		
    }
                        
    
                        
}   

/* End of file Home.php */

?>