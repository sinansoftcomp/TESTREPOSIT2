<?
include($_SERVER['DOCUMENT_ROOT']."/rental/include/source/head.php");

// ���ӱ���
$ugubun = $_SESSION['S_UGUBUN'];

$YDONG=$_SESSION['S_YDONG'];


// ���ݰ�꼭 ���� (���̿��� = 1, Ȩ�ý��� = 2)
$sql= "select taxbit from sinan_water.dbo.company where scode = '".$_SESSION['S_SCODE']."' ";

$result  = sqlsrv_query( $mscon, $sql );
$row =  sqlsrv_fetch_array($result);

$taxbit = $row[taxbit];

if(!$taxbit){
	$taxbit = '1';
}



if($_GET['GCODE']){
	// �⺻ �������� ������
	$sql	= "select a.*, b.SNAME from 
	kwngo a 
	left outer join swon b on a.scode=b.scode and a.ASKEY=b.SKEY
	where a.SCODE='".$_SESSION['S_SCODE']."' and a.GCODE='".$_GET['GCODE']."'";
	$qry	= sqlsrv_query( $mscon, $sql );
	extract($fet	= sqlsrv_fetch_array($qry));

	if(!$fet['GCODE']) alert("���� �����Ǿ��ų� ������ �����ϴ�.","close");

	// ���� �� �ܾ�
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
	--��/�ݾ�.
	";

	$qry	= sqlsrv_query( $mscon, $sql );
	$jan_data	= sqlsrv_fetch_array($qry);



$sql	= "
	SELECT A.GCODE, A.FAMT,  --��Ż�Ѿ�
			B.IPTOT, --�Ա���
			ISNULL(A.FAMT,0) - ISNULL(B.IPTOT,0) FAMT_JAN  --��Ż�ܾ� 
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

// Ÿ��Ʋ �̹����� ���� ��ȯ
$pageTemp	= explode("/",$_SERVER['PHP_SELF']);
$imageName	= str_replace(".php","",$pageTemp[count($pageTemp)-1]);
?>

<script>
	window.moveTo(window.screen.width/2-625, window.screen.height/2-490);  
	window.resizeTo("1250", "970");                             // ������ ��������
	
</script>






<div style="width:1200px">



   <header class="yb_title">
    <h2><img src="<?=$conf['homeDir']?>/img/<?=$imageName?>.gif"></h2>
	<!-- �������� ������ left������ �¿��� ��ġ ����-->
<!-- 	<span class="w3-tooltip movie" style="left: 130px; bottom:19px;"><i class="fa fa-caret-square-o-right"></i> -->
<!--     <span class="w3-text w3-tag movie_tooltip"><?=$MovieInfo[$imageName]['text']?></span></span>   -->
    <!-- �������� ������ �� -->
	<!--<div style="position: absolute;top: 32px;left: 371px;color: red;width: 269px;text-align: right;">�̼��ݾ� : <?=number_format($misu);?>��</div>-->
	<div style="position: absolute;top: 32px;left: 371px;color: red;width: 269px;text-align: right;"><strong><?=$kerr;?></strong></div> 
    <div style="position: absolute;top: 24px;left: 781px;">
      <button type="button" class="yb_btn_grey" onclick="$.ajaxLoding('./ren_menu1_01_pop_01_layer_smssend_temp.php',$('.layerBody'),'&bal_no=<?=$TEL1?>-<?=$TEL2?>-<?=$TEL3?>&addresseeNumber=<?=$HTEL1?>-<?=$HTEL2?>-<?=$HTEL3?>&prg_id=join&KNAME=<?=$KNAME?>')"><span>SMS�߼�</span></button>
	  <?if($taxbit == '1'){?>
		<button type="button" class="yb_btn_grey" onclick="$.ajaxLoding('/rental/sub/menu8/ren_menu8_03_layer_tvat.php',$('.layerBody'),'&KNAME=<?=$KNAME?>')"><span>���ݰ�꼭</span></button>
	  <?}else{?>
		<button type="button" class="yb_btn_grey" onclick="$.ajaxLoding('/rental/sub/menu8/ren_menu8_06_layer_tvat.php',$('.layerBody'),'&KNAME=<?=$KNAME?>')"><span>���ݰ�꼭</span></button>
	  <?}?>
      <button type="button" class="yb_btn_grey" onclick="$.ajaxLoding('/rental/sub/menu8/ren_menu8_01_layer_cash.php',$('.layerBody'),'&KNAME=<?=$KNAME?>')"><span>���ݿ�����</span></button>
      <button type="button" onclick="self.close();" class="yb_btn_grey"><span>�ݱ�</span></button>
    </div>
  </header>
  <div class="w3-row" style="margin-top:10px;">
	<div class="yb_round_box" style="height:135px;border-radius: 10px 10px 0px 0px;border-bottom: 0px;">
        <div style="border:1px solid #017dbd;padding-bottom: 10px;height:100%;border-radius: 7px 7px 0px 0px;border-bottom: 0px;">
			<section class="input_table">
			   <h6>������</h6>
			   <div class="btn_group">
					<button type="button" class="yb_btn addUser"><span><i class="fa fa-plus"></i>������</span></button>
					<button type="button" class="yb_btn searchUser"><span><i class="fa fa-search"></i>�� �˻�</span></button>
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
							<th>����ȣ</th>
							<td><?=$GCODE?></td>
							<th>����</th>
							<td id="kwn_kname_1" colspan=3><?=$KNAME?></td>
							<th>��ǥ��</th>
							<td><?=$KSNAME?></td>
							<th>����ڹ�ȣ</th>
							<td><?=$SNUM?></td>
							<th>����</th>
							<td><?=$UPTAE?></td>
							
						</tr>
						<tr>
							<th>�ŷ�ó�����</th>
							<td><?=$CDNAME?></td>
							<th>����</th>
							<td><?=$AREA?></td>
							<th>�����</th>
							<td><?=$SNAME?></td>
							<th>����ó</th>
							<td><?=$TEL1?>-<?=$TEL2?>-<?=$TEL3?></td>
							<th>��󿬶�ó</th>
							<td><?=$BTEL1?>-<?=$BTEL2?>-<?=$BTEL3?></td>
							
							<th>����</th>
							<td><?=$UPJONG?></td>
						</tr>
						<tr>
							<th>�ּ�</th>
							<td colspan=5>(<?=$POST?>) <?=$ADDR?> <?=$ADDR_DT?></td>
							<th>�޴���ȭ</th>
							<td><?=$HTEL1?>-<?=$HTEL2?>-<?=$HTEL3?>	</td>
							<th>��û���ܾ�</th>
							<td style="text-align:right;color:blue"><?=number_format($jan_data['KWN_CNT'])?> / <?=number_format($jan_data['JAN_AMT'])?></td>
							<th>�̸���</th>
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
	        
	        <!-- �⺻�������� -->          
           
           
           
           
           
           <!-- ��Ż��Ȳ -->


           <section class="input_table">
	           <h6>��Ż��Ȳ</h6>
				<!-- �Ա��հ� -->
				<div style="position:absolute;top: 16px;left: 140px;font-size: 12px;">
					<span class="dot_img" style="width: 6px;"><img src="/rental/img/dot_orange.png"></span>
					<span style="color:blue;margin-right:20px;">��Ż�հ� : <?=number_format($ipTotal['FAMT']);?></span>
					<span class="dot_img" style="width: 6px;"><img src="/rental/img/dot_orange.png"></span>
					<span style="color:red;margin-right:20px;">�Ա��� : <?=number_format($ipTotal['IPTOT']);?></span>
					<span class="dot_img" style="width: 6px;"><img src="/rental/img/dot_orange.png"></span>
					<span style="color:blue">��Ż�ܾ� : <?=number_format($ipTotal['FAMT_JAN']);?></span>
				</div>

			   <div class="yb_grey_box kwnlist" style="width: 100%;height: 100px;overflow-x: hidden;overflow-y: auto;"></div>
	           
		   </section>
           
		   <!-- ��Ż���� -->
           <section class="input_table">
				<h6>��Ż����</h6>
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
							 <button type="button" onclick="mfilePop(1);" class="yb_btn" ><span>��������� ��û</span></button>
							 </TD>
					<button type="button" class="yb_btn" onclick="kcode_reload('')"><span>�ű�</span></button>
					<button type="button" class="yb_btn" onclick="kwn_del();"><span>����</span></button>
					<button type="button" class="yb_btn" onclick="kwn_update();"><span>����</span></button>
					<? if ($_SERVER['REMOTE_ADDR'] =='121.137.89.13'){?>
					<!--div class="btn_group">
						
					</div-->
					<?}?>
				</div>
				
				<div class="kwnBody"></div>
	           
		   </section>

		   <!-- ��Ż�� ��ǰ���� -->
           <section class="input_table">
				<form name="bdate_all" class="bdateForm" method="post" action="ren_menu1_01_action_bdate_all.php">
					<input type="hidden" name="bdate_gcode" value="<?=$_GET['GCODE']?>">
					<input type="hidden" name="bdate_kcode" value="">
					<input type="hidden" name="bdate_bdate" value="">
					<input type="hidden" name="bdate_type" value="up">
				</form>
			   <div class="btn_group">
					<div id="datepicker_all_bdate_btn" style="position: absolute;left: -126px;"></div>
				   <button type="button" class="yb_btn all_bdate_btn"><span>�ϰ��ݳ�ó��</span></button>
					<button type="button" class="yb_btn" onclick="itemList_new();"><span>��ǰ�߰�</span></button>
				</div>
				<div>
		           <ul class="yb_tab_menu">
			           <li>��ǰ����</li>
			           <li style="left: -20px;z-index: 1;">�߰�û����Ȳ</li>
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
			
			<!-- û������ -->
           <section class="input_table">
	           <h6>û������</h6>
			   <div class="choBody" style="width: 100%;height: 86px;overflow-x: hidden;overflow-y: auto;">
					    
				</div>
	           
		   </section>

           <!-- �Ա���Ȳ -->
           <!-- CMS �����Ȳ -->
           <section class="input_table ">
				<div class="btn_group">
		           <button type="button" class="yb_btn" onclick="ipmst_new();"><span>�Աݵ��</span></button>
	           </div>
	           <div>
		           <ul class="yb_tab_menu">
			           <li>�Ա���Ȳ</li>
			           <li style="left: -20px;z-index: 1;">CMS �����Ȳ</li>
		           </ul>
		           <div class="yb_grey_box ipmstBody" style="width: 100%;height: 300px;overflow-x: hidden;overflow-y: auto;">
					    
				   </div>
		           <div class="yb_grey_box acmsBody" style="display: none;width: 100%;height: 300px;overflow-x: hidden;overflow-y: auto;">
					
				   </div>
	           </div>
	           
	           
           </section>
		   
		   		   
           <!-- AS���� -->
           <!-- ���޸� -->
           <section class="input_table ">
				
	           
	           <h6 style="width:142px;background-image: url(/rental/img/tableLabel_bg2.png);">AS.���.��������</h6>
			   <div class="btn_group">
		           <button type="button" class="yb_btn" onclick="$.ajaxLoding('ren_menu1_01_pop_01_layer_asmst.php',$('.layerBody'),'')"><span>AS.�����</span></button>
	           </div>
			   <div class="yb_grey_box asmstBody" style="width: 100%;height: 161px;overflow-x: hidden;overflow-y: auto;">
					   
			    </div>
           </section>


        </div>

      </div>
    </div>
  </div>
</div>




<!-- ��� -->
<div id="modal1" class="w3-modal" style="z-index:1000;padding-top:5%;">
  <div class="w3-modal-content w3-animate-top" style="width: 90%;height: 90%;border: 3px solid orange;overflow-x: hidden;overflow-y: auto;">
<!--     <header class="w3-container w3-teal">  -->
<!--       <span onclick="$('#modal1').hide()" class="w3-closebtn">��</span> -->
<!--     </header> -->
    <div class="w3-container layerBody ">
      
    </div>
	<footer class="w3-container" style="height:0px;"> 
      
    </footer>
  </div>
</div>

<script>




///�����������û
function mfilePop(sbit){
	
	///��ü ����� ���� üũ. ydong  y �� ����  n �̸� �̵���
	var ydong = $("#YDONG").val();

	//�̵��Ǹ� ������� â ����
	if ( ydong == "N" ||  ydong == " "){
		var left = Math.ceil((window.screen.width - 1000)/2);
		var top = Math.ceil((window.screen.height - 1000)/2);
		var popOpen	= window.open("ren_menu1_01_pop_mfile_ydong.php", "ydongpopup","width=500px,height=700px,top="+top+",left="+left+",status=0,toolbar=0,menubar=0,location=false");
		popOpen.focus();
		return false;
	}
	
	var kcode = $("#kwn_KCODE").val();
	if (kcode == "" || kcode == " "){
		alert("������ �� ������� ���ֽñ� �ٶ��ϴ�.");
		return false;
	}
	var sbit  = $("#KSBIT").val();
//$ugubun  ���α׷� ���� �Ϲ�CMS11 ������CMS12 ����CMS21 ����������22 ��Ż(����) 31 �ｺ�ɾ�(����) 41
//	var ugubun = '<?=$ugubun?>';
	if (sbit !="1" && sbit !="7"){
			alert("��� ����� CMS/ī��CMS  �� ��츸 ����� ������û�� �����մϴ�.");	
		return false;
	}

	//alert(sbit);
	var left = Math.ceil((window.screen.width - 1000)/2);
	var top = Math.ceil((window.screen.height - 1000)/2);
	var popOpen	= window.open("ren_menu1_01_pop_mfile.php?KCODE="+kcode+"&SBIT="+sbit, "mpopup","width=1000px,height=930px,top="+top+",left="+left+",status=0,toolbar=0,menubar=0,location=false");
	popOpen.focus();
}



// �⺻�������� ������ư
function kwn_del(){
	if($("form[name='kwn_form'] #kwn_KCODE").val()==""){
		alert('��Ż������ �������ּ���.');
		return false;
	}else{
		if(confirm("������ �����ʹ� �������� �ʽ��ϴ�.\n���� �����Ͻðڽ��ϱ�?")){
			document.kwn_form.type.value='del';
			$("form[name='kwn_form']").submit();
		}
	}
}

// ��Ż�� ��ǰ���� �űԵ��
function itemList_new(){
	var kcode	= $("form[name='kwn_form'] #kwn_KCODE").val();
	if(kcode==""){
		alert('��Ż������ �������ּ���.');
		return false;
	}else{
		$.ajaxLoding('ren_menu1_01_pop_01_layer_kwnitem_reg.php',$('.layerBody'),'&KCODE='+kcode);
	}
}

//	�Աݵ��
function ipmst_new(){
	var kcode	= $("form[name='kwn_form'] #kwn_KCODE").val();
	if(kcode==""){
		alert('��Ż������ �������ּ���.');
		return false;
	}else{
		$.ajaxLoding('ren_menu1_01_pop_01_layer_ipmst_reg.php',$('.layerBody'),'&KCODE='+kcode);
	}
}


// �ڹٽ�ũ��Ʈ Trim(���ڿ��ڸ��� �Լ� ����)
String.prototype.trim = function(){
  return this.replace(/\s/g, "");
}

function cms_value_check(){
	
	if($("form[name='kwn_form'] #KSBIT").val()=='1'){

		var syjuno = $("form[name='kwn_form'] #SYJUNO").val();
		syjuno = syjuno.trim();
		
		if($("form[name='kwn_form'] #kwn_GCODE").val()==""){
			alert('���� ���� �������ּ���.');
			return false;
		}else if($("form[name='kwn_form'] #BANK").val()==""){
			alert('������� �Է����ּ���.');
			return false;
		}else if($("form[name='kwn_form'] #SKJNO").val()==""){
			alert('���¹�ȣ�� �Է����ּ���.');
			return false;
		}else if($("form[name='kwn_form'] #SYJUNO").val()==""){
			alert('�������/���No�� �Է����ּ���.');
			return false;
		}else if(syjuno.length != 6 && syjuno.length != 10){
			alert('��������� 6�ڸ� / ����ڹ�ȣ�� 10�ڸ��� �Է����ּ���.');
			return false;
		}else if($("form[name='kwn_form'] #KYJ").val()==""){
			alert('�����ָ� �Է����ּ���.');
			return false;
		}else if($("form[name='kwn_form'] #SCDATE").val()==""){
			alert('��ݽ��ۿ��� �Է����ּ���.');
			return false;
		}else if($("form[name='kwn_form'] #KDAY").val()==""){
			alert('������� �Է����ּ���.');
			return false;
		}
	}else if($("form[name='kwn_form'] #KSBIT").val()=='7'){

		var yy = $("form[name='kwn_form'] #CARDYY").val();
		var mm = $("form[name='kwn_form'] #CARDMM").val();

		yy = yy.trim();
		mm = mm.trim();

		if($("form[name='kwn_form'] #PDATE").val()==""){
			alert('������� �Է����ּ���.');
			return false;
		}else if($("form[name='kwn_form'] #CARDYY").val()==""){
			alert('ī����ȿ���� �Է����ּ���.');
			return false;
		}else if(yy.length != 2){
			alert('ī����ȿ�� 2�ڸ� Ȯ�����ּ���.');
			return false;
		}else if($("form[name='kwn_form'] #CARDMM").val()==""){
			alert('ī����ȿ���� �Է����ּ���.');
			return false;
		}else if(mm.length != 2){
			alert('ī����ȿ�� 2�ڸ� Ȯ�����ּ���.');
			return false;
		}else if(mm > '12'){
			alert('ī����ȿ���� 01 - 12�� ���̷� �Է����ּ���.');
			return false;
		}else if($("form[name='kwn_form'] #CARDNO").val()==""){
			alert('ī���ȣ�� �Է����ּ���.');
			return false;
		}else if($("form[name='kwn_form'] #SCDATE").val()==""){
			alert('��ݽ��ۿ��� �Է����ּ���.');
			return false;
		}else if($("form[name='kwn_form'] #KDAY").val()==""){
			alert('������� �Է����ּ���.');
			return false;
		}
	}
	return true;
}



// �⺻�������� ���� �� �Է�
function kwn_update(){
	// disable ó����������� ������ �ȵǱ⿡ ������ disable ����
	$("form[name='kwn_form'] #BANK").attr("disabled", false);
	if(date_check(document.kwn_form)==false){
		return false;
	}else if(form_required_check(document.kwn_form)==false){
		return false;
	}else if(cms_value_check()==false){
		return false;
	}else{
		if($("form[name='kwn_form'] #CAMT").val()<=0){
			alert("����ݾ��� 0���� Ŀ���մϴ�.");
		}else{
			if(document.kwn_form.KCODE.value){
				if(confirm("�����Ͻðڽ��ϱ�?")){
					document.kwn_form.type.value='up';
					$("form[name='kwn_form']").submit();
				}
			}else{
				if(confirm("����Ͻðڽ��ϱ�?")){
					document.kwn_form.type.value='in';
					$("form[name='kwn_form']").submit();
				}
			}
		}
	}
}



// ����� ��ȯ �Լ� 20160115 => 2016-01-15
function ymd10(ymd){
	if(ymd.trim()=="") return "";
	var ymdTemp	= ymd.replace(/-/gi,"");
	return ymdTemp.substr(0,4)+"-"+ymdTemp.substr(4,2)+"-"+ymdTemp.substr(6,2);
}

// ����� ��ȯ �Լ� 2016-01-15 => 20160115
function ymd8(ymd){
	return ymd.replace(/-/gi,"");
}

// ����� 6�ڸ� ��ȯ �Լ� 2016-01-15 => 16-01-15  Date �Լ�  ��  0  ~ 11 ����.. 
function ymd6(ymd){
	if(ymd.trim()=="") return "";
	var ymdTemp	= ymd.replace(/-/gi,"");
	return ymdTemp.substr(2,2)+"-"+ymdTemp.substr(4,2)+"-"+ymdTemp.substr(6,2);
}

// �Է� ����ϰ� �ֱ�� ����� ��ȯ 2016-01-15   
function GetCycleDay(ymd, cycl){
	var ymd1_1	= ymd8(ymd);
	var formattedDate = new Date(ymd1_1.substr(0,4),parseInt(ymd1_1.substr(4,2)-1)+parseInt(cycl),ymd1_1.substr(6,2));
	var d = formattedDate.getDate();
	var m =  formattedDate.getMonth()+1;
	var y = formattedDate.getFullYear();

	return y+"-"+leadingZeros(m,2)+"-"+leadingZeros(d,2);
};

// �� ����Ϸ� �ܿ��� ���
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
	//alert('�۾����Դϴ�.');
	<?if(!$_GET['GCODE']){?>
	alert('�⺻���������� �Է����ּ���.');
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

//as���� ���ε�
function asmst_reload(){
	ajaxLodingTarket('ren_menu1_01_pop_01_layer_asmst_list.php',$('.asmstBody'),'');
}

// ajax�� �Ǿ��ִ� ��Ż�κ��� ���ε� �Ѵ�
// �������� ������ ��� ������ ajax�� �����´�
function gcode_reload(gcode,kcode){
	ajaxLodingTarket('ren_menu1_01_pop_01_layer_kwnlist.php',$('.kwnlist'),'&KCODE='+kcode);

	asmst_reload();
}



// kcode�� �ʿ��� ������ ajax�� �����´�
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
		$("#kwn_info_text_in").text("��ȸ���");
	}else{
		$("#kwn_info_text_in").text("�űԸ��");
	}
}


