<?php
include_once(__ROOT__."/classes/MySQL.php");
include_once(__ROOT__."/classes/Partners.php");

try {
	$sql = new MySQL(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	$query = "SELECT id,id_user,date_payment,status,total FROM cells WHERE date_payment>'".date("Y-m-d H:i:s",time())."' AND total>199";
	$res = mysqli_query($sql->db,$query);
	while($myrow = mysqli_fetch_array($res)) {
		$cell = new Partners;
		$cell->ReloadStatus($myrow);
	}

} catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$arr = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
	echo json_encode($arr);
	exit;
}
?>