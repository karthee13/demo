<?php require_once('global/config.php');

if(!empty($_POST)){
	unset($_POST['SAVE']);
	if($_GET['id'] > 0){
		//db_perform('REGISTRATION_FORM', $_POST, 'update', 'ID = "'.$_GET['id'].'"');
	}else{
		$sql="INSERT INTO REGISTRATION_FORM(NAME)VALUES('".mysql_real_escape_string($_POST['NAME'])."')";
		$result=mysql_query($sql);
		$id = mysql_insert_id();
		echo $id;
	}
}
//echo "hi";

?>
