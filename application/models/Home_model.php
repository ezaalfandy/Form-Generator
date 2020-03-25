<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Home_model extends CI_Model {
    
        public function get_all_table(){
            return $this->db->list_tables();
        }

        public function get_field_data($table){
            return $this->db->field_data($table);
        }
    
    }
    
    /* End of file Home_model.php */
    
?>