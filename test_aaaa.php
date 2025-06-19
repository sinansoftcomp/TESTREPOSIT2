<?
include($_SERVER['DOCUMENT_ROOT']."/rental/include/source/head.php");

// 접속구분
$ugubun = $_SESSION['S_UGUBUN'];

$YDONG=$_SESSION['S_YDONG'];


// 세금계산서 구분 (하이웍스 = 1, 홈택스빌 = 2)
$sql= "select taxbit from sinan_water.dbo.company where scode = '".$_SESSION['S_SCODE']."' ";

$result  = sqlsrv_query( $mscon, $sql );
$row =  sqlsrv_fetch_array($result);

$taxbit = $row[taxbit];

if(!$taxbit){
	$taxbit = '1';
}



if($_GET['GCODE']){
	// 기본 인적사항 데이터
	$sql	= "select a.*, b.SNAME from 
	kwngo a 
	left outer join swon b on a.scode=b.scode and a.ASKEY=b.SKEY
	where a.SCODE='".$_SESSION['S_SCODE']."' and a.GCODE='".$_GET['GCODE']."'";
	$qry	= sqlsrv_query( $mscon, $sql );
	extract($fet	= sqlsrv_fetch_array($qry));

	if(!$fet['GCODE']) alert("고객이 삭제되었거나 정보가 없습니다.","close");

	// 고객별 총 잔액
	$sql	= "
	SELECT A.GCODE, COUNT(*) KWN_CNT,  SUM(ISNULL(JAN_AMT,0)) JAN_AMT
	FROM KWN A LEFT OUTER JOIN
			(
			select     last_cho.KCODE, hyy+hmm+hdd HYMD , isnull(last_cho.hjamt,0) JM_AMT  ,  
								 isnull(hamt1,0) + ISNULL(hamt2,0) DANG_AMT , 
								 isnull(last_cho.hjamt,0) + isnull(hamt1,0) + isnull(hamt2,0) CH_AMT , 
								 isnull(iamt1,0) IP_AMT , 
								 isnull(last_cho.hjamt,0) + isnull(hamt1,0) + isnull(hamt2,0) - isnull(ijamt,0) - isnull(iamt1,0) - isnull(IAMT2,0) JAN_AMT 
			from (
					select * from 	
						 (select a.kcode , hyy , hmm , hdd,  hjamt , hamt1, hamt2, c.gcode,
							  row_number() over(partition by a.kcode order by hyy desc , hmm desc) cnt 
						 from cho a left outer join  KWN b on a.SCODE=b.SCODE and a.KCODE=b.KCODE
									left outer join  KWNgo c on b.SCODE=c.SCODE and b.gcode=c.gcode
						 where a.SCODE = '".$_SESSION['S_SCODE']."' and b.GCODE = '".$_GET['GCODE']."'  ) a 
					where cnt = 1                 
				  ) last_cho left outer join 
				  ( select a.KCODE, ICYY , ICMM, SUM(IJAMT) IJAMT , SUM(IAMT1) IAMT1 , SUM(IAMT2) IAMT2 
						 from IPMST a left outer join  KWN b on a.SCODE=b.SCODE and a.KCODE=b.KCODE
									  left outer join  KWNgo c on b.SCODE=c.SCODE and b.gcode=c.gcode
						 where a.SCODE = '".$_SESSION['S_SCODE']."'  and b.GCODE = '".$_GET['GCODE']."'  
						 GROUP BY a.KCODE , ICYY, ICMM )  cho_ip
				   on last_cho.KCODE = cho_ip.KCODE and last_cho.HYY = cho_ip.ICYY and last_cho.HMM = cho_ip.ICMM 
			) B	 ON A.KCODE =B.KCODE
	WHERE  A.SCODE = '".$_SESSION['S_SCODE']."'   and a.GCODE = '".$_GET['GCODE']."' 
	GROUP BY 	A.GCODE	
	--건/금액.
	";

	$qry	= sqlsrv_query( $mscon, $sql );
	$jan_data	= sqlsrv_fetch_array($qry);



$sql	= "
	SELECT A.GCODE, A.FAMT,  --렌탈총액
			B.IPTOT, --입금합
			ISNULL(A.FAMT,0) - ISNULL(B.IPTOT,0) FAMT_JAN  --렌탈잔액 
		FROM 
			(SELECT B.GCODE,SUM(A.FAMT) FAMT
			FROM   KWN A  LEFT OUTER JOIN KWNGO B  ON A.SCODE=B.SCODE AND A.GCODE = B.GCODE
			WHERE A.SCODE = '".$_SESSION['S_SCODE']."'  AND B.GCODE = '".$_GET['GCODE']."'
			GROUP BY B.GCODE ) A 

		LEFT OUTER JOIN (			
			select c.GCODE, SUM(IJAMT + IAMT1) IPTOT
			from IPMST a left outer join  KWN b on a.SCODE=b.SCODE and a.KCODE=b.KCODE
						 left outer join  KWNgo c on b.SCODE=c.SCODE and b.gcode=c.gcode
			where a.SCODE = '".$_SESSION['S_SCODE']."' AND C.GCODE = '".$_GET['GCODE']."'  
		GROUP BY C.GCODE) B  ON A.GCODE =B.GCODE
";
$qry		= sqlsrv_query( $mscon, $sql );
$ipTotal	= sqlsrv_fetch_array($qry);

//echo $_SESSION['S_YDONG'] ;


}

