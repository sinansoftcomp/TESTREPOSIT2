<meta charset="utf-8">
<?
include($_SERVER['DOCUMENT_ROOT']."/bin/include/dbConn2.php");

include_once('./ren_menu3_01_action_cho_insert_fun.php');   

// 신청일자
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


// 자동출금 데이터 생성완료했을 경우 리턴
if($MBIT == '2'){
	sqlsrv_free_stmt($result);
	sqlsrv_close($mscon);
	$message="해당일은 자동출금 데이터 생성이 완료되었습니다.";
	echo "<script> alert('$message'); </script>";
	exit;
}

if($JDATE == '' || $JDATE == null || $JDATE == 'undefined' ){
	sqlsrv_free_stmt($result);
	sqlsrv_close($mscon);
	$message="금일은 자동출금일이 아닙니다. ";
	echo "<script> alert('$message'); </script>";
	exit;	
}

if($DD == '' || $DD == null || $DD == 'undefined' ){
	sqlsrv_free_stmt($result);
	sqlsrv_close($mscon);
	$message="출금일 확인 오류입니다. ";
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

// 트렌젝션 시작
sqlsrv_query("BEGIN TRAN");

foreach($listData	as $key => $val){

	$S_SCODE	= $val['SCODE'];
	$UGUBUN		= $val['UGUBUN'];
	$PROC_BIT	= $val['AUTO_BANG']; // 출금방법

	$sql  = "SELECT COUNT(*) TOTCNT  FROM TACMS WHERE SCODE = '".$S_SCODE."' AND ADATE = '".$ADATE."' ";
	$result  = sqlsrv_query( $mscon, $sql );
	$row =  sqlsrv_fetch_array($result); 

	// 해당일에 출금 데이터가 있을 경우 패스~
	if($row[TOTCNT] < 1){

		echo "SCODE:".$S_SCODE."<br>";

		//--->당월 청구data가 존재 하는가 ? (전월말일 마감을 자동으로 해야하는지를 판단하기 위함)
		$YY = SUBSTR($JDATE,0,4);
		$MM = SUBSTR($JDATE,4,2);
		//$DD = SUBSTR($ADATE,6,2);

		//---전월구하기 
		$JUN_ADATE = date("Ymd", strtotime("-1 month", strtotime($JDATE)));
		$JUN_YY = SUBSTR($JUN_ADATE,0,4);
		$JUN_MM = SUBSTR($JUN_ADATE,4,2);
		$JUN_DD = '31';


		$sql  = "select count(*) LL_CNT  from cho where SCODE = '".$S_SCODE."' AND hyy  = '$YY' and  hmm = '$MM' AND CBIT = '1' ";
		$result  = sqlsrv_query( $mscon, $sql );
		$row =  sqlsrv_fetch_array($result); 

		If(!$row[LL_CNT]){
		   //--->당월청구data가 없으므로 만약을 위하여 전월말일자로  청구data 생성하고 
			if (ren_menu3_01_action_cho_insert_fun($S_SCODE, $JUN_YY,$JUN_MM, $JUN_DD, $mscon)  !=  0 ) {
				sqlsrv_free_stmt($result);
				sqlsrv_close($mscon);
				$message= "전월 청구 DATA생성중 에러가 발생하였습니다.(".$S_SCODE.")";
				echo "<script> alert('$message'); </script>";
			}	
		}


		//echo " / Check: ".$S_SCODE."/".$YY."/".$MM."/".$DD."/".'<br>' ;

		//--->당월이체 의뢰일 까지 월청구data생성 함 
		$dataS = ren_menu3_01_action_cho_insert_fun($S_SCODE, $YY,$MM, $KDAY, $mscon);
		//echo " / Check: ".$S_SCODE."/".$YY."/".$MM."/".$DD."/".$mscon ;
		if ($dataS  !=  0 ) {
			sqlsrv_free_stmt($result);
			sqlsrv_close($mscon);
			$message= "당월 청구 DATA생성중 에러가 발생하였습니다.(".$S_SCODE.")";
			echo "<script> alert('$message'); </script>";
		}	

		//--->회사정보에서 건당이체 한도가져오기. 
		$sql  = "select PAMT, CPAMT, PKCODE   from SINAN_WATER.DBO.COMPANY  where SCODE = '".$S_SCODE."'  ";
		$result  = sqlsrv_query( $mscon, $sql );
		$row =  sqlsrv_fetch_array($result); 

		$PAMT   =$row[PAMT];     // 계좌건당한도
		$CPAMT  =$row[CPAMT];    // 카드건당한도
		$PKCODE =$row['PKCODE']; //거래처ID


		If(!$PAMT || $PAMT < 1000  ){
			sqlsrv_free_stmt($result);
			sqlsrv_close($mscon);
			$message= "회사정보에 cms건당이체한도가 잘못정의됨.(".$S_SCODE.")";
			echo "<script> alert('$message'); </script>";
		}
		 

		// 처리사원 : 관리자
		$SKEY  = '1001';
		$GCODE = $PKCODE;

		if(!$GCODE || $GCODE == '' || $GCODE == ' '){
			sqlsrv_free_stmt($result);
			sqlsrv_close($mscon);
			$message= "환경설정 > 사용회사정보에서 거래처ID 입력 후 진행하시기 바랍니다.(".$S_SCODE.")";
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
			$message= "월출금 DATA생성중 에러가 발생하였습니다.(".$S_SCODE.")";
			echo "<script> alert('$message'); </script>";
		}


		// 한도금액 가져오기
		$sql	= "select PTOT, CPTOT from SINAN_WATER.DBO.COMPANY where SCODE='".$S_SCODE."'";
		$qry	= sqlsrv_query( $mscon, $sql );
		$fet	= sqlsrv_fetch_array($qry);
		extract($fet);

		// 월청구 가능액 가져오기
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
			$message = "계좌CMS월청구 가능액이 한도를 초과하였습니다. 금액 수정바랍니다.(".$S_SCODE.")";
		}else if($ptot_ctemp < 0){
			$message = "카드CMS월청구 가능액이 한도를 초과하였습니다. 금액 수정바랍니다.(".$S_SCODE.")";
		}
	
	} // if문 종료

} // foreach 종료
sqlsrv_query("COMMIT");
$message = $ADATE." 일 렌탈 자동출금신청 DATA를 생성 완료하였습니다" ;
sqlsrv_free_stmt($qry);
sqlsrv_close($mscon);	
echo "<script> alert('$message'); </script>";

?>

