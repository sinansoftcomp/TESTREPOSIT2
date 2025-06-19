<?
include($_SERVER['DOCUMENT_ROOT']."/bin/include/source/head.php");

// 기본 페이지 셋팅
$page = ($_GET['page']) ? $_GET['page'] : 1;
$page_row	= 15;

$limit1 = ($page-1)*$page_row+1;
$limit2 = $page*$page_row;

$where = "";

if($_GET['lvl1']){
	$where .= " and a.bonbu = '".$_GET['lvl1']."'";
}
if($_GET['lvl2']){
	$where .= " and a.jisa = '".$_GET['lvl2']."'";
}
if($_GET['lvl3']){
	$where .= " and a.jijum = '".$_GET['lvl3']."'";
}
if($_GET['lvl4']){
	$where .= " and a.team = '".$_GET['lvl4']."'";
}

if($_GET['jik']){
	$where .= " and a.jik = '".$_GET['jik']."'";
}
if($_GET['pbit']){
	$where .= " and a.pbit = '".$_GET['pbit']."'";
}


if($_GET['sname']){
	$where .= " and a.sname like '%".$_GET['sname']."%'";
}

if($_GET["sgubun"]){
	$sgubun = $_GET["sgubun"];
	if($sgubun == "1"){
		$select = " ";
		$from = " swon a";
	}else{
		$select = " aa.inscode , insmaster.name insname , aa.bscode , ";
		$from = " inswon aa left outer join swon a on aa.scode = a.scode and aa.skey=a.skey
				left outer join insmaster on aa.inscode = insmaster.code ";
		if($_GET["code"]){
			$where .= "and  aa.inscode=".$_GET["code"];
		}
	}
}else{
	$sgubun = "1";
	$select = " ";
	$from = " swon a";
}

$sql	= "
select *
from(
	select ".$select."
		a.scode,
		a.skey,
		a.sname,
		a.snameeng,
		a.sspwd,
		a.sjuno,
		a.bonbu,
		b.bname,
		a.jisa,
		c.jsname,
		a.team,
		e.tname,
		a.sbit,
		a.birthbit,
		a.birth,
		a.indate,
		a.ydate,
		a.tdate,
		a.ibit,		
		a.tbit,
		a.pbit,
		isnull(a.bamt,0) bamt,
		a.bfdate,
		a.btdate,
		a.igubun,
		a.mcode,
		f.sname mcode_nm,
		a.bigo,
		a.dpart,
		a.place,
		a.pos,
		a.jik,
		a.grade,
		a.tel1,
		a.tel2,
		a.tel3,
		a.tel1+'-'+a.tel2+'-'+a.tel3 as tel,
		a.htel1+'-'+a.htel2+'-'+a.htel3 as htel,
		a.htel1,
		a.htel2,
		a.htel3,
		a.smsyn,
		a.email,
		a.post,
		a.addr,
		a.addr_dt,
				case when isnull(a.bonbu,'') != '' then b.bname else '' end +
				case when isnull(a.bonbu,'') != '' and (isnull(a.jisa,'') != '' or isnull(a.jijum,'') != '' or isnull(a.team,'') != '')  then ' > ' else '' end +
				case when isnull(a.jisa,'') != '' then c.jsname else '' end +
				case when isnull(a.jisa,'') != '' and isnull(a.jijum,'') != '' then ' > ' else '' end +
				case when isnull(a.jijum,'') != '' then g.jname else '' end +
				case when isnull(a.jijum,'') != '' and isnull(a.team,'') != '' then ' > ' else '' end +
				case when isnull(a.team,'') != '' then e.tname else '' end as sosok,
		case when isnull(a.btdate,'') != '' then DATEDIFF(DAY, GETDATE(), CONVERT(DATE,a.btdate)) else '' end bdday,
		row_number()over(order by a.skey desc) rnum
	from ".$from."
		left outer join bonbu b on a.scode = b.scode and a.bonbu = b.bcode
		left outer join jisa c on a.scode = c.scode and a.jisa = c.jscode
		left outer join jijum g on a.scode = g.scode and a.jijum = g.jcode
		left outer join team e on a.scode = e.scode and a.team = e.tcode
		left outer join swon f on a.scode = f.scode and a.mcode = f.skey
	where a.scode = '".$_SESSION['S_SCODE']."'  ".$where."
	 ) p WHERE rnum between ".$limit1." AND ".$limit2 ;