sqlsrv_free_stmt($qry);
sqlsrv_close($mscon);

// 타이틀 이미지를 위한 변환
$pageTemp	= explode("/",$_SERVER['PHP_SELF']);
$imageName	= str_replace(".php","",$pageTemp[count($pageTemp)-1]);
?>

<script>
	window.moveTo(window.screen.width/2-625, window.screen.height/2-490);  
	window.resizeTo("1250", "970");                             // 윈도우 리사이즈
	
</script>






<div style="width:1200px">



   <header class="yb_title">
    <h2><img src="<?=$conf['homeDir']?>/img/<?=$imageName?>.gif"></h2>
	<!-- 동영상강의 아이콘 left값으로 좌우측 위치 수정-->
<!-- 	<span class="w3-tooltip movie" style="left: 130px; bottom:19px;"><i class="fa fa-caret-square-o-right"></i> -->
<!--     <span class="w3-text w3-tag movie_tooltip"><?=$MovieInfo[$imageName]['text']?></span></span>   -->
    <!-- 동영상강의 아이콘 끝 -->
	<!--<div style="position: absolute;top: 32px;left: 371px;color: red;width: 269px;text-align: right;">미수금액 : <?=number_format($misu);?>원</div>-->
	<div style="position: absolute;top: 32px;left: 371px;color: red;width: 269px;text-align: right;"><strong><?=$kerr;?></strong></div> 
    <div style="position: absolute;top: 24px;left: 781px;">
      <button type="button" class="yb_btn_grey" onclick="$.ajaxLoding('./ren_menu1_01_pop_01_layer_smssend_temp.php',$('.layerBody'),'&bal_no=<?=$TEL1?>-<?=$TEL2?>-<?=$TEL3?>&addresseeNumber=<?=$HTEL1?>-<?=$HTEL2?>-<?=$HTEL3?>&prg_id=join&KNAME=<?=$KNAME?>')"><span>SMS발송</span></button>
	  <?if($taxbit == '1'){?>
		<button type="button" class="yb_btn_grey" onclick="$.ajaxLoding('/rental/sub/menu8/ren_menu8_03_layer_tvat.php',$('.layerBody'),'&KNAME=<?=$KNAME?>')"><span>세금계산서</span></button>
	  <?}else{?>
		<button type="button" class="yb_btn_grey" onclick="$.ajaxLoding('/rental/sub/menu8/ren_menu8_06_layer_tvat.php',$('.layerBody'),'&KNAME=<?=$KNAME?>')"><span>세금계산서</span></button>
	  <?}?>
      <button type="button" class="yb_btn_grey" onclick="$.ajaxLoding('/rental/sub/menu8/ren_menu8_01_layer_cash.php',$('.layerBody'),'&KNAME=<?=$KNAME?>')"><span>현금영수증</span></button>
      <button type="button" onclick="self.close();" class="yb_btn_grey"><span>닫기</span></button>
    </div>
  </header>
  <div class="w3-row" style="margin-top:10px;">
	<div class="yb_round_box" style="height:135px;border-radius: 10px 10px 0px 0px;border-bottom: 0px;">
        <div style="border:1px solid #017dbd;padding-bottom: 10px;height:100%;border-radius: 7px 7px 0px 0px;border-bottom: 0px;">
			<section class="input_table">
			   <h6>고객정보</h6>
			   <div class="btn_group">
					<button type="button" class="yb_btn addUser"><span><i class="fa fa-plus"></i>고객관리</span></button>
					<button type="button" class="yb_btn searchUser"><span><i class="fa fa-search"></i>고객 검색</span></button>
				</div>
			   <table class="w3-striped">
					<colgroup>
						<col style="width:8%">
						<col style="width:8%">
						<col style="width:8%">
						<col style="width:8%">
						<col style="width:8%">
						<col style="">
						<col style="width:8%">
						<col style="width:10%">
						<col style="width:8%">
						<col style="width:10%">
						<col style="width:6%">
						<col style="width:11%">
					</colgroup>
					<tbody>
						<tr>
							<th>고객번호</th>
							<td><?=$GCODE?></td>
							<th>고객명</th>
							<td id="kwn_kname_1" colspan=3><?=$KNAME?></td>
							<th>대표자</th>
							<td><?=$KSNAME?></td>
							<th>사업자번호</th>
							<td><?=$SNUM?></td>
							<th>업태</th>
							<td><?=$UPTAE?></td>
							
						</tr>
						<tr>
							<th>거래처담당자</th>
							<td><?=$CDNAME?></td>
							<th>지역</th>
							<td><?=$AREA?></td>
							<th>담당자</th>
							<td><?=$SNAME?></td>
							<th>연락처</th>
							<td><?=$TEL1?>-<?=$TEL2?>-<?=$TEL3?></td>
							<th>비상연락처</th>
							<td><?=$BTEL1?>-<?=$BTEL2?>-<?=$BTEL3?></td>
							
							<th>업종</th>
							<td><?=$UPJONG?></td>
						</tr>
						<tr>
							<th>주소</th>
							<td colspan=5>(<?=$POST?>) <?=$ADDR?> <?=$ADDR_DT?></td>
							<th>휴대전화</th>
							<td><?=$HTEL1?>-<?=$HTEL2?>-<?=$HTEL3?>	</td>
							<th>고객청구잔액</th>
							<td style="text-align:right;color:blue"><?=number_format($jan_data['KWN_CNT'])?> / <?=number_format($jan_data['JAN_AMT'])?></td>
							<th>이메일</th>
							<td><?=$EMAIL?></td>
						</tr>
					</tbody>
				</table>
			   
			   
			</section>
		</div>
	</div>

    <div class="w3-col" style="width:630px;">
      <div class="yb_round_box" style="height:685px;border-top: 0px;border-radius: 0px 0px 0px 10px;border-right: 0px;">
        <div style="border:1px solid #017dbd;padding-bottom: 10px;height:100%;border-radius: 0px 0px 0px 7px;">
	        
	        <!-- 기본인적사항 -->          
           
           
           
           
           
           <!-- 렌탈현황 -->


           <section class="input_table">
	           <h6>렌탈현황</h6>
				<!-- 입금합계 -->
				<div style="position:absolute;top: 16px;left: 140px;font-size: 12px;">
					<span class="dot_img" style="width: 6px;"><img src="/rental/img/dot_orange.png"></span>
					<span style="color:blue;margin-right:20px;">렌탈합계 : <?=number_format($ipTotal['FAMT']);?></span>
					<span class="dot_img" style="width: 6px;"><img src="/rental/img/dot_orange.png"></span>
					<span style="color:red;margin-right:20px;">입금합 : <?=number_format($ipTotal['IPTOT']);?></span>
					<span class="dot_img" style="width: 6px;"><img src="/rental/img/dot_orange.png"></span>
					<span style="color:blue">렌탈잔액 : <?=number_format($ipTotal['FAMT_JAN']);?></span>
				</div>

			   <div class="yb_grey_box kwnlist" style="width: 100%;height: 100px;overflow-x: hidden;overflow-y: auto;"></div>
	           
		   </section>
           
		   <!-- 렌탈정보 -->
           <section class="input_table">
				<h6>렌탈정보</h6>
				<div id="kwn_info_text" style="
				display: inline-block;
