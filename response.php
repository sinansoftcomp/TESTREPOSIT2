
<?php
include($_SERVER['DOCUMENT_ROOT']."/bin/include/config.php");
include($_SERVER['DOCUMENT_ROOT']."/bin/include/dbConn.php");
 
 
 $tree_arr= array();
$tree_arr_tot= array();
 
unset($tree_arr); 
//-->본부 
$sql = "SELECT bcode,bname FROM BONBU WHERE scode = '".$_SESSION['S_SCODE']."'      order by num   ";
$res = sqlsrv_query( $mscon, $sql );
	//iterate on results row and create new index array of data
	while( $row = sqlsrv_fetch_array($res) ) { 
 		$tree_arr['id'] = 'N1'.$row["bcode"];  
		$tree_arr['parent'] =  "#";  
		$tree_arr['text'] =  iconv("EUCKR","UTF-8",$row["bname"]);  
		$tree_arr['icon'] =  "glyphicon glyphicon-home";  
		array_push($tree_arr_tot,$tree_arr) ;	}
 

unset($tree_arr); 
//--->지사
$sql = "SELECT a.jscode,a.jsname,a.upcode, b.bcode  FROM JISA a
				left outer join bonbu b on a.scode = b.scode and a.upcode = b.bcode  
				WHERE a.scode = '".$_SESSION['S_SCODE']."'  and  a.USEYN = '1'   order by a.num   ";
 

$res = sqlsrv_query( $mscon, $sql );
	while( $row = sqlsrv_fetch_array($res) ) { 
 		$tree_arr['id'] = 'N2'.$row["jscode"];     //--->지사
		if (str_replace(" ", "" ,$row["bcode"]) == ""    ||      is_null($row["bcode"]) ) {
			$tree_arr['parent'] = "#"; 
		}else{
			$tree_arr['parent'] = 'N1'.$row["upcode"]; 
		}
		$tree_arr['text'] =  iconv("EUCKR","UTF-8",$row["jsname"]);  
		$tree_arr['icon'] =  "glyphicon glyphicon-picture";  
		array_push($tree_arr_tot,$tree_arr) ;	}


unset($tree_arr); 
//--->지점
$sql = "SELECT a.jcode,a.jname, a.upcode, b.jscode, c.bcode   FROM JIJUM a
				left outer join jisa b on a.scode = b.scode and  a.upcode = b.jscode  
				left outer join bonbu c on a.scode = c.scode and b.upcode = c.bcode  
				WHERE a.scode = '".$_SESSION['S_SCODE']."'  and  a.USEYN = '1'   order by a.num   ";
 
 
$res = sqlsrv_query( $mscon, $sql );
	while( $row = sqlsrv_fetch_array($res) ) { 
 		$tree_arr['id'] = 'N3'.$row["jcode"];   //-->계층은 지점이다.
	
 		//-->본부 지사 	없다면  루트에 붙는다 
		if ( empty($row["jscode"]) &&   empty($row["bcode"])  ) {
			$tree_arr['parent'] = "#"; 
		}
		//-->지사가 	없다면 (본부에 붙는다) 
		if ( empty($row["jscode"])   ) {
			$tree_arr['parent'] = "#"; 
		}
		//-->지사가 	있다면
		if ( !empty($row["jscode"])   ) {
			$tree_arr['parent'] = 'N2'.$row["upcode"]; 
		}
		$tree_arr['text'] =  iconv("EUCKR","UTF-8",$row["jname"]);  
		$tree_arr['icon'] =  "glyphicon glyphicon-picture";  
		array_push($tree_arr_tot,$tree_arr) ;	}


unset($tree_arr); 
//--->팀 
$sql = "SELECT a.tcode,a.tname, a.upcode, b.jcode,  c.jscode, d.bcode   FROM TEAM a
				left outer join jijum b on a.scode = b.scode and a.upcode = b.jcode  
				left outer join jisa c on a.scode = c.scode and  b.upcode = c.jscode  
				left outer join bonbu d on a.scode = d.scode and c.upcode = d.bcode  
				WHERE a.scode = '".$_SESSION['S_SCODE']."'  and  a.USEYN = '1'   order by a.num   ";

$res = sqlsrv_query( $mscon, $sql );
	while( $row = sqlsrv_fetch_array($res) ) { 
 		$tree_arr['id'] = 'N4'.$row["tcode"];   //-->계층은 팀이다.
	
 		//-->본부 지사 	지점이  없다면  루트에 붙는다 
		if (empty($row["jcode"]) && empty($row["jscode"]) &&   empty($row["bcode"])  ) {
			$tree_arr['parent'] = "#"; 
		}
		//-->지사  지점이 	없다면 (본부에 붙는다) 
		if ( empty($row["jscode"]) && empty($row["jcode"])    ) {
			$tree_arr['parent'] = 'N1'.$row["bcode"]; 
		}
		//--> 지점이 	없다면 (지사 에 붙는다) 
		if (  empty($row["jcode"])    ) {
			$tree_arr['parent'] = 'N2'.$row["jscode"]; 
		}
		//-->지점가 	있다면
		if ( !empty($row["jcode"])   ) {
			$tree_arr['parent'] = 'N3'.$row["upcode"]; 
		}
		$tree_arr['text'] =  iconv("EUCKR","UTF-8",$row["tname"]);  
		$tree_arr['icon'] =  "glyphicon glyphicon-picture";  
		array_push($tree_arr_tot,$tree_arr) ;	}



unset($tree_arr); 
//--->사원 
$sql = "SELECT *   FROM  SWON a
				WHERE a.scode = '".$_SESSION['S_SCODE']."' ";

 

$res = sqlsrv_query( $mscon, $sql );
	while( $row = sqlsrv_fetch_array($res) ) { 
 		$tree_arr['id'] = 'N5'.$row["SKEY"];   //-->계층은 사원이다.
	
 		//-->본부 지사 	지점이 팀  없다면  루트에 붙는다 
		if (empty($row["BONBU"]) && empty($row["JISA"]) &&   empty($row["JIJUM"]) &&   empty($row["TEAM"])   ) {
			$tree_arr['parent'] = "#"; 
		}

		if (!empty($row["BONBU"])) {
				$tree_arr['parent'] = 'N1'.$row["BONBU"]; 
		}		
		if (!empty($row["JISA"])) {
				$tree_arr['parent'] = 'N2'.$row["JISA"]; 
		}	
		if (!empty($row["JIJUM"])) {
				$tree_arr['parent'] = 'N3'.$row["JIJUM"]; 
		}
		if ( !empty($row["TEAM"]))  {
 			$tree_arr['parent'] = 'N4'.$row["TEAM"]; 
		} 
 
		$tree_arr['text'] =  iconv("EUCKR","UTF-8",$row["SNAME"]);  
		$tree_arr['icon'] =  "glyphicon glyphicon-user";  
		array_push($tree_arr_tot,$tree_arr) ;	}





// Encode:
sqlsrv_free_stmt($result);
sqlsrv_close($mscon);
 
echo json_encode($tree_arr_tot);
 
?>
