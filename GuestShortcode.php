<!--
Front-end page for guests' employment cost calculator 
(There is another calculator for internal users)
Implemented through WordPress shortcode
-->

<html lang="en">
<head>
  <title>China Employment Cost Calculator | FDIChina</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

  <style>
		.slidecontainer {
			width: 100%;
		}
		
		hr.style1{
			border-top: 1px solid #e6f5ff;
		}

		th {
			font-family: Open Sans;
			text-align: center;
			font-size: 16xpx;
			color: white;
			background-color: #0559a8;
			height: 40px;
		}

		td {
			font-family: Open Sans;
			text-align: center;
			font-size: 20px;
			height: 40px;
		}

		.result_border {
			color: #a72d2e;
			height: 4px;
			background-color: #a72d2e;
		}
		
		.btn-primary, .btn-primary:hover, .btn-primary:active, .btn-primary:visited
		{
    		background-color: #0559a8 !important;
		}

		#calc-header {
			background-color: #0559a8 !important;
		}

		#calc-panel {
			border-color: #0559a8 !important;
		}
	</style>
	<script>
		window.onload = function () {

		//Better to construct options first and then pass it as a parameter
		var options = {
			animationEnabled: true,
			theme: "light2",
			title:{
				text: "Results"
			},
			axisY2:{
				prefix: "¥",
				lineThickness: 0				
			},
			toolTip: {
				shared: true
			},
			legend:{
				verticalAlign: "top",
				horizontalAlign: "center"
			},
			data: [
			{     
				type: "stackedBar",
				showInLegend: true,
				name: "Net Salary",
				axisYType: "secondary",
				color: "#2963d6",
				dataPoints: [
					{ y: 70855, label: "China" },
				]
			},
			{
				type: "stackedBar",
				showInLegend: true,
				name: "IIT",
				axisYType: "secondary",
				color: "#ed1818",
				dataPoints: [
					{ y: 395, label: "China" },
				]
			},
			{
				type: "stackedBar",
				showInLegend: true,
				name: "Employee Taxation",
				axisYType: "secondary",
				color: "#06c92a",
				dataPoints: [
					{ y: 2500, label: "China" },
					]
			},
			{
				type: "stackedBar",
				showInLegend: true,
				name: "Employer's Taxation",
				axisYType: "secondary",
				color:"#208031",
				indexLabel: "Total: ¥ #total",
				dataPoints: [
					{ y: 3816, label: "China" },
				]
			}
			]
		};

		$("#chartContainer").CanvasJSChart(options);

		}

		function checkIt(evt){
			evt = (evt) ? evt : window.event
		   	var charCode = (evt.which) ? evt.which : evt.keyCode
		   if (charCode > 31 && (charCode < 48 || charCode > 57)) {
		    status = "This field accepts numbers only."
		    return false
		   }
		   status = ""
		   return true
		}
	</script>
</head>

<?php

require_once(plugin_dir_path(__FILE__) . "../database/CityData.php");
require_once(plugin_dir_path( __FILE__ ) . "../calculator/Calculator.php");
require_once(plugin_dir_path(__FILE__) . "SelectCity.php");
require_once(plugin_dir_path(__FILE__) . "../calculator/AddComma.php");


function isLocalhost($whitelist = ['127.0.0.1', '::1']) {
    return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
}