function bdate_all_submit(){
	
	if(confirm("�ش� ��Ż������ ����ǰ�� �ݳ�ó���Ͻðڽ��ϱ�?")){
		$("form[name='bdate_all']").submit();
	}
}

// jquery ����

$(document).ready(function(){
	$(".all_bdate_btn").click(function(){
		if($("form[name='kwn_form'] select[name='LBIT']").val()=='4'){
			alert('�뿩������ �Ǹ��ΰ��� �ϰ��ݳ�ó���� ���� �ʽ��ϴ�.');
			return false;
		}else{
			$( "#datepicker_all_bdate_btn" ).datepicker({
				changeMonth: true,
				changeYear: true,
				DefaultDate:null,
				yearRange: "1900:2100",
				dateFormat: "yy-mm-dd",
				prevText: '���� ��',
				nextText: '���� ��',
				monthNames: ['1��','2��','3��','4��','5��','6��','7��','8��','9��','10��','11��','12��'],
				monthNamesShort: ['1��','2��','3��','4��','5��','6��','7��','8��','9��','10��','11��','12��'],
				dayNames: ['��','��','ȭ','��','��','��','��'],
				dayNamesShort: ['��','��','ȭ','��','��','��','��'],
				dayNamesMin: ['��','��','ȭ','��','��','��','��'],
				showMonthAfterYear: true,
				yearSuffix: '��',
				altFormat: "yy-mm-dd",
				onSelect : function(date){
					$("form[name='bdate_all'] input[name='bdate_bdate']").val(date);
					$("#datepicker_all_bdate_btn").removeClass("hasDatepicker").children().remove();
					bdate_all_submit();
				}
			});
		}
	});
	
	// �ϰ��ݳ�ó�� ��ư �� Ŭ���� �޷� ������� �ϱ�
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


	//alert('�۾����Դϴ�.');
	// ���̾� �˾��� ���� ���� �Լ� $.ajaxLoding(�������ּ�,������ ���� ���̵�)
	$.ajaxLoding	= function(url,target,etcData){
		//alert('�۾����Դϴ�.');
		<?if(!$_GET['GCODE']){?>
		alert('�⺻���������� �Է����ּ���.');
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



	// ���޸�, ��ǰ�޸�, ����, �ε�� �Ǹ޴� Ŭ����
	$(".yb_tab_menu > li").click(function(){
		var liData	= $(this).parent().find("li");
		var leng = liData.length;
		var idx	= liData.index($(this));
		
		liData.each(function(){
			// ���޸�, ��ǰ�޸�, ����, �ε�� �����޴� ����̹��� �⺻����
			if(liData.index($(this))==0){
				$(this).css("background","url('<?=$conf['homeDir']?>/img/tb_titl_off.png')");
			}else{
				$(this).css("background","url('<?=$conf['homeDir']?>/img/tap_off.png')");
			}


			if(idx==liData.index($(this))){
				// Ŭ���� �޴� ����̹��� �� css ����
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
