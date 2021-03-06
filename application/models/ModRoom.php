<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ModRoom extends CI_Model
{

   public function loadRoom()
   {
      $this->db->select('judul, deskripsi, kode_room, waktu_pembuatan, waktu_akhir, active');
      return $this->db->get_where('room', array('id_user' => $this->session->userdata('id_user')))->result();
   }

   public function loadSpecificRoom($code)
   {
      // detail informasi room
      // untuk vote atau lihat detail
      $this->db->select('*');
      $this->db->from('room r');
      $this->db->join('pilihan p', 'r.kode_room = p.kode_room');
      $this->db->where('r.kode_room', $code);
      return $this->db->get()->result();
   }

   public function loadChartDataSpecificRoom($code)
   {
      $this->db->select('count(v.id_pilihan) AS qty, p.nama_pilihan');
      $this->db->select("(SELECT COUNT(*) FROM voter v2 WHERE v2.kode_room = '$code') AS total");
      $this->db->from('pilihan p');
      $this->db->join('voter v', 'p.id_pilihan = v.id_pilihan', 'left');
      $this->db->where('p.kode_room', $code);
      $this->db->group_by('p.id_pilihan');
      return $this->db->get()->result();
   }

   public function checkSpecificRoom($code)
   {
      $query = $this->db->get_where('room', array('kode_room' => $code));
      return $query->num_rows() > 0 ? true : false;
   }

   public function checkRoomActive($code)
   {
      $this->db->select('active');
      $this->db->from('room');
      $this->db->where('kode_room', $code);
      return $this->db->get()->row()->active ? true : false;
   }

   private function checkRoomId($code)
   {
      $this->db->select('kode_room');
      $this->db->from('room');
      $this->db->where('kode_room', $code);
      return $this->db->get()->row();
   }

   public function checkUserVoted($code)
   {
      $checkData = array(
         'kode_room' => $this->checkRoomId($code)->kode_room,
         'id_user' => $this->session->userdata('id_user')
      );

      $query = $this->db->get_where('voter', $checkData);
      return $query->num_rows() > 0 ? true : false;
   }

   public function checkRoomEnded($code)
   {
      $date = new DateTime();
      $dateFormated = $date->format('Y-m-d');

      $this->db->select('waktu_akhir');
      $this->db->from('room');
      $this->db->where('kode_room', $code);
      $waktu_akhir = $this->db->get()->row()->waktu_akhir;

      return ($dateFormated > $waktu_akhir) ? true : false;
   }

   public function checkRoomCreator($code)
   {
      $checkData = array(
         'id_user' => $this->session->userdata('id_user'),
         'kode_room' => $code
      );

      $query = $this->db->get_where('room', $checkData);
      return $query->num_rows() > 0 ? true : false;
   }

   public function startVoteRoom($code)
   {
      $this->db->where('kode_room', $code);
      $this->db->update('room', array('active' => 1));
   }

   public function endVoteRoom($code)
   {
      $this->db->where('kode_room', $code);
      $this->db->update('room', array('active' => 0));
   }

   public function createVoteRoom($insertData, $pilihan)
   {
      $this->db->insert('room', $insertData);
      $this->db->insert_batch('pilihan', $pilihan);
   }

   public function insertVoter($insertData)
   {
      $this->db->insert('voter', $insertData);
   }

   public function getDataFiles($code)
   {
      $this->db->select("r.*, COUNT(v.id_pilihan) AS qty, p.nama_pilihan");
      $this->db->select("(SELECT COUNT(*) FROM voter v2 WHERE v2.kode_room = '$code') AS total");
      $this->db->from("pilihan p");
      $this->db->join('room r', 'r.kode_room = p.kode_room');
      $this->db->join('voter v', 'p.id_pilihan = v.id_pilihan', 'left');
      $this->db->where('p.kode_room', $code);
      $this->db->group_by('p.id_pilihan');
      return $this->db->get()->result();
   }

   public function checkExist($where, $table)
   {
      return $this->db->get_where($table, $where)->num_rows() > 0 ? true : false;
   }

   public function deleteVote($code)
   {
      $deleteRoom = array('voter', 'pilihan', 'room');
      $this->db->where('kode_room', $code);
      $this->db->delete($deleteRoom);
   }

   public function updateVote($insertData, $code)
   {
      $date = new DateTime();
      $dateFormated = $date->format('Y-m-d');

      if ($dateFormated <= $insertData['waktu_akhir']) {
         $insertData['active'] = 1;
      }

      $this->db->where('kode_room', $code);
      $this->db->update('room', $insertData);
   }

   public function getVoteDate($code)
   {
      $this->db->select('waktu_pembuatan, waktu_akhir');
      $this->db->from('room');
      $this->db->where('kode_room', $code);
      return $this->db->get()->result_array();
   }
}

/* End of file ModRoom.php */
