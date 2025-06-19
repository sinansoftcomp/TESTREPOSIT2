<? php
/* 참조 사이트 .
https://link2me.tistory.com/1146  //실시간으로 가져오는 
https://www.phpflow.com/php/dynamic-tree-with-jstree-php-and-mysql/
https://e-7-e.tistory.com/69 유용
https://developing-stock-child.tistory.com/38
*/
 

 ?>  



<!--   참조함  원소스  https://link2me.tistory.com/1146 -->


<!DOCTYPE html>
 
<head>
 
 
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
<script type="text/javascript"  src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.2.min.js"></script>
<link rel="stylesheet" href="dist/style.min.css" />
<script src="dist/jstree.min.js"></script> 


 
<title>
	1111111111
</title>
<body>
<div>
      		
<div id="tree-container"></div> 
</div>
</body>
</html>




<script type="text/javascript">


 
function get_jstree() {
 
	$("#tree-container").jstree({  
			'core': {
					'data' : {
								"url"	 : "/bin/sub/test/group_do_jstree.php",
								"dataType" : "json"	
					}
				} 
		}).on("loaded.jstree",function(e,data){
				$('#tree-container').jstree('open_all');
		});

/*본부만 펼칠 때 
	$("#tree-container").jstree({  
			'core': {
					'data' : {
								"url"	 : "/bin/sub/test/group_do_jstree.php",
								"dataType" : "json"	
					}
				} 
		}).on("loaded.jstree",function(e,data){
				$('#tree-container').jstree('open_node','N1000001');

		});

*/ 



}
 

 


$(document).ready(function(){
get_jstree();
});

// Node 선택했을 때.
$('#tree-container').on("select_node.jstree", function (e, data) {
			var id = data.instance.get_node(data.selected).id;
 

			alert(id);
			/*
			var type = data.instance.get_node(data.selected).type;
			var path = data.instance.get_node(data.selected).path;
			alert(data.selected);
			alert(id);
			alert(type);
			alert(path);*/ 
});
</script>