$qry	= sqlsrv_query( $mscon, $sql );
while( $fet = sqlsrv_fetch_array( $qry, SQLSRV_FETCH_ASSOC) ) {
	$listData[]	= $fet;
}
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
 // 데이터 총 건수
 //검색 데이터 구하기 
$sql= "
	select
		count(*) CNT
	from swon
	where scode = '".$_SESSION['S_SCODE']."'  " ;

$qry = sqlsrv_query( $mscon, $sql );
$totalResult  = sqlsrv_fetch_array($qry);

// 본부
$sql= "select bcode code , bname name
	   from bonbu
	   where scode = '".$_SESSION['S_SCODE']."'
	   order by bname ";

$qry= sqlsrv_query( $mscon, $sql );
$typeData	= array();
while( $fet = sqlsrv_fetch_array( $qry, SQLSRV_FETCH_ASSOC) ) {
  $typeData[] = $fet;
}


// 보험사 가져오기
$sql= "select inscode, name from inssetup where scode = '".$_SESSION['S_SCODE']."' and useyn = 'Y' order by num, inscode";
$qry= sqlsrv_query( $mscon, $sql );
$insData	= array();
while( $fet = sqlsrv_fetch_array( $qry, SQLSRV_FETCH_ASSOC) ) {
  $insData[] = $fet;
}

sqlsrv_free_stmt($qry);
sqlsrv_close($mscon);

// 페이지 클래스 시작
// 로드
include_once($conf['rootDir'].'/include/class/Pagination.php');

// 설정
$pagination = new Pagination(array(
		'base_url' => $_SERVER['PHP_SELF']."?sgubun=".$_GET['sgubun']."&sname=".$_GET['sname']."&lvl1=".$_GET['lvl1']."&lvl2=".$_GET['lvl2']."&lvl3=".$_GET['lvl3']."&lvl4=".$_GET['lvl4']."&jik=".$_GET['jik']."&pbit=".$_GET['pbit']."&code=".$_GET['code'],
		'per_page' => $page_row,
		'total_rows' => $totalResult['CNT'],
		'cur_page' => $page,
));

if($_GET["sgubun"]=="2"){
	$title_detail = "원수사사원별 상세리스트";
}else{
	$title_detail = "사원별 상세리스트";
}

?>
<style>
.tab_con_wrap .tit_wrap {margin-top: 10px;}
input::placeholder {
    text-align: center;
}
body{background-image: none;}
.container{margin:0px 0px 0px 10px;}
.box_wrap {margin-bottom:10px}
.tb_type01 th, .tb_type01 td {padding: 8px 0}
</style>


 <!--
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
<script type="text/javascript"  src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.2.min.js"></script>
<link rel="stylesheet" href="dist/style.min.css" />
<script src="dist/jstree.min.js"></script> 
--> 
 