zoom: 1;
font-size: 12px;
color: blue;
line-height: 20px;
-webkit-border-radius: 5px;
-moz-border-radius: 5px;
border-radius: 15px;
background: #fff;
vertical-align: top;
position: absolute;
top: 15px;
left: 125px;
width: 99px;
text-align: center;
height: 21px;
"><span id="kwn_info_text_in"></span></div>
				<div class="btn_group">
					<TD style="">
								<label style="color: BLUE; padding: 0 12px; font-size:12px; line-height:20px;"><?=$fetNANSUNAME?></label>
							</TD>
							<TD>
							 <button type="button" onclick="mfilePop(1);" class="yb_btn" ><span>모바일증빙 요청</span></button>
							 </TD>
					<button type="button" class="yb_btn" onclick="kcode_reload('')"><span>신규</span></button>
					<button type="button" class="yb_btn" onclick="kwn_del();"><span>삭제</span></button>
					<button type="button" class="yb_btn" onclick="kwn_update();"><span>저장</span></button>
					<? if ($_SERVER['REMOTE_ADDR'] =='121.137.89.13'){?>
					<!--div class="btn_group">
						
					</div-->
					<?}?>
				</div>
				
				<div class="kwnBody"></div>
	           
		   </section>

		   <!-- 렌탈별 상품정보 -->
           <section class="input_table">
				<form name="bdate_all" class="bdateForm" method="post" action="ren_menu1_01_action_bdate_all.php">
					<input type="hidden" name="bdate_gcode" value="<?=$_GET['GCODE']?>">
					<input type="hidden" name="bdate_kcode" value="">
					<input type="hidden" name="bdate_bdate" value="">
					<input type="hidden" name="bdate_type" value="up">
				</form>
			   <div class="btn_group">
					<div id="datepicker_all_bdate_btn" style="position: absolute;left: -126px;"></div>
				   <button type="button" class="yb_btn all_bdate_btn"><span>일괄반납처리</span></button>
					<button type="button" class="yb_btn" onclick="itemList_new();"><span>상품추가</span></button>
				</div>
				<div>
		           <ul class="yb_tab_menu">
			           <li>상품정보</li>
			           <li style="left: -20px;z-index: 1;">추가청구현황</li>
		           </ul>
		           <div class="yb_grey_box kwnitemBody" style="width: 100%;height: 161px;overflow-x: hidden;overflow-y: auto;"></div>
		           <div class="yb_grey_box choaddBody" style="display: none;width: 100%;height: 161px;overflow-x: hidden;overflow-y: auto;"></div>
	           </div>
		   </section>

        </div>
      </div>
    </div>
	


    <div class="w3-rest">
      <div class="yb_round_box" style="height:685px;border-top: 0px;border-left: 0px;border-radius: 0px 0px 10px 0px;border-left: 0px;">
        <div style="border:1px solid #017dbd;padding-bottom: 10px;height:100%;border-radius: 0px 0px 7px 0px;border-left: 0px;">
			
			<!-- 청구내역 -->
           <section class="input_table">
	           <h6>청구내역</h6>
			   <div class="choBody" style="width: 100%;height: 86px;overflow-x: hidden;overflow-y: auto;">
					    
				</div>
	           
		   </section>

           <!-- 입금현황 -->
           <!-- CMS 출금현황 -->
           <section class="input_table ">
				<div class="btn_group">
		           <button type="button" class="yb_btn" onclick="ipmst_new();"><span>입금등록</span></button>
	           </div>
	           <div>
		           <ul class="yb_tab_menu">
			           <li>입금현황</li>
			           <li style="left: -20px;z-index: 1;">CMS 출금현황</li>
		           </ul>
		           <div class="yb_grey_box ipmstBody" style="width: 100%;height: 300px;overflow-x: hidden;overflow-y: auto;">
					    
				   </div>
		           <div class="yb_grey_box acmsBody" style="display: none;width: 100%;height: 300px;overflow-x: hidden;overflow-y: auto;">
					
				   </div>
	           </div>
	           
	           
           </section>
		   
		   		   
           <!-- AS정보 -->
           <!-- 고객메모 -->
           <section class="input_table ">
				
	           
	           <h6 style="width:142px;background-image: url(/rental/img/tableLabel_bg2.png);">AS.상담.일정관리</h6>
			   <div class="btn_group">
		           <button type="button" class="yb_btn" onclick="$.ajaxLoding('ren_menu1_01_pop_01_layer_asmst.php',$('.layerBody'),'')"><span>AS.상담등록</span></button>
	           </div>
			   <div class="yb_grey_box asmstBody" style="width: 100%;height: 161px;overflow-x: hidden;overflow-y: auto;">
					   
			    </div>
           </section>


        </div>

      </div>
    </div>
  </div>
