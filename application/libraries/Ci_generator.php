<?php

    class Ci_generator{

        /*
            STRUKTUR DASAR FORM CONFIG
            [
                "Nama Kolom" =>[
                    "Atribut" => [
                        "name" => "value",
                        "id" => "value",
                        "class" => "value"
                    ],
                    "data" => [
                        "options" => [
                            "value", "value"
                        ],
                    ]
                    
                ]
            ]
        */

        public $form = '';

        public $table = '';
        
        public $camel_case_table = '';
        
        public $error_msg = array();

        public $form_config = NULL;

        public $controller = ' ';

        public $session_key = ' ';

        public $session_value = ' ';
        
        public $primary_key = ' ';

        protected $_CI;

        public function __construct(array $config = array()){
            empty($config) OR $this->initialize($config, FALSE); 

            $this->_CI =& get_instance();
            $this->_CI->load->database();
            $this->_CI->load->helper('form');
            $this->_CI->load->library('form_validation');
        }

        public function initialize(array $config = array(), $reset = TRUE){
            $reflection = new ReflectionClass($this);

            if ($reset === TRUE){
                $defaults = $reflection->getDefaultProperties();
                foreach (array_keys($defaults) as $key){
                    if ($key[0] === '_' || $key === 'primary_key'){
                        continue;
                    }
    
                    if (isset($config[$key])){
                        if ($reflection->hasMethod('set_'.$key)){
                            $this->{'set_'.$key}($config[$key]);
                        }else{
                            $this->$key = $config[$key];
                        }
                    }else{
                        $this->$key = $defaults[$key];
                    }
                }
            }else{
                foreach ($config as $key => &$value){
                    if ($key[0] !== '_' && $reflection->hasProperty($key)){
                        if ($reflection->hasMethod('set_'.$key)){
                            $this->{'set_'.$key}($value['atribut']);
                        }else{
                            $this->$key = $value;
                        }
                    }
                }
            }
            return $this;
        }

        public function set_table($table){
            $this->table = $table;
            $field_data = $this->_CI->db->field_data($this->table);

            foreach ($field_data as $key => $value) {
                if($value->primary_key == 1){
                    $this->primary_key = $value->name;
                }
                break;
            }

        }

        public function set_controller($controller){
            $this->controller = $controller;
        }
        
        public function set_session_key($session_key){
            $this->session_key = $session_key;
        }

        public function set_session_value($session_value){
            $this->session_value = $session_value;
        }

        public function generate_insert_modal(){
            $this->set_form_config('insert');

            $form_atribut = array(
                "novalidate" => "novalidate", 
                "id" => 'formInsert'.$this->to_camel_case($this->table),
                "action" => "<?= base_url('".$this->controller."/create')?>",
				"class" => "needs-validation"
            );
            

            $modal ='
			<button type="button" class="btn btn-outline-primary mb-4 mt-0" data-bs-toggle="modal" 
				data-bs-target="#modalInsert'.$this->to_camel_case($this->table).'">
				Tambah '.ucwords(str_replace('_', ' ',$this->to_singular($this->table))).'
			</button>
            <div class="modal fade" id="modalInsert'.$this->to_camel_case($this->table).'" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">';

            $modal .= form_open(" ", $form_atribut);

            $modal .= '
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah '.str_replace('_', ' ', $this->table).'</h5>
                            <button type="button" class="btn btn-link m-0" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">';

            foreach ($this->form_config as $key => $value) {
                if(strpos($value['atribut']['name'], 'id_'.$this->table) === FALSE){
                    $label = form_label(ucwords(str_replace('_', ' ', $this->create_label_from_name($value['atribut']['name']))), $value['atribut']['id']);
                    if($value['atribut']['type'] == 'text' || 
                       $value['atribut']['type'] == 'number' ||  
                       $value['atribut']['type'] == 'date'){
                        $input = form_input($value['atribut']);
                    }elseif ($value['atribut']['type'] == 'text') {
                        $input = form_input($value['atribut']);
                    }elseif ($value['atribut']['type'] == 'radio') {
            
                    }elseif ($value['atribut']['type'] == 'checkbox') {
            
                    }elseif($value['atribut']['type'] == 'dropdown'){
                        $options = $value['data']['options']; 
                        $input = form_dropdown($value['atribut']['name'], $options, null, $value['atribut']);
                    }elseif($value['atribut']['type'] == 'hidden'){
                        $input = form_hidden($value['atribut']);
                    }
                    $input .= '                    <small class="text-danger"><?= form_error(\''.$value['atribut']['name'].'\'); ?></small>';
                    $modal .= $this->create_stacked_input($input, $label);
                }else{
                    $modal .= form_hidden($value['atribut']['name'], $value['atribut']['value']);
                }
                
            }
            

            $modal .= '
                        </div>
                        <div class="modal-footer">
							<button class="btn btn-outline-default mb-0 me-2" data-dismiss="modal">Tutup</button>
							<button class="btn btn-primary m-0" type="submit">Tambah '.str_replace('_', ' ', $this->to_singular($this->table)).'</button>
                        </div>
                        ';

            $modal .= form_close();

            $modal .= '
                    </div>
                </div>
            </div>';
            return $modal;
        }

        public function generate_edit_modal(){
            $this->set_form_config('edit');

            $form_atribut = array(
                "novalidate" => "novalidate", 
                "id" => 'formEdit'.$this->to_camel_case($this->table),
                "action" => "<?= base_url('".$this->controller."/update/$".$this->table."->id_'".$this->table.")?>"
            );
            

            $modal ='
            <div class="modal fade" id="modalEdit'.$this->to_camel_case($this->table).'" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">';

            $modal .= form_open(" ", $form_atribut);

            $modal .= '
                        <div class="modal-header">
                            <h5 class="modal-title">Edit '.str_replace('_', ' ', $this->table).'</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">';

            foreach ($this->form_config as $key => $value) {
                if(strpos($value['atribut']['name'], 'id_'.$this->table) === FALSE){
                    $label = form_label(ucwords(str_replace('_', ' ', $this->create_label_from_name($value['atribut']['name']))), $value['atribut']['id']);
                    if($value['atribut']['type'] == 'text' || 
                       $value['atribut']['type'] == 'number' ||  
                       $value['atribut']['type'] == 'date'){
                        $input = form_input($value['atribut']);
                    }elseif ($value['atribut']['type'] == 'text') {
                        $input = form_input($value['atribut']);
                    }elseif ($value['atribut']['type'] == 'radio') {
            
                    }elseif ($value['atribut']['type'] == 'checkbox') {
            
                    }elseif($value['atribut']['type'] == 'dropdown'){
                        $options = $value['data']['options']; 
                        $input = form_dropdown($value['atribut']['name'], $options, null, $value['atribut']);
                    }elseif($value['atribut']['type'] == 'hidden'){
                        $input = form_hidden($value['atribut']);
                    }
                    $input .= '                    <small class="text-danger"><?= form_error(\''.$value['atribut']['name'].'\'); ?></small>';
                    $modal .= $this->create_stacked_input($input, $label);
                }else{
                    $modal .= form_hidden($value['atribut']['name'], $value['atribut']['value']);
                }
                
            }
            

            $modal .= '
                        </div>
                        <div class="modal-footer">
                            '.form_reset(null, 'Reset', 'class="btn btn-outline-primary"').'
                            '.form_submit(null, 'Submit', 'class="btn btn-primary"').'
                        </div>
                        ';

            $modal .= form_close();

            $modal .= '
                    </div>
                </div>
            </div>';
            return $modal;
        }

        public function generate_form(array $new_form_config = array(), $form_type = 'stacked', $link = null){

            $this->set_form_config('insert');
            if($new_form_config == NULL){
                //APABILA USER TIDAK MENSPESIFIKKAN FORM CONFIG MAKA AKAN MENGGUNAKAN CONFIG DEFAULT
                if($this->form_config == null){
                    //MELAKUKAN SET DEFAULT form_config
                    $this->set_form_config();
                }
            }else{
                $this->form_config = $new_form_config;
            }

            $form_atribut = array(
                "novalidate" => "novalidate", 
                "id" => 'formInsert'.$this->to_camel_case($this->table),
                "action" => "<?= base_url('".$this->controller."/insert-".$this->to_hypens($this->table)."')?>"
            );
            $this->form .= form_open(" ", $form_atribut);
            
            foreach ($this->form_config as $key => $value) {
                if(strpos($value['atribut']['name'], 'id_'.$this->table) === FALSE){
                    $label = form_label($this->create_label_from_name($value['atribut']['name']), $value['atribut']['id']);
                    if($value['atribut']['type'] == 'text' || 
                       $value['atribut']['type'] == 'number' ||  
                       $value['atribut']['type'] == 'date'){
                        $input = form_input($value['atribut']);
                    }elseif ($value['atribut']['type'] == 'text') {
                        $input = form_input($value['atribut']);
                    }elseif ($value['atribut']['type'] == 'radio') {
            
                    }elseif ($value['atribut']['type'] == 'checkbox') {
            
                    }elseif($value['atribut']['type'] == 'dropdown'){
                        $options = $value['data']['options']; 
                        $input = form_dropdown($value['atribut']['name'], $options, null, $value['atribut']);
                    }elseif($value['atribut']['type'] == 'hidden'){
                        $input = form_hidden($value['atribut']);
                    }
                    $input .= '                    <small class="text-danger"><?= form_error(\''.$value['atribut']['name'].'\'); ?></small>';
                    $this->form .= $this->create_stacked_input($input, $label);
                }
            }
            
            $this->form .= form_submit(null, 'Submit', 'class="btn btn-primary"');
            $this->form .= form_reset(null, 'Reset', 'class="btn btn-primary btn-link"');
            $this->form .= form_close();
            return $this->form;
        }

        public function retreive_form_config(){
            return $this->form_config;
        }

        public function get_possible_enum($table = '', $field = ''){
            $enums = array();
            if ($table == '' || $field == ''){
                return $enums;
            }else{
                preg_match_all("/'(.*?)'/", $this->_CI->db->query("SHOW COLUMNS FROM {$table} LIKE '{$field}'")->row()->Type, $matches);
                foreach ($matches[1] as $key => $value) {
                    $enums[$value] = ucwords(str_replace('_', ' ', $value)); 
                }
                return $enums;
            }
        }  

        public function create_stacked_input($input, $label){
            $stacked_input = '
                <div class="mb-2">
                    '.$label.'
                    '.trim($input).'
					<div class="invalid-feedback">
						Wajib diisi
					</div>
                </div>
            ';
            return $stacked_input;
        }

        public function set_form_config($prefix = 'insert'){
            $this->form_config = NULL;

            //FUNGSI INI DIGUNAKAN UNTUK MEMBUAT FORM CONFIG DARI SEBUAH TABLE DENGAN SETINGAN DEFAULT
            if($this->table == NULL || $this->table == ''){
                $this->error_msg[] = "Tidak ada table yang dipilih";
                return false;
            }

            $field_data = $this->_CI->db->field_data($this->table);
                
            foreach ($field_data as $key => $value) {
 
                $atribut = array(
                    'name'          => $value->name,
                    'id'            => $prefix.'_'.$this->table.'_'.$value->name,
                    'class'         => 'form-control',
                    'value'         => '<?= (!empty($data_'.$this->table.'->'.$value->name.'))? $data_'.$this->table.'->'.$value->name.' : set_value(\''.$prefix.'_'.$value->name.'\'); ?>',
                    'required'      => "true"
                );


                if(
                    $value->primary_key == 0 &&
                    strpos($value->name, 'id') === FALSE
                ){
                    if($value->type == 'varchar'){
                        $atribut['type'] = 'text';
                    }elseif ($value->type == 'int') {
                        $atribut['min'] = '0';
                        $atribut['type'] = 'number';
                    }elseif ($value->type == 'text') {
                        $atribut['type'] = 'text';
                    }elseif ($value->type == 'boolean') {
    
                    }elseif ($value->type == 'date') {
                        $atribut['type'] = 'text';
                        $atribut['class'] = 'form-control datepicker';
                    }elseif($value->type == 'enum'){
                        $atribut['type'] = 'dropdown';
                        $data['options'] = $this->get_possible_enum($this->table, $value->name);
                        $this->form_config[$value->name]["data"] =  $data;
                    }else{
                        //untuk jenis kolom lain seperti timestamp
                        continue;
                    }

                }else{
                    $atribut['type'] = 'number';
                }
                
                $this->form_config[$value->name]["atribut"] =  $atribut;
            }
        }

        public function set_laravel_form_config($prefix = 'insert'){
            $this->form_config = NULL;

            //FUNGSI INI DIGUNAKAN UNTUK MEMBUAT FORM CONFIG DARI SEBUAH TABLE DENGAN SETINGAN DEFAULT
            if($this->table == NULL || $this->table == ''){
                $this->error_msg[] = "Tidak ada table yang dipilih";
                return false;
            }

            $field_data = $this->_CI->db->field_data($this->table);
                
            foreach ($field_data as $key => $value) {
 
                $atribut = array(
                    'name'          => $prefix.'_'.$value->name,
                    'id'            => $prefix.'_'.$this->table.'_'.$value->name,
                    'class'         => 'form-control',
                    'value'         => "{{ old('".$value->name."') }}",
                    'required'      => "true"
                );


                if(
                    $value->primary_key == 0 &&
                    strpos($value->name, 'id') === FALSE
                ){
                    if($value->type == 'varchar'){
                        $atribut['type'] = 'text';
                    }elseif ($value->type == 'int') {
                        $atribut['min'] = '0';
                        $atribut['type'] = 'number';
                    }elseif ($value->type == 'text') {
                        $atribut['type'] = 'text';
                    }elseif ($value->type == 'boolean') {
    
                    }elseif ($value->type == 'date') {
                        $atribut['type'] = 'text';
                        $atribut['class'] = 'form-control datepicker';
                    }elseif($value->type == 'enum'){
                        $atribut['type'] = 'dropdown';
                        $data['options'] = $this->get_possible_enum($this->table, $value->name);
                        $this->form_config[$value->name]["data"] =  $data;
                    }else{
                        //untuk jenis kolom lain seperti timestamp
                        continue;
                    }

                }else{
                    $atribut['type'] = 'number';
                }
                
                $this->form_config[$value->name]["atribut"] =  $atribut;
            }
        }

        public function generate_form_validation(array $new_form_config = array(), $laravel = FALSE){
            
            if($new_form_config == null){
                if($this->form_config == null){
                    //MELAKUKAN SET DEFAULT form_config
                    $this->set_form_config();
                }
            }else{
                $this->form_config = $new_form_config;
            }

            if($laravel == FALSE)
            {
                $form_validation = '
                $config = array(';
                foreach ($this->form_config as $key => $value) {
                    if(strpos($value['atribut']['name'], 'id_'.$this->table) === FALSE){
    
                        $label = ucwords(str_replace('_', ' ', $this->create_label_from_name($value['atribut']['name'])));
                        if($value['atribut']['type'] == 'email'){
                            $form_validation .= "
                                    array(
                                        'field' => '{$value['atribut']['name']}',
                                        'label' => '{$label}',
                                        'rules' => 'trim|required|valid_email'
                                    )";
                        }else{
                            $form_validation .= "
                                    array(
                                        'field' => '{$value['atribut']['name']}',
                                        'label' => '{$label}',
                                        'rules' => 'required'
                                    )";
                        }
        
                        if(array_key_last($this->form_config) != $key){
                            $form_validation .= ',';
                        }
                    }
                        
                }
                $form_validation .= '
                            );';
    
                return $form_validation;

            }else
            {
                $form_validation = '$request->validate([';
                
                foreach ($this->form_config as $key => $value) {
                    $form_validation .= "
                        '{$value['atribut']['name']}'=>'required',";
                }
                
                $form_validation .= '
                    ]);';
                return $form_validation;
            }
        }
                
        public function generate_array_model(array $new_form_config = array(), $laravel = FALSE, $edit_laravel = FALSE){
           
            if($new_form_config == null){
                if($this->form_config == null){
                    //MELAKUKAN SET DEFAULT form_config
                    $this->set_form_config();
                }
            }else{
                $this->form_config = $new_form_config;
            }

            if($laravel == FALSE)
            {

                $array_model = '
                $array_model = array(';
                foreach ($this->form_config as $key => $value) {
                    $input_name = $value['atribut']['name'];
                    $column_name = $this->convert_input_name_to_column($input_name);
    
                    $array_model .= '
                                \''.$column_name.'\' => $this->input->post(\''.$input_name.'\', TRUE)';
    
                    if(array_key_last($this->form_config) != $key){
                        $array_model .= ',';
                    }
                }
                $array_model .= '
                            );';
                
                return $array_model;
            }else
            {
                if($edit_laravel == FALSE)
                {
                    $array_model = "
                    \$$this->table = new ".ucwords($this->table)."([
                    ";
                }else{
                    $array_model = "
                    \$$this->table = ".ucwords($this->table)."::find(\$id)([
                    ";
                }

                foreach ($this->form_config as $key => $value) {
                    $input_name = $value['atribut']['name'];
                    $column_name = $this->convert_input_name_to_column($input_name);
    
                    $array_model .= "
                                '$input_name' => \$request->get('$column_name'),";
                }

                $array_model .= '
                            ]);';
                
                return $array_model;
            }
        }

        public function generate_insert_controller($array_model = true){
            
            $this->set_form_config('insert');

            $controller = '
            public function insert_'.$this->table.'()
            {
                if($this->session->userdata(\''.$this->session_key.'\') == \''.$this->session_value.'\')
                {';

            $string_form_validation = $this->generate_form_validation();
            
            
            $controller .= '        '.$string_form_validation;
            $controller .= '
                    
                    $this->form_validation->set_rules($config);
                    if($this->form_validation->run($this) == TRUE) 
                    {';

            $controller .= $this->generate_array_model();
            
            $controller .= '
                            if($this->Base_model->insert(\''.$this->table.'\', $array_model) !== false)
                            {
                                $array = array(
                                    \'status\' => \'success\',
                                    \'message\' => \'Berhasil Input Data\'
                                );
                            }else{
                                $array = array(
                                    \'status\' => \'failed\',
                                    \'message\' => \'Gagal Input Data\'
                                );
                            }
                            $this->session->set_flashdata($array);
                            redirect(\''.$this->controller.'/'.$this->to_hypens($this->table).'\');
                    }else{
                        $this->'.$this->table.'();
                    }
                    
                }else
                {
                    redirect(\'account\');
                }
            }
            ';
            return $controller;
        }

        public function generate_laravel_insert_controller($array_model = true){
            $this->set_form_config('insert');

            $controller = '
                //store
            ';

            $string_form_validation = $this->generate_form_validation(array(), TRUE);
            
            
            $controller .= '        '.$string_form_validation;


            $controller .= $this->generate_array_model(array(), TRUE);
            
            $controller .= ucwords($this->table).'::create($request->all());';
            $controller .= $this->table.'->save();';
            return $controller;
        }

        public function generate_delete_controller($form_validation = true, $array_model = true){
            $controller = '
            public function delete_'.$this->table.'($'.$this->primary_key.')
            {
                if($this->session->userdata(\''.$this->session_key.'\') == \''.$this->session_value.'\')
                {
                    if($'.$this->primary_key.' !== NULL)
                    {
                        $delete = $this->Base_model->delete(\''.$this->table.'\',  array(\''.$this->primary_key.'\'=> $'.$this->primary_key.') );
                        if($delete == true)
                        {
                            $array = array(
                                \'status\' => \'success\',
                                \'message\' => \'Berhasil Hapus Data\'
                            );
                        }else{
                            $array = array(
                                \'status\' => \'failed\',
                                \'message\' => $delete[\'message\']
                            );
                        }
                    }else
                    {
                        $array = array(
                            \'status\' => \'failed\',
                            \'message\' => \'Gagal Hapus Data\'
                        );
                    }

                    $this->session->set_flashdata($array);
                    redirect(\''.$this->controller.'/'.$this->table.'\');
                }else
                {
                    redirect(\'account\');
                }
            }
            ';
            return $controller;
        }
        
        public function generate_edit_controller($array_model = true){
            
            $this->set_form_config('edit');
            $controller = '
            public function edit_'.$this->table.'()
            {
                if($this->session->userdata(\''.$this->session_key.'\') == \''.$this->session_value.'\')
                {';

            $string_form_validation = $this->generate_form_validation();
            
            
            $controller .= '        '.$string_form_validation;
            $controller .= '
                    
                    $this->form_validation->set_rules($config);
                    if($this->form_validation->run($this) == TRUE) 
                    {';

            $controller .= $this->generate_array_model();
            
            $controller .= '
                            $'.$this->primary_key.' = $this->input->post(\'edit_'.$this->primary_key.'\', TRUE);
                            if($this->Base_model->edit(\''.$this->table.'\', array("'.$this->primary_key.'" => $'.$this->primary_key.'), $array_model) == TRUE)
                            {
                                $array = array(
                                    \'status\' => \'success\',
                                    \'message\' => \'Berhasil Edit Data\'
                                );
                            }else{
                                $array = array(
                                    \'status\' => \'failed\',
                                    \'message\' => \'Gagal Edit Data\'
                                );
                            }
                            $this->session->set_flashdata($array);
                            redirect(\''.$this->controller.'/'.$this->to_hypens($this->table).'\');
                    }else{
                        $this->'.$this->table.'();
                    }
                    
                }else
                {
                    redirect(\'account\');
                }
            }
            ';
            return $controller;
        }
        
        public function generate_laravel_edit_controller($array_model = true){
            
            $this->set_form_config('edit');

            $controller = '
                //UPDATE
            ';

            $string_form_validation = $this->generate_form_validation(array(), TRUE);
            
            
            $controller .= '        '.$string_form_validation;

            $controller .= $this->generate_array_model(array(), TRUE, TRUE);
            
            $controller .= "
            ".$this->table.'->save();';
            return $controller;
        }

        public function generate_get_specific_controller($array_model = true){
            $controller = '
            public function get_specific_'.$this->table.'()
            {
                if($this->session->userdata(\''.$this->session_key.'\') == \''.$this->session_value.'\')
                {   
                    $result = $this->Base_model->get_specific(\''.$this->table.'\',  array(\''.$this->primary_key.'\'=> $this->uri->segment(3)));
                    echo json_encode($result);
                }else
                {
                    redirect(\'account\');
                }
            }
            ';
            return $controller;
        }

        public function generate_view_controller($array_model = true){
            $controller = '
            public function '.$this->table.'()
            {
                if($this->session->userdata(\''.$this->session_key.'\') == \''.$this->session_value.'\')
                {   
                    $data[\'data_'.$this->table.'\'] = \'\';
                    $data[\'main_view\'] = \''.$this->table.'\';

                    $this->load->view(\'template\', $data);
                }else
                {
                    redirect(\'account\');
                }
            }
            ';
            return $controller;
        }

        public function generate_table(array $new_form_config = array(), $laravel = FALSE){
            if($new_form_config == null){
                if($this->form_config == null){
                    //MELAKUKAN SET DEFAULT form_config
                    $this->set_form_config();
                }
            }else{
                $this->form_config = $new_form_config;
            }

            if($laravel == FALSE)
            {
                $table = '
                <table class="table" id="tabel'.$this->to_camel_case($this->table).'">
                        <thead>
                            <tr>';
                    $table .= "
                                <td>No</td>";
                foreach ($this->form_config as $key => $value) {
                        $label = $this->create_label_from_name($value['atribut']['name']);
                        $table .= "
                                <td>$label</td>";
                }
                $table .= "
                                <td></td>";
    
                $table .= '
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data_'.$this->table.' as $'.$this->table.'):?>
                                <tr>';
    
                    $table .= "
                                <td></td>";
                foreach ($this->form_config as $key => $value) {
                    $table .= '
                                    <td><?= $'.$this->table.'->'.$this->convert_input_name_to_column($value['atribut']['name']).'?></td>';
                }
                
                $table .= '         <td>                            <div class="dropstart">
                            <button type="button" id="dropdown<?= $'.$this->table.'->id_'.$this->table.'?>" class="btn btn-default m-0 font-weight-normal shadow-none" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu shadow-lg">
                                <li class="dropdown-item">
                                    <a class="btn btn-default shadow-none m-0 w-100 text-start" href="<?= base_url('.$this->table.'/$'.$this->table.'->id_'.$this->table.') ?>">Edit</a>
                                </li>
                                <li class="dropdown-item">
                                    <form action="<?= base_url('.$this->table.'/delete/$'.$this->table.'->id_'.$this->table.') ?>" method="post">
                                        <button type="button" class="btn btn-default shadow-none m-0 w-100 text-start" onclick="confirm_submit(this, \'Data '.$this->table.'  akan dihapus ! \')">Hapus</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
                    ';
    
                return $table;
            }else
            {

                $table = '
                <table class="table table-striped" id="tabel'.$this->to_camel_case($this->table).'">
                        <thead>
                            <tr>';
                    $table .= "
                                <td>No</td>";
                foreach ($this->form_config as $key => $value) {
                        $label = $this->create_label_from_name($value['atribut']['name']);
                        $table .= "
                                <td>$label</td>";
                }
                $table .= "
                                <td></td>";
    
                $table .= '
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($'.$this->table.' as $'.$this->to_singular($this->table).')
                                <tr>';
    
                    $table .= "
                                <td></td>";
                foreach ($this->form_config as $key => $value) {
                    $table .= '
                                    <td>{{ $'.$this->to_singular($this->table).'->'.$this->convert_input_name_to_column($value['atribut']['name']).' }}</td>';
                }
                
                $table .= '         
                                    <td>
                                        <form action="{{ route(\''.$this->to_singular($this->table).'.destroy\', $'.$this->to_singular($this->table).'->'.$this->to_singular($this->table).'_id) }}" method="POST" onsubmit="delete'.$this->to_camel_case($this->table).'()">
                                            @csrf
                                            @method(\'DELETE\')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                        <button class="btn btn-info btn-sm"  onclick="openModal'.$this->to_camel_case($this->table).'({ $'.$this->table.'->'.$this->to_singular($this->table).'_id }})">
                                            <i class="material-icons"> 
                                            create
                                            </i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    ';
    
                return $table;
            }
        }

        function generate_javascript(array $new_form_config = array() ){
            if($new_form_config == null){
                if($this->form_config == null){
                    //MELAKUKAN SET DEFAULT form_config
                    $this->set_form_config();
                }
            }else{
                $this->form_config = $new_form_config;
            }

            $javascript = '
            <script>
                $(document).ready(function () {
                    var tabel'.$this->to_camel_case($this->table).' = $(\'#tabel'.$this->to_camel_case($this->table).'\').DataTable({
                        "pagingType": "full_numbers",
                        "lengthMenu": [
                            [10, 25, 50, -1],
                            [10, 25, 50, "All"]
                        ],
                        "columnDefs": [
                            { "width": "10%", "targets": -1 }
                        ],
                        responsive: false
                    });

                    tabel'.$this->to_camel_case($this->table).'.on(\'order.dt search.dt\', function () {
                        tabel'.$this->to_camel_case($this->table).'.column(0, {search:\'applied\', order:\'applied\'}).nodes().each( function (cell, i) {
                            cell.innerHTML = i+1;
                        } );
                    } ).draw();

                    $(\'#formInsert'.$this->to_camel_case($this->table).'\').validate();
                    $(\'#formEdit'.$this->to_camel_case($this->table).'\').validate();
                });

            </script>
            ';
            return $javascript;
        }
        
        public function to_camel_case($string){
            $string = str_replace('_', ' ', $string);
            $string = ucwords($string);
            $string = str_replace(' ', '', $string);
            return $string;
        }

        public function create_label_from_name($name){
            $name = str_replace('insert', '', $name);
            $name = str_replace('edit', '', $name);
            $name = str_replace('_', ' ', $name);
            $name = ucwords($name);
            return $name;
        }

        public function convert_input_name_to_column($name){
            $name = str_replace('insert_', '', $name);
            $name = str_replace('edit_', '', $name);
            return $name;
        }
        
        public function to_hypens($name){
            return str_replace('_', '-', $name);
        }

        public function to_singular($name){
            if(substr($name, -1) == 's')
            {
                return substr_replace($name, "", -1);
            }
        }
    }
