<?
include($_SERVER['DOCUMENT_ROOT']."/bin/include/source/head.php");

// �⺻ ������ ����
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
 // ������ �� �Ǽ�
 //�˻� ������ ���ϱ� 
$sql= "
	select
		count(*) CNT
	from swon
	where scode = '".$_SESSION['S_SCODE']."'  " ;

$qry = sqlsrv_query( $mscon, $sql );
$totalResult  = sqlsrv_fetch_array($qry);

// ����
$sql= "select bcode code , bname name
	   from bonbu
	   where scode = '".$_SESSION['S_SCODE']."'
	   order by bname ";

$qry= sqlsrv_query( $mscon, $sql );
$typeData	= array();
while( $fet = sqlsrv_fetch_array( $qry, SQLSRV_FETCH_ASSOC) ) {
  $typeData[] = $fet;
}


// ����� ��������
$sql= "select inscode, name from inssetup where scode = '".$_SESSION['S_SCODE']."' and useyn = 'Y' order by num, inscode";
$qry= sqlsrv_query( $mscon, $sql );
$insData	= array();
while( $fet = sqlsrv_fetch_array( $qry, SQLSRV_FETCH_ASSOC) ) {
  $insData[] = $fet;
}

sqlsrv_free_stmt($qry);
sqlsrv_close($mscon);

// ������ Ŭ���� ����
// �ε�
include_once($conf['rootDir'].'/include/class/Pagination.php');

// ����
$pagination = new Pagination(array(
		'base_url' => $_SERVER['PHP_SELF']."?sgubun=".$_GET['sgubun']."&sname=".$_GET['sname']."&lvl1=".$_GET['lvl1']."&lvl2=".$_GET['lvl2']."&lvl3=".$_GET['lvl3']."&lvl4=".$_GET['lvl4']."&jik=".$_GET['jik']."&pbit=".$_GET['pbit']."&code=".$_GET['code'],
		'per_page' => $page_row,
		'total_rows' => $totalResult['CNT'],
		'cur_page' => $page,
));