</div>




<!-- 모달 -->
<div id="modal1" class="w3-modal" style="z-index:1000;padding-top:5%;">
  <div class="w3-modal-content w3-animate-top" style="width: 90%;height: 90%;border: 3px solid orange;overflow-x: hidden;overflow-y: auto;">
<!--     <header class="w3-container w3-teal">  -->
<!--       <span onclick="$('#modal1').hide()" class="w3-closebtn">×</span> -->
<!--     </header> -->
    <div class="w3-container layerBody ">
      
    </div>
	<footer class="w3-container" style="height:0px;"> 
      
    </footer>
  </div>
</div>

<script>




///모바일증빙요청
function mfilePop(sbit){
	
	///업체 모바일 동의 체크. ydong  y 면 동의  n 이면 미동의
	var ydong = $("#YDONG").val();

	//미동의면 약관동의 창 오픈
	if ( ydong == "N" ||  ydong == " "){
		var left = Math.ceil((window.screen.width - 1000)/2);
		var top = Math.ceil((window.screen.height - 1000)/2);
		var popOpen	= window.open("ren_menu1_01_pop_mfile_ydong.php", "ydongpopup","width=500px,height=700px,top="+top+",left="+left+",status=0,toolbar=0,menubar=0,location=false");
		popOpen.focus();
		return false;
	}
	
	var kcode = $("#kwn_KCODE").val();
	if (kcode == "" || kcode == " "){
		alert("고객저장 후 증빙등록 해주시기 바랍니다.");
		return false;
	}
	var sbit  = $("#KSBIT").val();
//$ugubun  프로그램 구분 일반CMS11 정수기CMS12 더빌CMS21 더빌정수기22 렌탈(더빌) 31 헬스케어(더빌) 41
//	var ugubun = '<?=$ugubun?>';
	if (sbit !="1" && sbit !="7"){
			alert("출금 방법이 CMS/카드CMS  인 경우만 모바일 증빙요청이 가능합니다.");	
		return false;
	}

	//alert(sbit);
	var left = Math.ceil((window.screen.width - 1000)/2);
	var top = Math.ceil((window.screen.height - 1000)/2);
	var popOpen	= window.open("ren_menu1_01_pop_mfile.php?KCODE="+kcode+"&SBIT="+sbit, "mpopup","width=1000px,height=930px,top="+top+",left="+left+",status=0,toolbar=0,menubar=0,location=false");
	popOpen.focus();
}



