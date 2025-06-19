<!DOCTYPE html>
<html lang="ko">
<head>
<!-- jsTree theme -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
</head>
<body>
<button>demo button</button>
<div id="jstree"></div>
<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.1/jquery.min.js"></script>
<!-- jsTree -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
<script>
$(function () { 
    
    $('#jstree').jstree({ 
		'core' : {
			'data' : [
				


				

{"id":"H00001","parent":"#","text":"\uac15\uc6d0\ubcf8\ubd80","icon":"glyphicon glyphicon-home"},{"id":"H00003","parent":"#","text":"\uacbd\uae30\ubcf8\ubd80","icon":"glyphicon glyphicon-home"},{"id":"H00009","parent":"#","text":"\uad11\uc8fc\ubcf8\ubd80","icon":"glyphicon glyphicon-home"},{"id":"H00010","parent":"#","text":"\ub300\uad6c\ubcf8\ubd80","icon":"glyphicon glyphicon-home"},{"id":"H00011","parent":"#","text":"\ub300\uc804\ubcf8\ubd80","icon":"glyphicon glyphicon-home"},{"id":"H00012","parent":"#","text":"\ubd80\uc0b0\ubcf8\ubd80","icon":"glyphicon glyphicon-home"},{"id":"H00013","parent":"#","text":"\uc11c\uc6b8\ubcf8\ubd80","icon":"glyphicon glyphicon-home"},{"id":"H00014","parent":"#","text":"\uc138\uc885\ubcf8\ubd80","icon":"glyphicon glyphicon-home"},{"id":"H00015","parent":"#","text":"\uc6b8\uc0b0\ubcf8\ubd80","icon":"glyphicon glyphicon-home"},{"id":"H00016","parent":"#","text":"\uc778\ucc9c\ubcf8\ubd80","icon":"glyphicon glyphicon-home"},{"id":"H00017","parent":"#","text":"\uc804\ub77c\ubcf8\ubd80","icon":"glyphicon glyphicon-home"},{"id":"H00018","parent":"#","text":"\uc81c\uc8fc\ubcf8\ubd80","icon":"glyphicon glyphicon-home"},{"id":"H00019","parent":"#","text":"\ucda9\uccad\ubcf8\ubd80","icon":"glyphicon glyphicon-home"},{"id":"S00002","parent":"H00019","text":"\uac15\ub0a8\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00003","parent":"H00003","text":"\uac15\ub3d9\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00004","parent":"H00003","text":"\uac15\ub989\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00005","parent":"H00005","text":"\uac15\ubd81\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00006","parent":"H00013","text":"\uac15\uc11c\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00007","parent":"#","text":"\uac15\uc9c4\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00008","parent":"H00017","text":"\uac15\ud654\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00009","parent":"H00009","text":"\uac70\uc81c\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00010","parent":"H00010","text":"\uac70\ucc3d\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00012","parent":"H00012","text":"\uacbd\uc8fc\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00013","parent":"H00013","text":"\uacc4\ub8e1\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00014","parent":"H00014","text":"\uacc4\uc591\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00015","parent":"H00015","text":"\uace0\ub839\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00016","parent":"H00016","text":"\uace0\uc131\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00017","parent":"H00017","text":"\uace0\uc591\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00018","parent":"H00018","text":"\uace0\ucc3d\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00019","parent":"H00019","text":"\uace0\ud765\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"},{"id":"S00073","parent":"H00013","text":"\ubcf4\uc740\uc9c0\uc0ac","icon":"glyphicon glyphicon-home"}




				/*		

				{ "id" : "12_name", "parent" : "#", "text" : "12_display" },
				{ "id" : "ajson2", "parent" : "#", "text" : "222222" },
					

				{ "id" : "ajson1", "parent" : "#", "text" : "11111" },

				{ "id" : "ajson2", "parent" : "#", "text" : "222222" },
				{ "id" : "ajson3", "parent" : "ajson2", "text" : "Child 1" },
				{ "id" : "ajson4", "parent" : "ajson2", "text" : "Child 2" },

				{ "id" : "ajson5", "parent" : "#", "text" : "333333" },
				{ "id" : "ajson6", "parent" : "ajson5", "text" : "Child 3" },
				{ "id" : "ajson7", "parent" : "ajson5", "text" : "Child 4" },
				{ "id" : "ajson8", "parent" : "ajson5", "text" : "Child 4" },
				{ "id" : "ajson9", "parent" : "ajson5", "text" : "Child 3" },
				{ "id" : "ajson10", "parent" : "ajson5", "text" : "Child 4" },
				{ "id" : "ajson11", "parent" : "ajson5", "text" : "Child 4" },

				{ "id" : "ajson12", "parent" : "ajson11", "text" : "Child 5" },
				{ "id" : "ajson13", "parent" : "ajson12", "text" : "±è¼ø°ü" },
					*/ 
			


			]
		}
   	});
});

$(function () { 
	//$('#jstree').jstree(); 
		

	$('#jstree').on("changed.jstree", function (e, data) {
         //alert(data.selected);
		//var node_id = $("#treeId",).jstree("get_selected").attr("id");
		//alert(node_id);
    });
	
	// Node ¼±ÅÃÇßÀ» ¶¯
	$('#jstree').on("select_node.jstree", function (e, data) {
			var id = data.instance.get_node(data.selected).id;
			/*
			var type = data.instance.get_node(data.selected).type;
			var path = data.instance.get_node(data.selected).path;
			alert(data.selected);
			alert(id);
			alert(type);
			alert(path);*/ 
	});




	$('button').on('click', function () {
      $('#jstree').jstree(true).select_node('child_node_1');
      $('#jstree').jstree('select_node', 'child_node_1');
      $.jstree.reference('#jstree').select_node('child_node_1');
    });
});
</script>


</script>
</body>
</html>
