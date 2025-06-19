<?	

 /* 
	KWN ���̺� ��ݹ��(KSBIT)�� û������(9) �� ��쿡 û�������� �̻���
	û������������ �̼��� ����� ��û�� ������ ����(��, ������� 0������ ����)
	CHO ���̺� CBIT�÷��߰� (1:��û���ڵ����� / 2:��������)
	- 17�� 11�� 03�� û�������� ���� �߰�
	������(SBIT)�� �������(6)�� ��쿡 ��û�� �����ͻ������� ����
	�ݳ�������(BDATE)�� YYYYMM ���ر����� ��û�� ������ �����Ұ�
	(EX. �ݳ��������� 2017��11��01���̶�� 2017��11������ ��û�� ����)
	- 17�� 11�� 24�� û�������� ���� �߰�
	��Ż����(LBIT)�� ����Ż(3)�� �Ǹ�(4) ��쿡�� ��û�� ������ �����Ұ�
	(�ð���Ż / �Ϸ�Ż�� �߰�û���� ����X, ��û�� ������ ������ ����X)
 */
function ren_menu3_01_action_cho_insert_fun ($S_SCODE, $HYY,$HMM, $HDD, $mscon) 
{
			//echo "Action: ".$S_SCODE;
            $T_DATE		=  date("Ymd");	
			$A_DATE     =  $HYY.$HMM.$HDD; 
			$YYMM       =  $HYY.$HMM;

			$term = intval((strtotime($A_DATE)-strtotime($T_DATE))/86400); //��¥ ������ �ϼ��� ���Ѵ�.

			
/*
			// �׽�Ʈ�� ���Ͽ� 7�� ���� ����
			IF ($term > 10) {
				$message = '��û�� ������ 10�� �� ������ �����մϴ�!' ;
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

	//-->û������� ��û������ ���� (KSBIT <> '9' )	

    // Ʈ������ ����
    sqlsrv_query("BEGIN TRAN");
    $result =  sqlsrv_query( $mscon, $sql );


    if ($result == false){
        sqlsrv_query("ROLLBACK");
		$message = '��û�� DATA������ ������ �߻��Ͽ����ϴ�.';
        echo "<script>  alert('$message');   </script>";
        return -1;
    }

	sqlsrv_query("COMMIT");


     //----��û�� ������ ������ �� 3������ û�������ʹ� �����Ѵ�  
	 // ������ �Ǽ��� ���Ͽ� 3�Ǹ� ���� ����ó��
     	   $sql = "delete from cho 
				   where scode = '".$S_SCODE."' and 
						 not exists (select * 
									   from ( select scode , kcode , hyy , hmm , hdd , row_number() over(partition by kcode order by hyy desc , hmm desc , hdd desc ) cnt 
											   from cho where SCODE = '".$S_SCODE."') tbl 
									  where cho.SCODE = tbl.scode and cho.kcode = tbl.kcode and cho.hyy = tbl.hyy and cho.HMM = tbl.hmm and cho.HDD = tbl.hdd and 
											tbl.cnt <= 3 ) 	";

    // Ʈ������ ����
    sqlsrv_query("BEGIN TRAN");
    $result =  sqlsrv_query( $mscon, $sql );


    if ($result == false){
        sqlsrv_query("ROLLBACK");
		$message = '���� û�������� ������ ������ �߻��Ͽ����ϴ�.';
        echo "<script>  alert('$message');   </script>";
        return -1;
    }

	sqlsrv_query("COMMIT");

 return 0;

}
?>