// 기본인적사항 삭제버튼
function kwn_del(){
	if($("form[name='kwn_form'] #kwn_KCODE").val()==""){
		alert('렌탈정보를 선택해주세요.');
		return false;
	}else{
		if(confirm("삭제된 데이터는 복구되지 않습니다.\n정말 삭제하시겠습니까?")){
			document.kwn_form.type.value='del';
			$("form[name='kwn_form']").submit();
		}
	}
}

// 렌탈별 상품정보 신규등록
function itemList_new(){
	var kcode	= $("form[name='kwn_form'] #kwn_KCODE").val();
	if(kcode==""){
		alert('렌탈정보를 선택해주세요.');
		return false;
	}else{
		$.ajaxLoding('ren_menu1_01_pop_01_layer_kwnitem_reg.php',$('.layerBody'),'&KCODE='+kcode);
	}
}

//	입금등록
function ipmst_new(){
	var kcode	= $("form[name='kwn_form'] #kwn_KCODE").val();
	if(kcode==""){
		alert('렌탈정보를 선택해주세요.');
		return false;
	}else{
		$.ajaxLoding('ren_menu1_01_pop_01_layer_ipmst_reg.php',$('.layerBody'),'&KCODE='+kcode);
	}
}


// 자바스크립트 Trim(문자열자르기 함수 선언)
String.prototype.trim = function(){
  return this.replace(/\s/g, "");
}

