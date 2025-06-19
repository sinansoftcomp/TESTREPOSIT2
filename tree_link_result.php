<?php
include($_SERVER['DOCUMENT_ROOT']."/bin/include/source/head.php");
$id=$_POST['id'];
if(isset($id) && !empty($id)){
$sql = "SELECT * FROM treeview_items where id='{$id}'";
$result = mysqli_query($db, $sql);
if($row=mysqli_fetch_array($result)){
echo $row['link'];
}
}
?> 