function data_input(){

?>

<div class="container">
	<h2></h2>
	<div class="panel panel-primary" id="calc-panel">
		<div class="panel-heading" id="calc-header"><span class="glyphicon glyphicon-info-sign"></span>  Employment Cost Calculator</div>
		<div class="panel-body">		  
			<form method="post"> 
				<div class="form-group ">
					<label for="g_salary">Gross Monthly Salary:</label>

					<div class="row">
						<div class="col-sm-8" style="width: 100%; clear:left;">
							<div class="input-group mb-3">
								<div class="input-group-prepend">
									<span class="input-group-text">￥</span>

									<input type="bi_gs" class="form-control" placeholder="Monthly Salary" id="bi_gs" name="salary" value="<?php if(isset($_POST['salary'])){echo $_POST['salary'];}?>" oninput="this.form.myRange" onKeyPress="return checkIt(event)" required>
									</div>
									</div>
									</div>
									<div class="col-sm-4">

									<div class="slidecontainer" style="margin-top: 12px">
										<input type="range" min="0" max="100000" value="0" step="500" class="slider" id="myRange" oninput="this.form.bi_gs.value=this.value">
									</div>

								</div>
							</div>
						</div>

							<div class="form-group">
								<label for="city">City:</label>
								<?php city_dropdown(); ?>
							</div>

							<div class="form-group">
								<div class="form-group">
									<label for="bi_le">Is your employee mainland Chinese?</label>
									<select name="chinese" class="form-control" id="sel1">
										<option value="1">Yes</option>
										<option value="0"
<?php
										if (isset($_POST['chinese'])){
											if ($_POST['chinese'] == 0){
												?> selected="true" <?php
											}
										}
?>
										>No</option>
									</select>
								</div>
							</div>
						<center><input type="submit" class="btn btn-primary" name="submit" value="Calculate Cost" style="font-weight: bold;"></center>
						<!-- color change, bold for button text -->
					</div>
					</form>
				</div>

<?php
	if (isset($_POST['submit'])){
		$salary = $_POST['salary'];
		$city_id = explode(" ", $_POST['city'])[0];

		$city = city_data($city_id);
		$calc = new Calculator($salary, $city, $_POST['chinese']);
?>
<div>
	<hr class="result_border">
	<div style="margin-top: -5px; margin-bottom: 10px">
		<center><font color="#a72d2e" style="font-size: 28px; font-family: Open Sans;">Employment Cost Result</font></center>
	</div>
	<table style="width: 100%">
			<tr>
				<th>Gross Salary</th>
				<th>Employee Social Insurance</th>
				<th>Employer Social Insurance</th>
				<th>Individual Income Tax</th>
				<th>Employment Cost</th>
				<th>Net Salary</th>
			</tr>
			<tr>
				<td><?php echo add_comma($salary);?></td>
				<td><?php echo add_comma(round($calc->guest_soc_insur_EE()));?></td>
				<td><?php echo add_comma(round($calc->guest_soc_insur_ER()));?></td>
				<td><?php echo add_comma(round($calc->EE_tax()));?></td>
				<td><?php echo add_comma(round($calc->guest_soc_insur_ER() + $salary));?></td>
				<td><?php echo add_comma(round($salary - $calc->guest_soc_insur_EE() - $calc->EE_tax()));?></td>
			</tr>

	</table>
	<hr class="result_border" style="margin-top: 3px">
</div>
<?php
		return array('salary' => $salary, 'city' => $_POST['city'], 'chinese' => $_POST['chinese']);
	}
}