function cms_value_check(){
	
	if($("form[name='kwn_form'] #KSBIT").val()=='1'){

		var syjuno = $("form[name='kwn_form'] #SYJUNO").val();
		syjuno = syjuno.trim();
		
		if($("form[name='kwn_form'] #kwn_GCODE").val()==""){
			alert('고객을 먼저 선택해주세요.');
			return false;
		}else if($("form[name='kwn_form'] #BANK").val()==""){
			alert('은행명을 입력해주세요.');
			return false;
		}else if($("form[name='kwn_form'] #SKJNO").val()==""){
			alert('계좌번호를 입력해주세요.');
			return false;
		}else if($("form[name='kwn_form'] #SYJUNO").val()==""){
			alert('생년월일/사업No를 입력해주세요.');
			return false;
		}else if(syjuno.length != 6 && syjuno.length != 10){
			alert('생년월일은 6자리 / 사업자번호는 10자리를 입력해주세요.');
			return false;
		}else if($("form[name='kwn_form'] #KYJ").val()==""){
			alert('예금주를 입력해주세요.');
			return false;
		}else if($("form[name='kwn_form'] #SCDATE").val()==""){
			alert('출금시작월을 입력해주세요.');
			return false;
		}else if($("form[name='kwn_form'] #KDAY").val()==""){
			alert('출금일을 입력해주세요.');
			return false;
		}
	}else if($("form[name='kwn_form'] #KSBIT").val()=='7'){

		var yy = $("form[name='kwn_form'] #CARDYY").val();
		var mm = $("form[name='kwn_form'] #CARDMM").val();

		yy = yy.trim();
		mm = mm.trim();

		if($("form[name='kwn_form'] #PDATE").val()==""){
			alert('계약일을 입력해주세요.');
			return false;
		}else if($("form[name='kwn_form'] #CARDYY").val()==""){
			alert('카드유효년을 입력해주세요.');
			return false;
		}else if(yy.length != 2){
			alert('카드유효년 2자리 확인해주세요.');
			return false;
		}else if($("form[name='kwn_form'] #CARDMM").val()==""){
			alert('카드유효월을 입력해주세요.');
			return false;
		}else if(mm.length != 2){
			alert('카드유효월 2자리 확인해주세요.');
			return false;
		}else if(mm > '12'){
			alert('카드유효월은 01 - 12월 사이로 입력해주세요.');
			return false;
		}else if($("form[name='kwn_form'] #CARDNO").val()==""){
			alert('카드번호를 입력해주세요.');
			return false;
		}else if($("form[name='kwn_form'] #SCDATE").val()==""){
			alert('출금시작월을 입력해주세요.');
			return false;
		}else if($("form[name='kwn_form'] #KDAY").val()==""){
			alert('출금일을 입력해주세요.');
			return false;
		}
	}
	return true;
}



// 기본인적사항 수정 및 입력
function kwn_update(){
	// disable 처리되있을경우 저장이 안되기에 저장전 disable 해제
	$("form[name='kwn_form'] #BANK").attr("disabled", false);
	if(date_check(document.kwn_form)==false){
		return false;
	}else if(form_required_check(document.kwn_form)==false){
		return false;
	}else if(cms_value_check()==false){
		return false;
	}else{
		if($("form[name='kwn_form'] #CAMT").val()<=0){
			alert("월출금액은 0보다 커야합니다.");
		}else{
			if(document.kwn_form.KCODE.value){
				if(confirm("수정하시겠습니까?")){
					document.kwn_form.type.value='up';
					$("form[name='kwn_form']").submit();
				}
			}else{
				if(confirm("등록하시겠습니까?")){
					document.kwn_form.type.value='in';
					$("form[name='kwn_form']").submit();
				}
			}
		}
	}
}



// 년월일 변환 함수 20160115 => 2016-01-15
function ymd10(ymd){
	if(ymd.trim()=="") return "";
	var ymdTemp	= ymd.replace(/-/gi,"");
	return ymdTemp.substr(0,4)+"-"+ymdTemp.substr(4,2)+"-"+ymdTemp.substr(6,2);
}

// 년월일 변환 함수 2016-01-15 => 20160115
function ymd8(ymd){
	return ymd.replace(/-/gi,"");
}

// 년월일 6자리 변환 함수 2016-01-15 => 16-01-15  Date 함수  월  0  ~ 11 까지.. 
function ymd6(ymd){
	if(ymd.trim()=="") return "";
	var ymdTemp	= ymd.replace(/-/gi,"");
	return ymdTemp.substr(2,2)+"-"+ymdTemp.substr(4,2)+"-"+ymdTemp.substr(6,2);
}

// 입력 년월일과 주기로 년월일 반환 2016-01-15   
function GetCycleDay(ymd, cycl){
	var ymd1_1	= ymd8(ymd);
	var formattedDate = new Date(ymd1_1.substr(0,4),parseInt(ymd1_1.substr(4,2)-1)+parseInt(cycl),ymd1_1.substr(6,2));
	var d = formattedDate.getDate();
	var m =  formattedDate.getMonth()+1;
	var y = formattedDate.getFullYear();

	return y+"-"+leadingZeros(m,2)+"-"+leadingZeros(d,2);
};

// 두 년월일로 잔여일 계산
function treatAsUTC(date) {
    var result = new Date(date);
    result.setMinutes(result.getMinutes() - result.getTimezoneOffset());
    return result;
}

function GETRestDay(ymd1, ymd2){
	var ymd1_1	= ymd10(ymd1);
	var ymd2_1	= ymd10(ymd2);

	var formattedDate1 = treatAsUTC(ymd1_1);
	var formattedDate2 = treatAsUTC(ymd2_1);

	return (formattedDate2.getTime()-formattedDate1.getTime())/86400000;
}