<div class="container">
 
	<div class="content_wrap">
		<fieldset>
			<legend>사원관리</legend>
			<h2 class="tit_big">사원관리</h2>
				<div class="box_wrap sel_btn">
					<form name="searchFrm" method="get" action="<?$_SERVER['PHP_SELF']?>">

						<input type="radio" class="sgubun" name="sgubun" id="sgubun1" value="1" <?if(trim($sgubun)=='1') echo "checked";?>><label for="pbit1">사원별 </label>&nbsp;&nbsp;&nbsp;
						<input type="radio" class="sgubun" name="sgubun" id="sgubun2" value="2" <?if(trim($sgubun)=='2') echo "checked";?>><label for="pbit2">원수사 사원별</label>

						<select name="lvl1" id="lvl1" style="width:120px;margin-left:30px">
							<option value="">본부선택</option>
							<?foreach($typeData as $key => $val){?>
							<option value="<?=$val['code']?>" ><?=$val['name']?></option>
							<?}?>
						</select>
						<select name="lvl2" id="lvl2" style="width:120px;">
							<option value="">지사선택</option>
						</select>
						<select name="lvl3" id="lvl3" style="width:120px;">
							<option value="">지점선택</option>
						</select>
						<select name="lvl4" id="lvl4" style="width:120px;">
							<option value="">팀선택</option>
						</select>

						<select name="code" id="code" style="width:150px;margin-left:20px"> 		
						  <option value="">보험사</option>
						  <?foreach($insData as $key => $val){?>
						  <option value="<?=$val['inscode']?>" <?if($_GET['code']==$val['inscode']) echo "selected"?>><?=$val['name']?></option>
						  <?}?>
						</select>	
	
						<select name="jik" id="jik" style="width:120px;">
						  <option value="">직급선택</option>
						  <?foreach($conf['jik'] as $key => $val){?>
						  <option value="<?=$key?>" <?if($_GET['jik']==$key) echo "selected"?>><?=$val?></option>
						  <?}?>
						</select>

						<select name="pbit" id="pbit" style="width:120px;">
						  <option value="">수당지급여부</option>
						  <option value="1" <?if($_GET['pbit']=="1") echo "selected"?>>지급</option>
						  <option value="2" <?if($_GET['pbit']=="2") echo "selected"?>>미지급</option>
						</select>

						<input type="text" name="sname" id="sname" style="width:118px" value="<?=$_GET['sname']?>" placeholder="사원명" >
						<a href="#" class="btn_s navy btn_search" >조회</a>
					</form>
				</div>

			<div class="tit_wrap mt20;margin-top:25px">
				<div class="tit_wrap">
					<h3 class="tit_sub"><?=$title_detail?></h3>
					<span class="btn_wrap">
						<a href="#" class="btn_s navy" style="min-width:100px;" onclick="swon_new('');">신규등록</a>
					</span>
				</div>
				<!--
				<div class="data_left" id="tree-container" style="width:170px">
				</div>
				-->
				<div   >
					<div class="tb_type01" style="margin-top:10px">
						<table class="gridhover">
							<colgroup>
								<col style="width:6%">
								<col style="width:9%">

								<?if($sgubun=="2"){?>
									<col style="width:9%">
									<col style="width:9%">
								<?}?>
								<col style="width:auto">
								<col style="width:9%">

								<col style="width:6%">
								<col style="width:7%">
								<col style="width:8%">

								<col style="width:8%">
								<col style="width:10%">
								<col style="width:8%">
							</colgroup>
							<thead>
							<tr>				
								<th sortData="skey">사원번호</th>
								<th sortData="sname">사원명</th>	

								<?if($sgubun=="2"){?>
									<th sortData="insname">원수사명</th>
									<th sortData="bscode">원수사사원코드</th>
								<?}?>
								<th sortData="bname">소속</th>
								<th sortData="htel">휴대전화</th>

								<th sortData="jik">직책</th>
								<th sortData="dpart">부서</th>
								<th sortData="mcode_nm">리크루팅</th>

								<th sortData="bdday">보증보험금액</th>
								<th sortData="igubun">보증보험종료일자</th>
								<th sortData="bdday">보증보험잔여일수</th>
								
							</tr>
							</thead>
							<tbody>
							<?if(!empty($listData)){?>
							<?foreach($listData as $key => $val){extract($val);?>
							<tr onclick="swon_new('<?=$skey?>')">				
								<td style="text-align:left"><?=$skey?></td>
								<td><?=$sname?></td>

								<?if($sgubun=="2"){?>
									<td style="text-align:left"><?=$insname?></td>
									<td><?=$bscode?></td>
								<?}?>
								<td style="text-align:left"><?=$sosok?></td>
								<td><?=$htel?></td>

								<td><?=$conf['jik'][$jik]?></td>
								<td><?=$dpart?></td>
								<td><?=$mcode_nm?></td>

								<td align="right"><?=number_format($bamt).' 원'?></td>
								<td align="center"><?if(trim($btdate)) echo date("Y-m-d",strtotime($btdate));?></td>
								<td style="text-align:right">
										<?if($bdday>=0){echo $bdday?>일<?}else {echo str_replace('-','',$bdday) ?>일 경과<?}?>
								</td>
							</tr>
							<?}}?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
				<div style="text-align: center">		
					<ul class="pagination pagination-sm" style="margin: 20px">
					  <?=$pagination->create_links();?>
					</ul>
				</div>

				<!-- 모달 -->
				<div id="modal2" class="layerBody_swon">

				</div>

		</fieldset>
	</div><!-- // content_wrap -->
	<div id="layer" style="display:none;position:fixed;overflow:hidden;z-index:2;-webkit-overflow-scrolling:touch;">
		<img src="//t1.daumcdn.net/postcode/resource/images/close.png" id="btnCloseLayer" style="cursor:pointer;position:absolute;right:-3px;top:-3px;z-index:2" onclick="closeDaumPostcode()" alt="닫기 버튼">
	</div>

