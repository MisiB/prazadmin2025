<?php

namespace App\Interfaces\repositories;

interface iworkshopInterface
{
   public function getallworkshops($search=null);
   public function getworkshopbyid($id);
   public function getworkshopinvoices($workshop_id,$status=null,$currency_id=null);
   public function getworkshopinvoicebyid($id);
   public function updateworkshop($id,$data);
   public function createworkshop($data);
   public function deleteworkshop($id);
   public function getopenworkshops();
   public function viewworkshop($id);


   public function createorder($data);
   public function updateorder($id,$data);
   public function deleteorder($id);
   public function getorder($id);
   public function saveorderdocument($order_id,$data);
   public function getorders($workshop_id);
   public function getordersbyregnumber($regnumber);
   public function adddelegate($data);
   public function updatedelegate($id,$data);
   public function deletedelegate($id);
}