function ajaxLodingTarket(url,target,etcData){

	$("#loadingView").hide();
	$.ajax({
	  url: url+"?"+etcData,
	  cache : false,
	  dataType : "html",
	  method : "GET",
	  data: { ajaxType : true, GCODE:"<?=$_GET['GCODE']?>"},
	  headers : {"charset":"euc-kr"},
	}).done(function(htmlData) {
		$(target).html(htmlData);
	});
};

function ajaxLoding(url,target,etcData){
	//alert('작업중입니다.');
	<?if(!$_GET['GCODE']){?>
	alert('기본인적사항을 입력해주세요.');
	<?}else{?>
		$("#loadingView").hide();
		if(url=='ren_menu1_01_pop_01_layer_tvat.php') $(".w3-modal-content").css("width","900px");
		if(url=='ren_menu1_01_pop_01_layer_smssend_temp.php') $(".w3-modal-content").css("width","86%");
		else $(".w3-modal-content").css("width","90%");

		$.ajax({
		  url: url+"?"+etcData,
		  cache : false,
		  dataType : "html",
		  method : "GET",
		  data: { ajaxType : true, GCODE:"<?=$_GET['GCODE']?>"},
		  headers : {"charset":"euc-kr"},
		}).done(function(htmlData) {
			$(target).html(htmlData);
			$("#modal1").show();
		});
	<?}?>
};

//as영역 리로드
function asmst_reload(){
	ajaxLodingTarket('ren_menu1_01_pop_01_layer_asmst_list.php',$('.asmstBody'),'');
}

// ajax로 되어있는 렌탈부분을 리로드 한다
// 고객정보를 제외한 모든 영역을 ajax로 가져온다
function gcode_reload(gcode,kcode){
	ajaxLodingTarket('ren_menu1_01_pop_01_layer_kwnlist.php',$('.kwnlist'),'&KCODE='+kcode);

	asmst_reload();
}



// kcode가 필요한 영역을 ajax로 가져온다
function kcode_reload(kcode){
	$("form[name='bdate_all'] input[name='bdate_kcode']").val(kcode);

	ajaxLodingTarket('ren_menu1_01_pop_01_layer_kwn.php',$('.kwnBody'),'&KCODE='+kcode);
	ajaxLodingTarket('ren_menu1_01_pop_01_layer_kwnitem.php',$('.kwnitemBody'),'&KCODE='+kcode);
	ajaxLodingTarket('ren_menu1_01_pop_01_layer_choadd.php',$('.choaddBody'),'&KCODE='+kcode);
	ajaxLodingTarket('ren_menu1_01_pop_01_layer_cho.php',$('.choBody'),'&KCODE='+kcode);
 //ajaxLodingTarket('ren_menu1_01_pop_01_layer_ipmst.php',$('.ipmstBody'),'&KCODE='+kcode);
	ajaxLodingTarket('ren_menu1_01_pop_01_layer_ipmst.php',$('.ipmstBody'),'&KCODE='+kcode);
	ajaxLodingTarket('ren_menu1_01_pop_01_layer_acms.php',$('.acmsBody'),'&KCODE='+kcode);

	if(kcode){
		$("#kwn_info_text_in").text("조회모드");
	}else{
		$("#kwn_info_text_in").text("신규모드");
	}
}


function bdate_all_submit(){
	
	if(confirm("해당 렌탈에대한 모든상품을 반납처리하시겠습니까?")){
		$("form[name='bdate_all']").submit();
	}
}

// jquery 시작

