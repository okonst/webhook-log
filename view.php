<?php

/**
 *   View основной страницы Webhook-log
 */

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap&subset=cyrillic" rel="stylesheet">
	<style>
		.container{
			font-family: 'Roboto', sans-serif;
		}
		.container-fluid{
			background-color: #f2f2f2;
			margin-bottom: 20px;
		}
		.row.form {
			background-color: #f2f2f2;
			padding-top: 18px;
			padding-bottom: 18px;
			border-radius: 5px;
		}
		.row.param{
			border-bottom: 1px solid #f2f2f2;
		}
		.section {
			margin-top: 15px;
			margin-bottom: 15px;
		}
		.type{ 
			padding: 8px 10px;
			margin-bottom: 10px;
		 }
		 h1{
		 	margin-bottom: 20px;
		 }
		 h1 img{
		 	vertical-align: baseline;
		 	width: 150px;
		 	float: right;
		 }
		 .table th{
		 	background-color: #f2f2f2;
		 }
		 .table td.payload {
		    word-break: break-word;
		}
	</style>
</head>
<body>
	<div class="container-fluid">
	  <div class="container">
	    <h1>Webhook Logger</h1>
	  </div>
	</div>
	<div class="container">
		
		<!-- ОБРАБОТКА ПЛАТЕЖА -->
		<? if(isset($this->payment)){ ?>
				
			<div class="section">
				<a href="/" class="btn btn-default btn">Назад</a>
			</div>
			<hr/>
		<? } ?>

		<!-- ТАБЛИЦА С ПЛАТЕЖАМИ -->
		<h3>Webhooks</h3>
		<div class="table-responsive">
			<? echo $this->table; ?>
		</div>


	</div>
</body>
</html>
