<meta charset="utf-8">
<?
include($_SERVER['DOCUMENT_ROOT']."/bin/include/dbConn2.php");

include_once('./ren_menu3_01_action_cho_insert_fun.php');   

// ��û����
$ADATE = $_GET['ADATE'];

$sql  = "SELECT YYMM, DD, JDATE, SDATE, KDAY, MBIT FROM SINAN_WATER.DBO.CMS_SCH WHERE SDATE = '".$ADATE."' ";
$result  = sqlsrv_query( $mscon, $sql );
$Cms_chk =  sqlsrv_fetch_array($result); 

$YYMM	= $Cms_chk['YYMM'];
$DD		= $Cms_chk['DD'];
$JDATE	= $Cms_chk['JDATE'];
$SDATE	= $Cms_chk['SDATE'];
$KDAY	= $Cms_chk['KDAY'];
$MBIT	= $Cms_chk['MBIT'];


// �ڵ���� ������ �����Ϸ����� ��� ����
if($MBIT == '2'){
	sqlsrv_free_stmt($result);
	sqlsrv_close($mscon);
	$message="�ش����� �ڵ���� ������ ������ �Ϸ�Ǿ����ϴ�.";
	echo "<script> alert('$message'); </script>";
	exit;
}

if($JDATE == '' || $JDATE == null || $JDATE == 'undefined' ){
	sqlsrv_free_stmt($result);
	sqlsrv_close($mscon);
	$message="������ �ڵ�������� �ƴմϴ�. ";
	echo "<script> alert('$message'); </script>";
	exit;	
}

if($DD == '' || $DD == null || $DD == 'undefined' ){
	sqlsrv_free_stmt($result);
	sqlsrv_close($mscon);
	$message="����� Ȯ�� �����Դϴ�. ";
	echo "<script> alert('$message'); </script>";
	exit;	
}

$where= "";
if($DD == '05'){
	$where = " AND A.AUTO_05 = 'Y' " ;
}else if($DD == '15'){
	$where = " AND A.AUTO_15 = 'Y' " ;
}else if($DD == '25'){
	$where = " AND A.AUTO_25 = 'Y' " ;
}else if($DD == '31'){
	$where = " AND A.AUTO_30 = 'Y' " ;
}

$listData	= array();
$sql = "SELECT SCODE, AUTO_BANG, UGUBUN, CNT
		FROM(
			select A.SCODE, a.AUTO_BANG, a.UGUBUN, d.cnt as CNT
			from SINAN_WATER.DBO.company a
				left outer join (select scode, COUNT(scode) cnt 
								 from kwn where KSBIT IN('1','7') and KBIT = '2' group by scode) d on d.scode = a.scode
			where a.AUTO_BIT = 'Y'
			  and a.AUTO_DON = 'Y'
			  AND A.UGUBUN IN('31')
			  and a.SCODE != 'rental33' ".$where." 
		) TBL
		WHERE TBL.CNT > 0
		order by SCODE  ";	

$qry	= sqlsrv_query( $mscon, $sql );
while( $fet = sqlsrv_fetch_array( $qry, SQLSRV_FETCH_ASSOC) ) {
	$listData[]	= $fet;
}

// Ʈ������ ����
sqlsrv_query("BEGIN TRAN");