function user_info_input($calc_input){
?>
<div class="panel panel-danger">
	<div class="panel-heading"><span class="glyphicon glyphicon-envelope"></span> Fill your data and get more results in your email!</div>
	<div class="panel-body">		  
		<form method="post"> 
		<div class="row">
		<div class="col-sm-8">
		<input name="salary" value="<?php echo $calc_input["salary"]; ?>" hidden>
		<input name="city" value="<?php echo $calc_input["city"]; ?>" hidden>
		<input name="chinese" value="<?php echo $calc_input['chinese']; ?>" hidden>
			<div class="form-group">
				<label for="name">Name:</label>
				<input type="name" class="form-control" id="name" placeholder="ex. Peter Parker" name="client_name">
			</div>	
			
			<div class="form-group">
				<label for="eemail">Email:</label>
				<input type="eemail" class="form-control" id="email" placeholder="example@email.com" name="client_email">
			</div>
			
			<div class="form-group">
				<label for="company">Company:</label>
				<input type="company" class="form-control" id="company" placeholder="ex.: FDIChina" name="company">
			</div>
			<center><input type="submit" class="btn btn-danger" name="enter_info" value="Get Detailed Results" style="font-weight: bold;"></center>
		</div>
		<div class="col-sm-4">
		<div class="form-group">
			<img src="<?php bloginfo('template_url'); ?>/assets/images/expdf.png" class="img-fluid" alt="Responsive image">
		</div>
		</div>
		</form>
		</div>
	</div>

<div style="margin: 10px">
<?php
	if (isset($_POST["enter_info"])){
		ini_set('display_errors', '1');

		// Write PDF
		$city = explode(" ", $_POST["city"]);

		if (count($city) != 2){
			echo "Error: Please complete all the input fields";
			return;
		}

		$city_id = $city[0];
		$city_name = $city[1];

		// Input check
		$email = $_POST["client_email"];
		$name = $_POST["client_name"];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  			echo "Please enter a valid email address"; 
  			return;
		}

		if ($_POST["company"] == ""){
			echo "Please enter a company name";
			return;
		}

		// Write to database
		global $wpdb;
		$result = $wpdb->insert("wp_client_info", array("client_name" => $name, "email" => $email, "company_name" => $_POST["company"], "date_cal" => current_time('mysql', 1)));

		// Prepare PDF
	    require_once(plugin_dir_path(__FILE__) . "../pdf/ExternalPDF.php");
		$calc = new Calculator($_POST['salary'], city_data($city_id), $_POST['chinese']);
		externalPDF($_POST["company"], $calc, $city_name);

		// Mail to user
		require_once(plugin_dir_path(__FILE__) . "../lib/PHPMailer/src/PHPMailer.php");
		require_once(plugin_dir_path(__FILE__) . "../lib/PHPMailer/src/Exception.php");
		require_once(plugin_dir_path(__FILE__) . "../lib/PHPMailer/src/SMTP.php");

		$mail = new PHPMailer\PHPMailer\PHPMailer();
		$mail->IsSMTP(); // enable SMTP

		$sender = "david.perrenoud@hrone.com";
		$password = "p25hqN;e";

		require_once(plugin_dir_path(__FILE__) . "Signature.php");
		$message = nl2br('Hello ' . $name . ',

			Thank you for reaching out.

			As promised, find your ' . $city_name . ' employment cost estimation attached.

			We hope that our calculator was useful to you.

			FDIChina is a <a href="http://FDIChina.com/services-solutions/china-payroll-service/">Payroll</a> & <a href="http://FDIChina.com/services-solutions/peo-employee-leasing/">Employment Solutions</a> provider that helps foreign companies overcome HR complexities in Greater China.

			Feel free to contact us for any further queries about your business in China.

			Kind regards,
			' . $signature);

	    $mail->SMTPDebug = 0; // debugging: 1 = errors and messages, 2 = messages only
	    $mail->SMTPAuth = true; // authentication enabled
	    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
	    $mail->Host = "smtp.exmail.qq.com";
	    $mail->Port = 465; // 465 or 587
	    $mail->IsHTML(true);
	    $mail->Username = $sender;
	    $mail->Password = $password; 
	    $mail->SetFrom($sender);
	    $mail->Subject = "FDIChina - Employment Cost Calculation"; 
	    $mail->Body = $message;
	    $mail->AddAddress($email);

		$mail->AddAttachment(plugin_dir_path(__FILE__) . "../pdf/pdf/FDIChina-External-Calculation.pdf", 'FDIChina-Employment-Cost-Calculation.pdf' );

		if (isLocalhost()){
			echo "Working on localhost, email sending deactivated";
			return;
		}

		if(!$mail->Send()) {
        	echo "Mailer Error: " . $mail->ErrorInfo;
     	} else {
        	echo "Results have been sent";
     	}
	}
}


user_info_input(data_input());

?>

</div>
</div>
</div>
</body>
</html>

<div hidden> <!-- Hide pagination -->