</div>

<span id="guide" style="color:#999;display:none"></span>
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

<script type="text/javascript">

// 우편번호 찾기 화면을 넣을 element
var element_layer = document.getElementById('layer');

function closeDaumPostcode() {
	// iframe을 넣은 element를 안보이게 한다.
	element_layer.style.display = 'none';
}

function DaumPostcode() {
	new daum.Postcode({
		oncomplete: function(data) {
			// 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.

			// 각 주소의 노출 규칙에 따라 주소를 조합한다.
			// 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
			var fullAddr = data.address; // 최종 주소 변수
			var extraAddr = ''; // 조합형 주소 변수

			// 기본 주소가 도로명 타입일때 조합한다.
			if(data.addressType === 'R'){
				//법정동명이 있을 경우 추가한다.
				if(data.bname !== ''){
					extraAddr += data.bname;
				}
				// 건물명이 있을 경우 추가한다.
				if(data.buildingName !== ''){
					extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
				}
				// 조합형주소의 유무에 따라 양쪽에 괄호를 추가하여 최종 주소를 만든다.
				fullAddr += (extraAddr !== '' ? ' ('+ extraAddr +')' : '');
			}

			// 우편번호와 주소 정보를 해당 필드에 넣는다.
			document.getElementById('post').value = data.zonecode;
			document.getElementById('addr').value = data.address;
			//document.getElementById('bcode').value = data.bcode;

			document.getElementById('addr_dt').focus();

			// iframe을 넣은 element를 안보이게 한다.
			// (autoClose:false 기능을 이용한다면, 아래 코드를 제거해야 화면에서 사라지지 않는다.)
			var guideTextBox = document.getElementById("guide");
			guideTextBox.style.display = 'none';

			//element_layer.style.display = 'none';
		},
		width : '100%',
		height : '100%',
		maxSuggestItems : 5
	}).open();

	// iframe을 넣은 element를 보이게 한다.
	//element_layer.style.display = 'block';

	// iframe을 넣은 element의 위치를 화면의 가운데로 이동시킨다.
	//initLayerPosition();
}

// 브라우저의 크기 변경에 따라 레이어를 가운데로 이동시키고자 하실때에는
// resize이벤트나, orientationchange이벤트를 이용하여 값이 변경될때마다 아래 함수를 실행 시켜 주시거나,
// 직접 element_layer의 top,left값을 수정해 주시면 됩니다.
function initLayerPosition(){
	var width = 500; //우편번호서비스가 들어갈 element의 width
	var height = 650; //우편번호서비스가 들어갈 element의 height
	var borderWidth = 5; //샘플에서 사용하는 border의 두께

	// 위에서 선언한 값들을 실제 element에 넣는다.
	element_layer.style.width = width + 'px';
	element_layer.style.height = height + 'px';
	element_layer.style.border = borderWidth + 'px solid';
	// 실행되는 순간의 화면 너비와 높이 값을 가져와서 중앙에 뜰 수 있도록 위치를 계산한다.
	element_layer.style.left = (((window.innerWidth || document.documentElement.clientWidth) - width)/2 - borderWidth) + 'px';
	element_layer.style.top = (((window.innerHeight || document.documentElement.clientHeight) - height)/2 - borderWidth) + 'px';
}




// 사원관리 모달창
function swon_new(code){
	var sgubun = $("#sgubun").val();
	var sname = $("#sname").val();
	
	$.ajaxLoding('ga_menu1_06_swon_pop.php?sgubun='+sgubun+'&sname='+sname,$('.layerBody_swon'),$('#modal2'),'&SKEY='+code);	
}


<script type="text/javascript">



 function get_jstree() {

    $("#tree-container").jstree({  
            'core': {
	                'data' : {
								"url"	 : "/bin/sub/test/response.php",
								"dataType" : "json"	
					}
                } 
		});

}



