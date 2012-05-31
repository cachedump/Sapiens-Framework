<?php

error_reporting(E_ALL);

require "../packages/sapiens/core/Input.php";

$input = new SF_Input();

//var_dump($input);

//var_dump($_GET);
//var_dump($_POST);
//var_dump($_REQUEST);

//var_dump($input->post());
//var_dump($input->get());
//var_dump($input->cookie());
//var_dump($input->server());
//var_dump($input->ip_adress());
//var_dump($input->user_agent());
//var_dump($input->request_headers());
//var_dump($input->get_request_header('Host'));
//var_dump($input->is_ajax_request());
//var_dump($input->is_cli_request());

//echo $input->post('name');

//$input->set_cookie('test', 'test_value', time() + 10);



?>

<form method="post">
	Name: <input type="text" name="name">
	Age: <input type="text" name="age">
	Gender: <select name="gender">
		<option value="male">Male</option>
		<option value="female">Female</option>
	</select>
	<input type="submit" value="Go">
</form>

<!--

<h3>AJAX-Request - test</h3>

<div class="test"></div>



<script type="text/javascript" src="http://code.jquery.com/jquery.js"></script>
<script type="text/javascript">
	$(function() {
		var url = document.location;
		//alert(url);
		$.ajax({
		  url: url,
		  context: $('.test')
		}).done(function(data) { 
		  $(this).html(data);
		});
	});
</script>

-->