foreach($listData	as $key => $val){

	$S_SCODE	= $val['SCODE'];
	$UGUBUN		= $val['UGUBUN'];
	$PROC_BIT	= $val['AUTO_BANG']; // ��ݹ��

	$sql  = "SELECT COUNT(*) TOTCNT  FROM TACMS WHERE SCODE = '".$S_SCODE."' AND ADATE = '".$ADATE."' ";
	$result  = sqlsrv_query( $mscon, $sql );
	$row =  sqlsrv_fetch_array($result); 

	// �ش��Ͽ� ��� �����Ͱ� ���� ��� �н�~
	if($row[TOTCNT] < 1){

		echo "SCODE:".$S_SCODE."<br>";

		//--->��� û��data�� ���� �ϴ°� ? (�������� ������ �ڵ����� �ؾ��ϴ����� �Ǵ��ϱ� ����)
		$YY = SUBSTR($JDATE,0,4);
		$MM = SUBSTR($JDATE,4,2);
		//$DD = SUBSTR($ADATE,6,2);

		//---�������ϱ� 
		$JUN_ADATE = date("Ymd", strtotime("-1 month", strtotime($JDATE)));
		$JUN_YY = SUBSTR($JUN_ADATE,0,4);
		$JUN_MM = SUBSTR($JUN_ADATE,4,2);
		$JUN_DD = '31';


		$sql  = "select count(*) LL_CNT  from cho where SCODE = '".$S_SCODE."' AND hyy  = '$YY' and  hmm = '$MM' AND CBIT = '1' ";
		$result  = sqlsrv_query( $mscon, $sql );
		$row =  sqlsrv_fetch_array($result); 

		If(!$row[LL_CNT]){
		   //--->���û��data�� �����Ƿ� ������ ���Ͽ� ���������ڷ�  û��data �����ϰ� 
			if (ren_menu3_01_action_cho_insert_fun($S_SCODE, $JUN_YY,$JUN_MM, $JUN_DD, $mscon)  !=  0 ) {
				sqlsrv_free_stmt($result);
				sqlsrv_close($mscon);
				$message= "���� û�� DATA������ ������ �߻��Ͽ����ϴ�.(".$S_SCODE.")";
				echo "<script> alert('$message'); </script>";
			}	
		}


		//echo " / Check: ".$S_SCODE."/".$YY."/".$MM."/".$DD."/".'<br>' ;

		//--->�����ü �Ƿ��� ���� ��û��data���� �� 
		$dataS = ren_menu3_01_action_cho_insert_fun($S_SCODE, $YY,$MM, $KDAY, $mscon);
		//echo " / Check: ".$S_SCODE."/".$YY."/".$MM."/".$DD."/".$mscon ;
		if ($dataS  !=  0 ) {
			sqlsrv_free_stmt($result);
			sqlsrv_close($mscon);
			$message= "��� û�� DATA������ ������ �߻��Ͽ����ϴ�.(".$S_SCODE.")";
			echo "<script> alert('$message'); </script>";
		}	

		//--->ȸ���������� �Ǵ���ü �ѵ���������. 
		$sql  = "select PAMT, CPAMT, PKCODE   from SINAN_WATER.DBO.COMPANY  where SCODE = '".$S_SCODE."'  ";
		$result  = sqlsrv_query( $mscon, $sql );
		$row =  sqlsrv_fetch_array($result); 

		$PAMT   =$row[PAMT];     // ���°Ǵ��ѵ�
		$CPAMT  =$row[CPAMT];    // ī��Ǵ��ѵ�
		$PKCODE =$row['PKCODE']; //�ŷ�óID


		If(!$PAMT || $PAMT < 1000  ){
			sqlsrv_free_stmt($result);
			sqlsrv_close($mscon);
			$message= "ȸ�������� cms�Ǵ���ü�ѵ��� �߸����ǵ�.(".$S_SCODE.")";
			echo "<script> alert('$message'); </script>";
		}
		 

		// ó����� : ������
		$SKEY  = '1001';
		$GCODE = $PKCODE;

		if(!$GCODE || $GCODE == '' || $GCODE == ' '){
			sqlsrv_free_stmt($result);
			sqlsrv_close($mscon);
			$message= "ȯ�漳�� > ���ȸ���������� �ŷ�óID �Է� �� �����Ͻñ� �ٶ��ϴ�.(".$S_SCODE.")";
			echo "<script> alert('$message'); </script>";
		} 


		$sql = "
				insert into TACMS(SCODE, NCODE, ADATE, ABNO, BANK, AJUMIN, AAMT, AABMT, AOUTAMT, ABIT, ABCODE, AIPBIT,
								  CRDTIME, CRSWON, KSBIT, IN_BIT)
				select  '".$S_SCODE."' , A.NCODE ,  '$ADATE'  ,  A.SKJNO  ,  A.BANK, A.SYJUNO , 
						CASE WHEN A.KSBIT = '1' AND ISNULL(CHO_AMT,0)  > " .$PAMT." THEN " .$PAMT.  " 
							 WHEN A.KSBIT = '7' AND ISNULL(CHO_AMT,0)  > " .$CPAMT." THEN " .$CPAMT.  " ELSE ISNULL(CHO_AMT,0)  END, 
						0,0, '2','','2', getdate(), '".$SKEY."', A.KSBIT, '1'                           
				from    kwn a left outer join
					(
							select last_cho.kcode code , isnull(last_cho.hjamt,0) hjamt,
										CASE  '$PROC_BIT'  WHEN '1' THEN 	 isnull(last_cho.hjamt,0)   +  isnull(last_cho.hamt1,0) + isnull(last_cho.hamt2,0)  -  isnull(cho_ip.ijamt,0)  -  isnull(cho_ip.IAMT1,0)-  isnull(cho_ip.IAMT2,0)  
																		WHEN '2' THEN 	isnull(last_cho.hjamt,0)   -  isnull(cho_ip.ijamt,0)   
																		WHEN '3' THEN  isnull(last_cho.hamt1,0)  +  isnull(last_cho.hamt2,0)  -    isnull(cho_ip.IAMT1,0)-  isnull(cho_ip.IAMT2,0)		
																		ELSE 0 END CHO_AMT 
							from ( select kcode , hyy , hmm , hjamt ,  hamt1, hamt2,   	row_number() over(partition by kcode order by hyy desc , hmm desc) cnt 
										from cho
										where SCODE = '".$S_SCODE."' ) last_cho left outer join
													(select KCODE,  ICYY , ICMM, SUM(IJAMT) IJAMT , SUM(IAMT1) IAMT1 , SUM(IAMT2) IAMT2  
													from IPMST   where SCODE = '".$S_SCODE."'   GROUP BY  KCODE , ICYY, ICMM ) cho_ip 	on last_cho.KCODE = cho_ip.KCODE and last_cho.HYY = cho_ip.ICYY and last_cho.HMM = cho_ip.ICMM 
							where cnt = 1 
					) b on a.KCODE = b.code 
				where A.SCODE = '".$S_SCODE."'   AND A.KSBIT IN('1','7') AND A.KBIT = '2'   AND  ISNULL(B.CHO_AMT,0)  > 0 
				  AND A.SBIT IN('1','2','3','7') AND (A.MADATE > '$ADATE'  OR  ISNULL(A.MADATE,'')  = '') ";


		$result =  sqlsrv_query( $mscon, $sql );

		if ($result == false){
			sqlsrv_free_stmt($result);
			sqlsrv_close($mscon);
			$message= "����� DATA������ ������ �߻��Ͽ����ϴ�.(".$S_SCODE.")";
			echo "<script> alert('$message'); </script>";
		}


		// �ѵ��ݾ� ��������
		$sql	= "select PTOT, CPTOT from SINAN_WATER.DBO.COMPANY where SCODE='".$S_SCODE."'";
		$qry	= sqlsrv_query( $mscon, $sql );
		$fet	= sqlsrv_fetch_array($qry);
		extract($fet);

		// ��û�� ���ɾ� ��������
		$sql	= "select sum(case when ksbit = '1' then aoutamt else 0 end) + sum(case when ksbit = '1' and abit < 8 then aamt else 0 end) gsin_amt, 
						  sum(case when ksbit = '7' then aoutamt else 0 end) + sum(case when ksbit = '7' and abit < 8 then aamt else 0 end) csin_amt  
				   from TACMS 
				   where SCODE='".$S_SCODE."'
					 AND SUBSTRING(ADATE,1,6) = substring(CONVERT(varchar(8),GETDATE(),112),1,6) ";

		$qry	= sqlsrv_query( $mscon, $sql );
		$totmonth	= sqlsrv_fetch_array($qry);

		$gsin_amt = $totmonth['gsin_amt'];
		$csin_amt = $totmonth['csin_amt'];

		$ptot_temp	= $PTOT-$gsin_amt;
		$ptot_ctemp	= $CPTOT-$csin_amt;

		if($ptot_temp < 0){
			$message = "����CMS��û�� ���ɾ��� �ѵ��� �ʰ��Ͽ����ϴ�. �ݾ� �����ٶ��ϴ�.(".$S_SCODE.")";
		}else if($ptot_ctemp < 0){
			$message = "ī��CMS��û�� ���ɾ��� �ѵ��� �ʰ��Ͽ����ϴ�. �ݾ� �����ٶ��ϴ�.(".$S_SCODE.")";
		}
	
	} // if�� ����

} // foreach ����
sqlsrv_query("COMMIT");
$message = $ADATE." �� ��Ż �ڵ���ݽ�û DATA�� ���� �Ϸ��Ͽ����ϴ�" ;
sqlsrv_free_stmt($qry);
sqlsrv_close($mscon);	
echo "<script> alert('$message'); </script>";

?>