$(document).ready(function(){
	//get_jstree();
	// 조회
	$(".btn_search").click(function(){
		$("form[name='searchFrm']").attr("method","get");
		$("form[name='searchFrm']").attr("target","");
		$("form[name='searchFrm']").submit();
	}); 

/*
	$('#sgubun').change(function(){
		$("form[name='searchFrm']").attr("method","get");
		$("form[name='searchFrm']").attr("target","");
		$("form[name='searchFrm']").submit();
	});
*/
	$("input[name='sgubun']").change(function(){
		$("form[name='searchFrm']").attr("method","get");
		$("form[name='searchFrm']").attr("target","");
		$("form[name='searchFrm']").submit();
	});

	if('<?=$_GET["sgubun"]?>'=="2"){
		$("#code").show();
	}else{
		$("#code").hide();
		document.getElementById('code').value = "";
	}


	document.searchFrm.lvl1.value='<?=$_GET["lvl1"]?>';	

	if(!isEmpty('<?=$_GET["lvl1"]?>')){
		var lvl1 = $('#lvl1').val();
		$.ajaxSetup({ async:false });	
		$.post("/bin/sub/menu1/ga_menu1_06_lvl1.php",{optVal:lvl1}, function(data) {

			$('#lvl2').empty();				
			$('#lvl2').append('<option value="">지사선택</option>');
			$('#lvl2').append(data);

			$('#lvl3').empty();
			$('#lvl3').append('<option value="">지점선택</option>');

			$('#lvl4').empty();
			$('#lvl4').append('<option value="">팀선택</option>');

		});		

		document.searchFrm.lvl2.value='<?=$_GET["lvl2"]?>';
	}

	if(!isEmpty($('#lvl2').val())){
		var lvl2 = $('#lvl2').val();
		$.post("/bin/sub/menu1/ga_menu1_06_lvl2.php",{optVal:lvl2}, function(data) {
			$('#lvl3').empty();
			$('#lvl3').append('<option value="">지점선택</option>');
			$('#lvl3').append(data);

			$('#lvl4').empty();
			$('#lvl4').append('<option value="">팀선택</option>');
		});			

		document.searchFrm.lvl3.value='<?=$_GET["lvl3"]?>';
	}

	if(!isEmpty($('#lvl3').val())){
		var lvl2 = $('#lvl3').val();
		$.post("/bin/sub/menu1/ga_menu1_06_lvl3.php",{optVal:lvl2}, function(data) {
			$('#lvl4').empty();
			$('#lvl4').append('<option value="">팀선택</option>');
			$('#lvl4').append(data);

		});			

		document.searchFrm.lvl4.value='<?=$_GET["lvl4"]?>';
	}

	// 본부 선택시 동적 셀렉트
	$('#lvl1').on('change', function(){ 
		var lvl1 = this.value;
		// 소분류 선택시 데이터 가져오는 부분때문에 동기처리
		$.ajaxSetup({ async:false });	
		$.post("/bin/sub/menu1/ga_menu1_06_lvl1.php",{optVal:this.value}, function(data) {

			$('#lvl2').empty();				
			$('#lvl2').append('<option value="">지사선택</option>');
			$('#lvl2').append(data);

			$('#lvl3').empty();
			$('#lvl3').append('<option value="">지점선택</option>');

			$('#lvl4').empty();
			$('#lvl4').append('<option value="">팀선택</option>');

		});				
	});

	// 지사 선택시 동적 셀렉트
	$('#lvl2').on('change', function(){ 

		var lvl2 = this.value;
			
		$.post("/bin/sub/menu1/ga_menu1_06_lvl2.php",{optVal:this.value}, function(data) {

			$('#lvl3').empty();
			$('#lvl3').append('<option value="">지점선택</option>');
			$('#lvl3').append(data);

			$('#lvl4').empty();
			$('#lvl4').append('<option value="">팀선택</option>');
		});				
	});

	// 지점 선택시 동적 셀렉트
	$('#lvl3').on('change', function(){ 

		var lvl3 = this.value;
			
		$.post("/bin/sub/menu1/ga_menu1_06_lvl3.php",{optVal:this.value}, function(data) {

			$('#lvl4').empty();
			$('#lvl4').append('<option value="">팀선택</option>');
			$('#lvl4').append(data);

		});				
	});

	// 그리드 리사이즈
	$( window ).resize(function() {
		
		windowResize($(this));

	});
	
	var windowResize	= function(win){
		if($(win).height()>1000){
			$(".tb_type01").height($(win).height()-300);
		}else{
			$(".tb_type01").height(610);
		}
		
	};
	windowResize($( window ));


	//kim();	
	//ajaxLodingTarket();
	//ajaxLodingTarket('/gaplus/bin/sub/test/index.php',$('.data_left'),'');
 
	
});





 

 

</script>

<?
include($_SERVER['DOCUMENT_ROOT'].$conf['homeDir']."/include/source/bottom.php");
?>