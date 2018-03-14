<?php require_once('global/config.php');

if($_GET['act'] == 'del'){
	$db->Execute("Delete from REGISTRATION_FORM where ID = '$_GET[id]'");
	header("location: index.php?msg=Deleted successfully");
}

/*if(!empty($_POST)){
	unset($_POST['SAVE']);
	if($_GET['id'] > 0){
		//db_perform('REGISTRATION_FORM', $_POST, 'update', 'ID = "'.$_GET['id'].'"');
	}else{
		$sql="INSERT INTO REGISTRATION_FORM(NAME)VALUES('".mysql_real_escape_string($_POST['NAME'])."')";
		$result=mysql_query($sql);
		//db_perform('REGISTRATION_FORM', $_POST, 'insert');
		//$PK_DEMO = $db->insert_ID();
	}
	header("location: index.php?msg=saved successfully");
}*/

$res = $db->Execute("SELECT * FROM REGISTRATION_FORM where ID = '$_GET[id]'");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
        <title><?=$title?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta content="" name="description" />
        <meta content="themes-lab" name="author" />
	</head>
	<body>
	<div id="tbl_div">
		<form id="form1" name="form1" method="post" action="">
			<table>
				<tr>
					<td colspan="2"><?=$_GET['msg']?></td>
				</tr>
				<tr>
					<td>Name:</td>
					<td><input type="text" name="NAME" id="NAME" value="<?=$res->fields['NAME']?>" required /></td>
				</tr>
				<tr>
					<td>Address:</td>
					<td><input type="text" name="ADDRESS" id="ADDRESS" value="<?=$res->fields['ADDRESS']?>" required /></td>
				</tr>
				<tr>
					<td>city:</td>
					<td><input type="text" name="CITY" value="<?=$res->fields['CITY']?>" id="CITY" required /></td>
				</tr>
				<tr>
					<td></td>
					<td><button type="button" name="SAVE" id="SAVE" >SAVE</button></td>
				</tr>
			</table>
		</form>
		<? $result = $db->Execute("SELECT * FROM REGISTRATION_FORM where ACTIVE='1'"); ?>
		<table border="1">
			<tr>
				<td>Name</td>
				<td>Address</td>
				<td>City</td>
				<td>Action</td>
			</tr>
			<? while(!$result->EOF){ ?>
			<tr>
				<td><?=$result->fields['NAME']?></td>
				<td><?=$result->fields['ADDRESS']?></td>
				<td><?=$result->fields['CITY']?></td>
				<td><a href="index.php?id=<?=$result->fields['ID']?>&act=edit">Edit</a>&nbsp;&nbsp;<a href="index.php?id=<?=$result->fields['ID']?>&act=del" >Delete</a></td>
			</tr>
			<? $result->MoveNext();
			} ?>
		</table>
	</body>
	</div>
</html>
<script src="https://code.jquery.com/jquery-1.9.1.js"></script>
<script>
	jQuery(document).ready(function (e){
		jQuery("#SAVE").on("click", (function(e){
			e.preventDefault();
			jQuery.ajax({
			url: "save_data.php",
			type: "POST",
			data:  new FormData(document.getElementById("form1")),
			contentType: false,
			cache: false,
			processData:false,
			success: function(data){
				//$("#tbl_div").load("index.php");
				window.history.pushState('', '', 'index.php?id='+data);
				//$('#form1')[0].reset();
			},
			error: function(){} 	        
			});
		}))
	});
</script>