$(document).ready(function(){
	$(".all_bdate_btn").click(function(){
		if($("form[name='kwn_form'] select[name='LBIT']").val()=='4'){
			alert('대여구분이 판매인경우는 일괄반납처리가 되지 않습니다.');
			return false;
		}else{
			$( "#datepicker_all_bdate_btn" ).datepicker({
				changeMonth: true,
				changeYear: true,
				DefaultDate:null,
				yearRange: "1900:2100",
				dateFormat: "yy-mm-dd",
				prevText: '이전 달',
				nextText: '다음 달',
				monthNames: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
				monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
				dayNames: ['일','월','화','수','목','금','토'],
				dayNamesShort: ['일','월','화','수','목','금','토'],
				dayNamesMin: ['일','월','화','수','목','금','토'],
				showMonthAfterYear: true,
				yearSuffix: '년',
				altFormat: "yy-mm-dd",
				onSelect : function(date){
					$("form[name='bdate_all'] input[name='bdate_bdate']").val(date);
					$("#datepicker_all_bdate_btn").removeClass("hasDatepicker").children().remove();
					bdate_all_submit();
				}
			});
		}
	});
	
	// 일괄반납처리 버튼 외 클릭시 달력 사라지게 하기
	 $("html").click(function(e) {
            if(!$(e.target).parent().hasClass("all_bdate_btn") && !$(e.target).hasClass("all_bdate_btn")){
              $("#datepicker_all_bdate_btn").removeClass("hasDatepicker").children().remove();
           }                        
        }); 

	$( document ).tooltip({
      position: {
        my: "center bottom-20",
        at: "center top",
        using: function( position, feedback ) {
          $( this ).css( position );
          $( "<div>" )
            .addClass( "arrow" )
            .addClass( feedback.vertical )
            .addClass( feedback.horizontal )
            .appendTo( this );
        }
      }
    });


	//alert('작업중입니다.');
	// 레이어 팝업을 띄우기 위한 함수 $.ajaxLoding(페이지주소,보여줄 영역 아이디)
	$.ajaxLoding	= function(url,target,etcData){
		//alert('작업중입니다.');
		<?if(!$_GET['GCODE']){?>
		alert('기본인적사항을 입력해주세요.');
		<?}else{?>
			$("#loadingView").hide();
			if(url=='ren_menu1_01_pop_01_layer_tvat.php') $(".w3-modal-content").css("width","900px");
			if(url=='ren_menu1_01_pop_01_layer_smssend_temp.php') $(".w3-modal-content").css("width","86%");
			else $(".w3-modal-content").css("width","90%");

			$.ajax({
			  url: url+"?"+etcData,
			  cache : false,
			  dataType : "html",
			  method : "GET",
			  data: { ajaxType : true, GCODE:"<?=$_GET['GCODE']?>"},
			 //2020-08-10 headers : {"charset":"euc-kr"},
			}).done(function(htmlData) {
				$(target).html(htmlData);
				$("#modal1").show();
			});
		<?}?>
	};



	// 고객메모, 제품메모, 지도, 로드뷰 탭메뉴 클릭시
	$(".yb_tab_menu > li").click(function(){
		var liData	= $(this).parent().find("li");
		var leng = liData.length;
		var idx	= liData.index($(this));
		
		liData.each(function(){
			// 고객메모, 제품메모, 지도, 로드뷰 각각메뉴 배경이미지 기본세팅
			if(liData.index($(this))==0){
				$(this).css("background","url('<?=$conf['homeDir']?>/img/tb_titl_off.png')");
			}else{
				$(this).css("background","url('<?=$conf['homeDir']?>/img/tap_off.png')");
			}


			if(idx==liData.index($(this))){
				// 클릭한 메뉴 배경이미지 및 css 설정
				if(idx==0){
					$(this).css("background","url('<?=$conf['homeDir']?>/img/tb_titl.png')");
				}else{
					$(this).css("background","url('<?=$conf['homeDir']?>/img/tap_on.png')");
				}
				$(this).css("z-index","20");
				$(this).parent().parent().find(".yb_grey_box").hide().eq(idx).show();

				
			}else{
				$(this).css("z-index",leng);
				leng--;
			}
		});
		
	});

	gcode_reload('<?=$_GET['GCODE']?>','<?=$_GET['KCODE']?>');
//	kcode_reload('<?=$default_kcode?>');
	//setInterval(textblink,500);

});

function textblink(){
	$("#kwn_info_text_in").toggle();
	
}


var btnAction	= true;
	
$(document).ready(function(){
	var options = { 
		dataType:  'json',
		beforeSubmit:  showRequest_modal_bdate,  // pre-submit callback 
		success:       processJson_modal_bdate  // post-submit callback 
	}; 

	$('.bdateForm').ajaxForm(options); 	
});



// pre-submit callback 
function showRequest_modal_bdate(formData, jqForm, options) { 
	var queryString = $.param(formData); 
	//alert('About to submit: \n\n' + queryString); 
	return true; 
} 
 
// post-submit callback 
function processJson_modal_bdate(data) { 
	if(data.message){
		alert(data.message);
	}
	
	if(data.result==''){
		kcode_reload(data.kcode);
	}

}

</script>
  <?
  include($_SERVER['DOCUMENT_ROOT'].$conf['homeDir']."/include/source/bottom.php");
  ?>
