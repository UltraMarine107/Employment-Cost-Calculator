<?php

/*
Insert, update city data from uploaded Excel sheet 
*/

require(plugin_dir_path(__FILE__) . '../lib/PHPExcel-1.8/Classes/PHPExcel.php');
require_once(plugin_dir_path(__FILE__) . '../database/DatabaseConnect.php');


function city_detail_array($worksheet, $row){
	$val = array();
	$highest_col = PHPExcel_Cell::columnIndexFromString($worksheet->getHighestColumn());

	for ($col = 1; $col < $highest_col; ++ $col) {
		$key = $worksheet->getCellByColumnAndRow($col, 1);
		$adjusted_key = str_replace(" ", "_", str_replace("%", "p", $key));

	    $cell = $worksheet->getCellByColumnAndRow($col, $row);

	    if ($adjusted_key == '')
	    	continue;

	    $val[$adjusted_key] = $cell->getValue();
	}

	return $val;
}


function filename_check($filename){
	$split_underline = explode("_", $filename);
	if ($split_underline[0] != "ECC-Data-Update")
		return false;

	$split_dot = explode(".", $split_underline[1]);
	if ($split_dot[1] != "xlsx")
		return false;
	
	return $split_dot[0];
}


// Example filename: ECC-Data-Update_20180820.xlsx
function get_worksheet(){
	$dir = plugin_dir_path(__FILE__) . "../../../uploads/" . date('Y') . "/" . date('m');
	$file_list = scandir($dir);

	$date = 0;
	$dup = 0;
	$file = "";
	foreach ($file_list as $index => $value){
		$code = filename_check($value);
		if (!$code)
			continue;

		$split_dash = explode("-", $code);
		$temp_dup = 0;
		$temp_date = (int)$split_dash[0];
		if (count($split_dash) == 2)
			$temp_dup = (int)$split_dash[1];

		if ($temp_date > $date || ($temp_date == $date && $temp_dup > $dup)){
			$file = $value;
			$date = $temp_date;
			$dup = $temp_dup;
		}
	}

	$objPHPExcel = PHPExcel_IOFactory::load($dir . '/' . $file);

	foreach ($objPHPExcel->getWorksheetIterator() as $worksheet){
		return $worksheet;
	}
}


function insert($worksheet, $row, $city){
	global $wpdb;
	$city_detail = city_detail_array($worksheet, $row);

	$query = "SELECT id FROM wp_city ORDER BY id DESC LIMIT 1";
	$id = $wpdb->get_row($query)->id + 1;
	$city_detail["id"] = $id;
	$city_detail["city"] = $id;

	$result = $wpdb->insert("wp_city", array("id" => $id, "name" => $city, "update_t" => current_time('mysql', 1), "country" => 1));
	// Only support mainland China for now
	if ($result === false){
		echo "Error inserting " . $city . " into wp_city.";
		?><br><?php
	}

	$result = $wpdb->insert("wp_city_details", $city_detail);
	if ($result === false){
		echo "Error inserting " . $city . " into wp_city_details.";
		?><br><?php
	}	
}


function update($worksheet, $row, $id){
	global $wpdb;
	$city_detail = city_detail_array($worksheet, $row);

	$result = $wpdb->update("wp_city_details", $city_detail, array("id" => $id));

	if ($result === false){
		echo " Update error on row " . $row . " column " . $col . ". ";
		?><br><?php
		return 0;
	}

	return $result;
}


/*
Update or insert entries into database from excel sheet
*/
function write_db(){
	database_connect();

	$updated_cells = 0;
	$inserted_rows = 0;
	$worksheet = get_worksheet();

	require(plugin_dir_path(__FILE__) . "../database/AllCities.php");

	for ($row = 2; $row <= $worksheet->getHighestRow(); ++ $row) {
		$city_full = $worksheet->getCellByColumnAndRow(0, $row);

		if ($city_full == "")
			continue;

		$city_english = explode(" ", $city_full)[0];
		$index = array_search(strtolower($city_english), array_map('strtolower', $cities));

		if ($index === false){
			insert($worksheet, $row, $city_english);
			$inserted_rows++;
		}
		else{
			$updated_cells += update($worksheet, $row, $index + 1);
		}
	}

	echo $updated_cells . " cells updated. " . $inserted_rows . " rows inserted. Please check if this is reasonable."; 
	?><br><?php
}