if($_GET["sgubun"]=="2"){
	$title_detail = "���������� �󼼸���Ʈ";
}else{
	$title_detail = "����� �󼼸���Ʈ";
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
			<legend>�������</legend>
			<h2 class="tit_big">�������</h2>
				<div class="box_wrap sel_btn">
					<form name="searchFrm" method="get" action="<?$_SERVER['PHP_SELF']?>">

						<input type="radio" class="sgubun" name="sgubun" id="sgubun1" value="1" <?if(trim($sgubun)=='1') echo "checked";?>><label for="pbit1">����� </label>&nbsp;&nbsp;&nbsp;
						<input type="radio" class="sgubun" name="sgubun" id="sgubun2" value="2" <?if(trim($sgubun)=='2') echo "checked";?>><label for="pbit2">������ �����</label>

						<select name="lvl1" id="lvl1" style="width:120px;margin-left:30px">
							<option value="">���μ���</option>
							<?foreach($typeData as $key => $val){?>
							<option value="<?=$val['code']?>" ><?=$val['name']?></option>
							<?}?>
						</select>
						<select name="lvl2" id="lvl2" style="width:120px;">
							<option value="">���缱��</option>
						</select>
						<select name="lvl3" id="lvl3" style="width:120px;">
							<option value="">��������</option>
						</select>
						<select name="lvl4" id="lvl4" style="width:120px;">
							<option value="">������</option>
						</select>

						<select name="code" id="code" style="width:150px;margin-left:20px"> 		
						  <option value="">�����</option>
						  <?foreach($insData as $key => $val){?>
						  <option value="<?=$val['inscode']?>" <?if($_GET['code']==$val['inscode']) echo "selected"?>><?=$val['name']?></option>
						  <?}?>
						</select>	
	
						<select name="jik" id="jik" style="width:120px;">
						  <option value="">���޼���</option>
						  <?foreach($conf['jik'] as $key => $val){?>
						  <option value="<?=$key?>" <?if($_GET['jik']==$key) echo "selected"?>><?=$val?></option>
						  <?}?>
						</select>

						<select name="pbit" id="pbit" style="width:120px;">
						  <option value="">�������޿���</option>
						  <option value="1" <?if($_GET['pbit']=="1") echo "selected"?>>����</option>
						  <option value="2" <?if($_GET['pbit']=="2") echo "selected"?>>������</option>
						</select>

						<input type="text" name="sname" id="sname" style="width:118px" value="<?=$_GET['sname']?>" placeholder="�����" >
						<a href="#" class="btn_s navy btn_search" >��ȸ</a>
					</form>
				</div>

			<div class="tit_wrap mt20;margin-top:25px">
				<div class="tit_wrap">
					<h3 class="tit_sub"><?=$title_detail?></h3>
					<span class="btn_wrap">
						<a href="#" class="btn_s navy" style="min-width:100px;" onclick="swon_new('');">�űԵ��</a>
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
								<th sortData="skey">�����ȣ</th>
								<th sortData="sname">�����</th>	

								<?if($sgubun=="2"){?>
									<th sortData="insname">�������</th>
									<th sortData="bscode">���������ڵ�</th>
								<?}?>
								<th sortData="bname">�Ҽ�</th>
								<th sortData="htel">�޴���ȭ</th>

								<th sortData="jik">��å</th>
								<th sortData="dpart">�μ�</th>
								<th sortData="mcode_nm">��ũ����</th>

								<th sortData="bdday">��������ݾ�</th>
								<th sortData="igubun">����������������</th>
								<th sortData="bdday">���������ܿ��ϼ�</th>
								
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

								<td align="right"><?=number_format($bamt).' ��'?></td>
								<td align="center"><?if(trim($btdate)) echo date("Y-m-d",strtotime($btdate));?></td>
								<td style="text-align:right">
										<?if($bdday>=0){echo $bdday?>��<?}else {echo str_replace('-','',$bdday) ?>�� ���<?}?>
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

				<!-- ��� -->
				<div id="modal2" class="layerBody_swon">

				</div>

		</fieldset>
	</div><!-- // content_wrap -->
	<div id="layer" style="display:none;position:fixed;overflow:hidden;z-index:2;-webkit-overflow-scrolling:touch;">
		<img src="//t1.daumcdn.net/postcode/resource/images/close.png" id="btnCloseLayer" style="cursor:pointer;position:absolute;right:-3px;top:-3px;z-index:2" onclick="closeDaumPostcode()" alt="�ݱ� ��ư">
	</div>

</div>

<span id="guide" style="color:#999;display:none"></span>
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

<script type="text/javascript">

// �����ȣ ã�� ȭ���� ���� element
var element_layer = document.getElementById('layer');

function closeDaumPostcode() {
	// iframe�� ���� element�� �Ⱥ��̰� �Ѵ�.
	element_layer.style.display = 'none';
}

function DaumPostcode() {
	new daum.Postcode({
		oncomplete: function(data) {
			// �˻���� �׸��� Ŭ�������� ������ �ڵ带 �ۼ��ϴ� �κ�.

			// �� �ּ��� ���� ��Ģ�� ���� �ּҸ� �����Ѵ�.
			// �������� ������ ���� ���� ��쿣 ����('')���� �����Ƿ�, �̸� �����Ͽ� �б� �Ѵ�.
			var fullAddr = data.address; // ���� �ּ� ����
			var extraAddr = ''; // ������ �ּ� ����

			// �⺻ �ּҰ� ���θ� Ÿ���϶� �����Ѵ�.
			if(data.addressType === 'R'){
				//���������� ���� ��� �߰��Ѵ�.
				if(data.bname !== ''){
					extraAddr += data.bname;
				}
				// �ǹ����� ���� ��� �߰��Ѵ�.
				if(data.buildingName !== ''){
					extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
				}
				// �������ּ��� ������ ���� ���ʿ� ��ȣ�� �߰��Ͽ� ���� �ּҸ� �����.
				fullAddr += (extraAddr !== '' ? ' ('+ extraAddr +')' : '');
			}

			// �����ȣ�� �ּ� ������ �ش� �ʵ忡 �ִ´�.
			document.getElementById('post').value = data.zonecode;
			document.getElementById('addr').value = data.address;
			//document.getElementById('bcode').value = data.bcode;

			document.getElementById('addr_dt').focus();

			// iframe�� ���� element�� �Ⱥ��̰� �Ѵ�.
			// (autoClose:false ����� �̿��Ѵٸ�, �Ʒ� �ڵ带 �����ؾ� ȭ�鿡�� ������� �ʴ´�.)
			var guideTextBox = document.getElementById("guide");
			guideTextBox.style.display = 'none';

			//element_layer.style.display = 'none';
		},
		width : '100%',
		height : '100%',
		maxSuggestItems : 5
	}).open();

	// iframe�� ���� element�� ���̰� �Ѵ�.
	//element_layer.style.display = 'block';

	// iframe�� ���� element�� ��ġ�� ȭ���� ����� �̵���Ų��.
	//initLayerPosition();
}

// �������� ũ�� ���濡 ���� ���̾ ����� �̵���Ű���� �ϽǶ�����
// resize�̺�Ʈ��, orientationchange�̺�Ʈ�� �̿��Ͽ� ���� ����ɶ����� �Ʒ� �Լ��� ���� ���� �ֽðų�,
// ���� element_layer�� top,left���� ������ �ֽø� �˴ϴ�.
function initLayerPosition(){
	var width = 500; //�����ȣ���񽺰� �� element�� width
	var height = 650; //�����ȣ���񽺰� �� element�� height
	var borderWidth = 5; //���ÿ��� ����ϴ� border�� �β�

	// ������ ������ ������ ���� element�� �ִ´�.
	element_layer.style.width = width + 'px';
	element_layer.style.height = height + 'px';
	element_layer.style.border = borderWidth + 'px solid';
	// ����Ǵ� ������ ȭ�� �ʺ�� ���� ���� �����ͼ� �߾ӿ� �� �� �ֵ��� ��ġ�� ����Ѵ�.
	element_layer.style.left = (((window.innerWidth || document.documentElement.clientWidth) - width)/2 - borderWidth) + 'px';
	element_layer.style.top = (((window.innerHeight || document.documentElement.clientHeight) - height)/2 - borderWidth) + 'px';
}




// ������� ���â
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
	// ��ȸ
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
			$('#lvl2').append('<option value="">���缱��</option>');
			$('#lvl2').append(data);

			$('#lvl3').empty();
			$('#lvl3').append('<option value="">��������</option>');

			$('#lvl4').empty();
			$('#lvl4').append('<option value="">������</option>');

		});		

		document.searchFrm.lvl2.value='<?=$_GET["lvl2"]?>';
	}

	if(!isEmpty($('#lvl2').val())){
		var lvl2 = $('#lvl2').val();
		$.post("/bin/sub/menu1/ga_menu1_06_lvl2.php",{optVal:lvl2}, function(data) {
			$('#lvl3').empty();
			$('#lvl3').append('<option value="">��������</option>');
			$('#lvl3').append(data);

			$('#lvl4').empty();
			$('#lvl4').append('<option value="">������</option>');
		});			

		document.searchFrm.lvl3.value='<?=$_GET["lvl3"]?>';
	}

	if(!isEmpty($('#lvl3').val())){
		var lvl2 = $('#lvl3').val();
		$.post("/bin/sub/menu1/ga_menu1_06_lvl3.php",{optVal:lvl2}, function(data) {
			$('#lvl4').empty();
			$('#lvl4').append('<option value="">������</option>');
			$('#lvl4').append(data);

		});			

		document.searchFrm.lvl4.value='<?=$_GET["lvl4"]?>';
	}

	// ���� ���ý� ���� ����Ʈ
	$('#lvl1').on('change', function(){ 
		var lvl1 = this.value;
		// �Һз� ���ý� ������ �������� �κж����� ����ó��
		$.ajaxSetup({ async:false });	
		$.post("/bin/sub/menu1/ga_menu1_06_lvl1.php",{optVal:this.value}, function(data) {

			$('#lvl2').empty();				
			$('#lvl2').append('<option value="">���缱��</option>');
			$('#lvl2').append(data);

			$('#lvl3').empty();
			$('#lvl3').append('<option value="">��������</option>');

			$('#lvl4').empty();
			$('#lvl4').append('<option value="">������</option>');

		});				
	});

	// ���� ���ý� ���� ����Ʈ
	$('#lvl2').on('change', function(){ 

		var lvl2 = this.value;
			
		$.post("/bin/sub/menu1/ga_menu1_06_lvl2.php",{optVal:this.value}, function(data) {

			$('#lvl3').empty();
			$('#lvl3').append('<option value="">��������</option>');
			$('#lvl3').append(data);

			$('#lvl4').empty();
			$('#lvl4').append('<option value="">������</option>');
		});				
	});

	// ���� ���ý� ���� ����Ʈ
	$('#lvl3').on('change', function(){ 

		var lvl3 = this.value;
			
		$.post("/bin/sub/menu1/ga_menu1_06_lvl3.php",{optVal:this.value}, function(data) {

			$('#lvl4').empty();
			$('#lvl4').append('<option value="">������</option>');
			$('#lvl4').append(data);

		});				
	});

	// �׸��� ��������
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