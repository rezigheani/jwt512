<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';
require_once APPPATH . '/libraries/REST_Controller.php';

//uncomment di bawah ini atau gunakan autoload yang di config->config->composer_autoload default ada di composer_autoload
//require_once FCPATH . 'vendor/autoload.php';

use Restserver\Libraries\REST_Controller;

class Restdata extends REST_Controller{

  private $secretkey = 'ini rahasia untuk encode dan decode';

  public function __construct(){
    parent::__construct();

    $this->load->library('form_validation');
  }


  //method untuk not found 404
  public function notfound($pesan){

    $this->response([
      'status'=>FALSE,
      'message'=>$pesan
    ],REST_Controller::HTTP_NOT_FOUND);

  }

  //method untuk bad request 400
  public function badreq($pesan){
    $this->response([
      'status'=>FALSE,
      'message'=>$pesan
    ],REST_Controller::HTTP_BAD_REQUEST);
  }

  //method untuk melihat token pada user
  public function getToken_post(){


    $this->load->model('model_login');

    $date = new DateTime();

    $username = $this->post('username',TRUE);
    $pass = $this->post('password',TRUE);

    $dataadmin = $this->model_login->is_valid($username);

    if ($dataadmin) {

      if (password_verify($pass,$dataadmin->password)) {

        $resource = openssl_pkey_new([
            'digest_alg'       => 'sha512',
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $privateKey = "rahasia";

        openssl_pkey_export($resource, $privateKey);

        $publicKey = openssl_pkey_get_details($resource)['key'];

        /**
         * Membuat token akses user.
         */
        $startTime = microtime(true);

        $jwt = new \Lindelius\JWT\JWT('RS512');
        $jwt->kd_pegawai = $dataadmin->kd_pegawai;
        $jwt->username = $dataadmin->username;
        $jwt->iat = $date->getTimestamp(); //waktu di buat
        $jwt->exp = $date->getTimestamp() + 2629746; //satu bulan

        $output['id_token'] = $jwt->encode($this->secretkey);
        // var_dump($output['id_token']);
        // $this->response($output,'HTTP_OK');

      }else {

        $this->viewtokenfail($username,$pass);

      }

    }else {
      $this->viewtokenfail($username,$pass);
    }

  }

  //method untuk jika view token diatas fail
  public function viewtokenfail($username,$pass){
    $this->response([
      'status'=>FALSE,
      'username'=>$username,
      'password'=>$pass,
      'message'=>'USERNAME ATAU PASSWORD SALAH, SILAHKAN LOGIN KEMBALI'
      ],HTTP_BAD_REQUEST);
  }

//method untuk mengecek token setiap melakukan post, put, etc
  public function cektoken(){
    $this->load->model('model_login');
    $jwt = $this->input->get_request_header('Token');

    try {

      $decode = JWT::decode($jwt,$this->secretkey,array('HS512'));
      //melakukan pengecekan database, jika nama tersedia di database maka return true
      if ($this->model_login->is_valid_num($decode->username)>0) {
        return true;
      }

    } catch (Exception $e) {
      exit('TOKEN SALAH SILAHKAN MASUKKAN TOKEN DENGAN BENAR');
    }


  }




}
