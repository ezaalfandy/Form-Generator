<?php

    class Laravel_generator{

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

        public function generate_edit_modal(){
            $this->set_form_config('edit');

            $form_atribut = array(
                "novalidate" => "novalidate", 
                "id" => 'formEdit'.$this->to_camel_case($this->table),
                "action" => "<?= base_url('".$this->controller."/edit-".$this->to_hypens($this->table)."')?>"
            );
            

            $modal ='
            <div class="modal fade" id="modalEdit'.$this->to_camel_case($this->to_singular($this->table)).'" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">';

            
            $modal .= '
                        <form method="POST" novalidate="novalidate" id="formEdit'.$this->to_camel_case($this->to_singular($this->table)).'" action="{{ route(\''.$this->controller.'.update\') }} ">
                                @csrf
                                @method(\'PUT\')';

            $modal .= '
                            <div class="modal-header">
                                <h5 class="modal-title">Edit '.str_replace('_', ' ',$this->to_singular($this->table)).'</h5>
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
                    $input .= '                                     @error(\''.$value['atribut']['name'].'\')<small class="text-danger">{{ $message }}</small>@enderror';
                    $modal .= '     '.$this->create_stacked_input($input, $label);
                }else{
                    $modal .= form_hidden($value['atribut']['name'], $value['atribut']['value']);
                }
                
            }
            

            $modal .= '     
                            </div>
                            <div class="modal-footer">';
            $modal .= '         
                                <button class="btn btn-primary" type="submit">Insert '.str_replace('_', ' ', $this->to_singular($this->table)).'</button>
                            </div>';
            $modal .= "
                        </form>";
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
                "action" => $this->controller."@store"
            );
            
            $this->form = NULL;
            $this->form .= '<form method="POST" novalidate="novalidate" id="formInsert'.$this->to_camel_case($this->table).'" action="{{ route(\''.$this->controller.'.store\') }} ">
                            @csrf';
            
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
            
                    }elseif ($value['atribut']['type'] == 'textarea') {
                        $input =  htmlspecialchars(form_textarea($value['atribut']));
                        $input .= '
                                    <div class="fileinput fileinput-new text-center d-block" data-provides="fileinput">
                                        <div class="fileinput-new thumbnail">
                                            <img src="{{ asset(\'material\') }}/img/image_placeholder.jpg" alt="...">
                                        </div>
                                        <div class="fileinput-preview fileinput-exists thumbnail"></div>
                                        <div>
                                            <span class="btn btn-primary btn-link  btn-file">
                                                <span class="fileinput-new">Pilih Gambar</span>
                                                <span class="fileinput-exists">Ganti</span>
                                                <input type="file" name="'.$value['atribut']['name'].'" maxsize="2" extension="jpg|gif|png|jpeg">
                                            </span>
                                            <a  class="btn btn-danger btn-link fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>
                                        </div>
                                    </div>
                        ';
                    }elseif ($value['atribut']['type'] == 'checkbox') {
            
                    }elseif($value['atribut']['type'] == 'dropdown'){
                        $options = $value['data']['options'];
                        $value['atribut']['class'] = 'selectpicker w-100';
                        $value['atribut']['data-style'] = 'select-with-transition';

                        $input = form_dropdown($value['atribut']['name'], $options, null, $value['atribut']);
                    }elseif($value['atribut']['type'] == 'hidden'){
                        $input = form_hidden($value['atribut']);
                    }
                    $input .= '                                     @error(\''.$value['atribut']['name'].'\')<small class="text-danger">{{ $message }}</small>@enderror';
                    $this->form .= $this->create_stacked_input($input, $label);
                }
            }
            
            $this->form .= '    
                            <button class="btn btn-primary" type="submit">Insert '.str_replace('_', ' ', $this->to_singular($this->table)).'</button>';
            $this->form .= "
                        </form>";
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
                                <div class="form-group">
                                    '.$label.'
                                    '.trim($input).'
                                </div>';
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
                        $atribut['type'] = 'textarea';
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

        public function generate_form_validation(array $new_form_config = array()){
            
            if($new_form_config == null){
                if($this->form_config == null){
                    //MELAKUKAN SET DEFAULT form_config
                    $this->set_form_config();
                }
            }else{
                $this->form_config = $new_form_config;
            }

            $form_validation = '
                $request->validate([';
                
            foreach ($this->form_config as $key => $value) {
                $form_validation .= "
                    '{$value['atribut']['name']}'=>'required',";
            }
            
            $form_validation .= '
                ]);';
            return $form_validation;
        }
                
        public function generate_array_model(array $new_form_config = array()){
           
            if($new_form_config == null){
                if($this->form_config == null){
                    //MELAKUKAN SET DEFAULT form_config
                    $this->set_form_config();
                }
            }else{
                $this->form_config = $new_form_config;
            }

            $array_model ="";

            foreach ($this->form_config as $key => $value) {
                $input_name = $value['atribut']['name'];
                $column_name = $this->convert_input_name_to_column($input_name);

                $array_model .= "
                    '$input_name' => \$request->get('$column_name'),";
            }
            
            return $array_model;
        }

        public function generate_insert_controller($array_model = true){
            $this->set_form_config('insert');
            $string_form_validation = $this->generate_form_validation(array());
            $controller = $string_form_validation;
            
            $controller .= '
                $'.$this->to_singular($this->table).' = new "'.ucwords($this->to_singular($this->table)).'([';

            $controller .= '    '.$this->generate_array_model();
            

            $controller .= '
                ]);';
            $controller .= '
                '.ucwords($this->to_singular($this->table)).'::create($request->all());';
            $controller .= "
                return redirect()->route('".$this->to_singular($this->table).".index')
                ->with('success', '".ucwords($this->to_singular($this->table))." created successfully');";
        
            return $controller;
        }

        public function generate_delete_controller($form_validation = true, $array_model = true){
            
            return "
                $".$this->to_singular($this->table)."->delete();
                return redirect()->route('".$this->to_singular($this->table).".index')
                ->with('success', '".$this->to_singular($this->table)." berhasil dihapus');
            
            ";
            
        }
        
        
        public function generate_edit_controller($array_model = true){
            
            $this->set_form_config('edit');

            $string_form_validation = $this->generate_form_validation(array(), TRUE);
            
            
            $controller = '        '.$string_form_validation;

            $controller .= '
                $newData = array(';
            $controller .= $this->generate_array_model(array());
            $controller .= '
                );';
            
            $controller .= "
                $".$this->table."->update(\$newData);
                return redirect()->route('".$this->to_singular($this->table).".index')
                ->with('success', '".$this->table." berhasil diupdate');
                ";
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

        public function generate_table(array $new_form_config = array()){
            if($new_form_config == null){
                if($this->form_config == null){
                    //MELAKUKAN SET DEFAULT form_config
                    $this->set_form_config();
                }
            }else{
                $this->form_config = $new_form_config;
            }


            $table = '
            <table class="table table-striped" id="table'.$this->to_camel_case($this->table).'">
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
                            <td>Aksi</td>";

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
                                    <form class="d-inline-block" 
                                        action="{{ route(\''.$this->to_singular($this->table).'.destroy\', $'.$this->to_singular($this->table).'->'.$this->to_singular($this->table).'_id) }}" 
                                        method="POST" onsubmit="delete'.$this->to_camel_case($this->table).'()">
                                        @csrf
                                        @method(\'DELETE\')
                                        <button type="button" class="btn btn-link btn-danger  delete-form-'.$this->to_singular($this->table).'">Delete</button>
                                    </form>
                                    <button class="btn btn-info btn-link btn-sm"  
                                        onclick="openModal'.$this->to_camel_case($this->to_singular($this->table)).'(
                                            \'{{ route(\''.$this->to_singular($this->table).'.destroy\', $'.$this->to_singular($this->table).'->'.$this->to_singular($this->table).'_id) }}\'
                                        )"
                                    >
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                ';

            return $table;
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
                    var table'.$this->to_camel_case($this->table).' = $(\'#table'.$this->to_camel_case($this->table).'\').DataTable({
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

                    table'.$this->to_camel_case($this->table).'.on(\'order.dt search.dt\', function () {
                        table'.$this->to_camel_case($this->table).'.column(0, {search:\'applied\', order:\'applied\'}).nodes().each( function (cell, i) {
                            cell.innerHTML = i+1;
                        } );
                    } ).draw();

                    $(\'#formInsert'.$this->to_camel_case($this->table).'\').validate();
                    $(\'#formEdit'.$this->to_camel_case($this->table).'\').validate();
                });

                
                $(\'.btn-delete-'.$this->to_singular($this->table).'\').on(\'click\', function (e) {
                    e.preventDefault();
                    var form = $(this).parents(\'form\');
                    swal({
                        title: \'Apakah Anda Yakin ?\',
                        text: "Data '.$this->to_singular($this->table).' akan dihapus dan tidak dapat dikembalikan !",
                        type: \'warning\',
                        showCancelButton: true,
                        confirmButtonClass: \'btn btn-danger\',
                        cancelButtonClass: \'btn btn-default btn-link\',
                        confirmButtonText: \'Ya, Hapus\',
                        buttonsStyling: false
                    }).then(function(result) {
                        if(result.value === true){
                            $(form).submit();
                        }
                    })
                });

                function openModal'.$this->to_camel_case($this->to_singular($this->table)).'($url){
                    $.getJSON($url,
                        function (data, textStatus, jqXHR) {
                            $(\'#formEdit'.$this->to_camel_case($this->to_singular($this->table)).' .form-group\').addClass(\'is-filled\');';
                            foreach ($this->form_config as $k => $v) {
                                $column_name = $this->convert_input_name_to_column($v['atribut']['name']);
                                $javascript .= '
                            $(\'#formEdit'.$this->to_camel_case($this->to_singular($this->table)).' [name="'.$v['atribut']['name'].'"]\').val(data.'.$column_name.');';
                            }
            $javascript .= '
                            $(\'#modalEdit'.$this->to_camel_case($this->to_singular($this->table)).'\').modal(\'show\');
                        }
                    );  
                }
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
            if(substr($name, -2) == 'es')
            {
                return substr_replace($name, "", -2);
            }elseif(substr($name, -1) == 's')
            {
                return substr_replace($name, "", -1);
            }
        }
    }
    
?>