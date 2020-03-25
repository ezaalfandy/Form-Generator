<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	protected  $form_config = array(
		"table" => "kontingen",
		"link" => 'Welcome/submit-kontingen',
		"exclude_field" => [
			'pembayaran_dn',
			'id_pembayaran',
			'pembayaran_ln',
			'jenis_pendaftaran',
			'status_data'
		],
		"custom_field" => [
			"provinsi" =>  [
				"type" => "dropdown",
				"options" => [
					"DKI JAKARTA" => "DKI JAKARTA"
				]
			],
			"password" =>  [
				"type" => "password"
			],
			"keterangan" =>  [
				"type" => "textarea",
				"required" => false
			]

		]
	);

	public function __construct(){
		parent::__construct();
	}
	
	public function index()
	{	
		$data['form'] = $this->form_generator->generate_form($this->form_config);
		$this->load->view('home', $data);
		
	}

	public function submit_kontingen(){
		$data = $this->form_generator->validate_form($this->form_config);
		
	}
}
