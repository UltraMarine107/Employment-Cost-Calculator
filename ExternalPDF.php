<?php

require_once(plugin_dir_path(__FILE__) . "../calculator/AddComma.php");

/**
manipulate the pdf that will be sent to guests
$company: a string of company name
$calc: a Calculator object
$city_name: a string of city name
return nothing
*/
function externalPDF($company, $calc, $city_name){
	$output_file = plugin_dir_path(__FILE__) . "pdf/FDIChina-External-Calculation.pdf";
	$src_file = plugin_dir_path(__FILE__) . "pdf/ExternalTemplate.pdf";

	require_once(plugin_dir_path(__FILE__) . "../lib/fpdf181/fpdf.php");
	require_once(plugin_dir_path(__FILE__) . "../lib/FPDI/src/autoload.php");
	require_once(plugin_dir_path(__FILE__) . "../lib/FPDI/src/Fpdi.php");

	$pdf = new setasign\Fpdi\Fpdi();
	$pdf->setSourceFile($src_file);

	// Page 1
	$template = $pdf->importPage(1);
	$size = $pdf->getTemplateSize($template);

	$pdf->AddPage('L', $size);
	$pdf->useTemplate($template);

	$pdf->SetFont("Helvetica", "", 28);
	$pdf->SetXY(0, 140);
	$pdf->SetTextColor(15, 90, 168);
	$pdf->Cell(0, 0, "  Employment Cost Estimation for " . $company, 0, 0, 'C');

	// Page 2
	$template = $pdf->importPage(2);
	$size = $pdf->getTemplateSize($template);

	$pdf->AddPage('L', $size);
	$pdf->useTemplate($template);

	$chinese = "Chinese";
	if (!$calc->chinese)
		$chinese = "foreign";
	$text = "    Calculation based on a " . $chinese . " worker based in " . $city_name . " with a gross salary of RMB " . add_comma($calc->salary, false);

	$pdf->SetFont("Helvetica", "", 16);
	$pdf->setXY(0, 30.5);
	$pdf->SetTextColor(68, 84, 106);
	$pdf->Cell(0, 0, $text, 0, 0, 'C');

	// TODO calc result
	$delta_Y = 5.44;
	$delta_Y_1 = 5.45;
	$X_0 = 89;
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont("Arial", "", 11);

	$items_ER = array($calc->salary, $calc->PeER(), $calc->MeER(), $calc->UeER(), $calc->WriER(), $calc->MaER(), $calc->DF_ER(), $calc->HF_ER());
	$mand_bene_ER = array_sum($items_ER) - $calc->salary;
	array_push($items_ER, $mand_bene_ER);
	array_push($items_ER, $calc->salary + $mand_bene_ER);

	for ($i = 0; $i < count($items_ER); $i++) { 
		$pdf->setXY($X_0, 55.7 + $i * $delta_Y);

		if ($i == count($items_ER) - 1)
			$pdf->SetFont("Arial", "B", 11);

		$pdf->Cell(25, 7, add_comma($items_ER[$i], false));
	}

	$pdf->SetFont("Arial", "", 11);
	for ($i = 0; $i < count($items_ER); $i++) {
		$pdf->setXY($X_0, 55.7 + ($i + count($items_ER)) * $delta_Y);

		if ($i == count($items_ER) - 1)
			$pdf->SetFont("Arial", "B", 11);

		$pdf->Cell(25, 7, add_comma($items_ER[$i] * 12, false));
	}

	$items_EE = array($calc->salary, $calc->PeEE(), $calc->MeEE(), $calc->UeEE(), $calc->HF_EE());
	$mand_bene_EE = array_sum($items_EE) - $calc->salary;
	array_push($items_EE, $mand_bene_EE);
	array_push($items_EE, round($calc->EE_Tax()));
	array_push($items_EE, round($calc->EE_Tax()));
	array_push($items_EE, $calc->salary - $mand_bene_EE - round($calc->EE_Tax()));

	$pdf->SetFont("Arial", "", 11);
	for ($i = 0; $i < count($items_EE); $i++) { 
		$pdf->setXY(231, 56 + $i * $delta_Y_1);

		if ($i == count($items_EE) - 1)
			$pdf->SetFont("Arial", "B", 11);

		$pdf->Cell(25, 7, add_comma($items_EE[$i], false));
	}

	$pdf->SetFont("Arial", "", 11);
	for ($i = 0; $i < count($items_EE); $i++) { 
		$pdf->setXY(231, 56 + (count($items_EE) + $i) * $delta_Y_1);
		
		if ($i == count($items_EE) - 1)
			$pdf->SetFont("Arial", "B", 11);

		$pdf->Cell(25, 7, add_comma($items_EE[$i] * 12, false));
	}

	// Page 3
	$template = $pdf->importPage(3);
	$size = $pdf->getTemplateSize($template);
	$pdf->AddPage('L', $size);
	$pdf->useTemplate($template);

	$pdf->Output($output_file, 'F');
}

