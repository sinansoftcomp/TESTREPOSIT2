<?	

 /* 
	KWN 테이블에 출금방법(KSBIT)이 청구종료(9) 일 경우에 청구데이터 미생성
	청구종료일지라도 미수금 존재시 월청구 데이터 생성(단, 당월분은 0원으로 생성)
	CHO 테이블 CBIT컬럼추가 (1:월청구자동생성 / 2:수동생성)
	- 17년 11월 03일 청구데이터 내용 추가
	계약상태(SBIT)가 계약종료(6)일 경우에 월청구 데이터생성하지 않음
	반납예정일(BDATE)이 YYYYMM 기준까지만 월청구 데이터 생성할것
	(EX. 반납예정일이 2017년11월01일이라면 2017년11월까지 월청구 생성)
	- 17년 11월 24일 청구데이터 내용 추가
	렌탈구분(LBIT)이 월렌탈(3)과 판매(4) 경우에만 월청구 데이터 생성할것
	(시간렌탈 / 일렌탈은 추가청구도 생각X, 월청구 데이터 무조건 생성X)
 */
function ren_menu3_01_action_cho_insert_fun ($S_SCODE, $HYY,$HMM, $HDD, $mscon) 
{
			//echo "Action: ".$S_SCODE;
            $T_DATE		=  date("Ymd");	
			$A_DATE     =  $HYY.$HMM.$HDD; 
			$YYMM       =  $HYY.$HMM;

			$term = intval((strtotime($A_DATE)-strtotime($T_DATE))/86400); //날짜 사이의 일수를 구한다.

			
/*
			// 테스트로 인하여 7일 제한 해제
			IF ($term > 10) {
				$message = '월청구 생성은 10일 후 까지만 가능합니다!' ;
				echo "<script>	alert('$term');	</script>";
		        return -1;  
			}
*/

			$sql = "insert into cho (scode, kcode, hyy, hmm, hdd, hjamt, hamt1, hamt2, cbit)
				  select  a.scode , a.kcode, '$HYY' , '$HMM' , a.kday , isnull(b.mamt,0)  ,
						  case when ksbit = '9' OR A.SBIT = '6' OR SUBSTRING(A.BDATE,1,6) < '$YYMM' then 0 else a.camt end, isnull(c.aamt,0), '1'
				   from    kwn a left outer join (select last_cho.kcode code , isnull(last_cho.hjamt,0)   + isnull(last_cho.hamt1,0) + isnull(last_cho.hamt2,0)    -  isnull(cho_ip.ijamt,0)  -  isnull(cho_ip.IAMT1,0)-  isnull(cho_ip.IAMT2,0) mamt 
								from ( select kcode , hyy , hmm , isnull(hjamt,0) hjamt ,  isnull(hamt1,0) hamt1 , isnull(hamt2,0) hamt2 ,   	row_number() over(partition by kcode order by hyy desc , hmm desc) cnt 
										   from cho
										  where SCODE = '".$S_SCODE."' ) last_cho left outer join
														(select KCODE,  ICYY , ICMM, SUM(IJAMT) IJAMT , SUM(IAMT1) IAMT1 , SUM(IAMT2) IAMT2    from IPMST   where SCODE = '".$S_SCODE."'   GROUP BY  KCODE , ICYY, ICMM ) cho_ip 
												 on last_cho.KCODE = cho_ip.KCODE and last_cho.HYY = cho_ip.ICYY and last_cho.HMM = cho_ip.ICMM 
					   where cnt = 1 ) b on a.KCODE = b.code 
					   left outer join (select scode, kcode, cyymm, SUM(isnull(aamt,0)) aamt from choadd where scode = '".$S_SCODE."' group by scode,kcode,cyymm) c on a.SCODE = c.scode and a.KCODE = c.kcode and c.cyymm = '$YYMM'
				   where   A.SCODE = '".$S_SCODE."' AND  a.scdate <= '$YYMM'  and KDAY <= '$HDD' and A.LBIT IN('3','4') AND
						   ((A.SBIT <> '6' AND A.KSBIT <> '9' AND SUBSTRING(A.BDATE,1,6) >= '$YYMM') OR isnull(b.mamt,0) > 0) AND
						   not exists (select * from cho where scode = '".$S_SCODE."' and HYY = '$HYY' and HMM = '$HMM' and KCODE = a.KCODE )";

	//-->청구종료는 월청구생성 안함 (KSBIT <> '9' )	

    // 트렌젝션 시작
    sqlsrv_query("BEGIN TRAN");
    $result =  sqlsrv_query( $mscon, $sql );


    if ($result == false){
        sqlsrv_query("ROLLBACK");
		$message = '월청구 DATA생성중 에러가 발생하였습니다.';
        echo "<script>  alert('$message');   </script>";
        return -1;
    }

	sqlsrv_query("COMMIT");


     //----월청구 생성이 성공한 후 3개월전 청구데이터는 삭제한다  
	 // 데이터 건수로 인하여 3건만 제외 삭제처리
     	   $sql = "delete from cho 
				   where scode = '".$S_SCODE."' and 
						 not exists (select * 
									   from ( select scode , kcode , hyy , hmm , hdd , row_number() over(partition by kcode order by hyy desc , hmm desc , hdd desc ) cnt 
											   from cho where SCODE = '".$S_SCODE."') tbl 
									  where cho.SCODE = tbl.scode and cho.kcode = tbl.kcode and cho.hyy = tbl.hyy and cho.HMM = tbl.hmm and cho.HDD = tbl.hdd and 
											tbl.cnt <= 3 ) 	";

    // 트렌젝션 시작
    sqlsrv_query("BEGIN TRAN");
    $result =  sqlsrv_query( $mscon, $sql );


    if ($result == false){
        sqlsrv_query("ROLLBACK");
		$message = '과거 청구데이터 삭제중 에러가 발생하였습니다.';
        echo "<script>  alert('$message');   </script>";
        return -1;
    }

	sqlsrv_query("COMMIT");

 return 0;

}
?>