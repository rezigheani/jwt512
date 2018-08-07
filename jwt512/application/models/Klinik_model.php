<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Klinik_model extends CI_Model{

  public function __construct()
  {
    parent::__construct();
    $this->load->database();
  }

  public function selectBarang(){
    $this->db->select('*');
    $this->db->from('tbl_barang');
    $query = $this->db->get();
    return $query->result();
  }

  public function selectBarangWhere($id){
    $this->db->select('*');
    $this->db->from('tbl_barang');
    $this->db->where('id_barang',$id);
    $query = $this->db->get();
    return $query->row();
  }

  public function insertBarang($data){
    if ($this->db->insert('tbl_barang',$data)) {
      return true;
    }
  }

  public function updateBarang($id,$data){
    $this->db->set($data);
    $this->db->where('id_barang',$id);
    if ($this->db->update('tbl_barang')) {
      return true;
    }
  }

  public function deletBarang($id){
    $this->db->where('id_barang',$id);
    $this->db->delete('tbl_barang');
    if ($this->db->affected_rows()>0) {
      return true;
    }
  }

 
 

}
