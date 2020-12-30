<?php
function IAP_Format_Report_Heading($ReportName) {

	if (isset($_REQUEST['UserData']['DisplayName'])) {
		$iapHeading = $_REQUEST['UserData']['DisplayName'];
		if (substr_compare($iapHeading, "s", -1, 1) == 0) {
			$iapHeading = $iapHeading."' ".$ReportName;
		} else {
			$iapHeading = $iapHeading."'s ".$ReportName;
		}
	} else {
		$iapHeading = $iapReport;
	}	
	return($iapHeading);
}

function IAP_Generate_PDF($IAPTitle, $IAPSubject, $IAPHeader, $IAPReport, $IAPOrient = "P", $IAPDest = "I") {

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Generate_PDF with title of ".$IAPTitle." subject of ".$IAPSubject.", etc.<br />Report is<pre>";
		var_dump($IAPReport);
		echo "</pre>";
	}

// Include the main TCPDF library (search for installation path).
	require_once($_REQUEST['IAPPath']."/tcPDF/tcpdf_include.php");

//	require_once('tcPDF/config/lang/eng.php');
//	require_once('tcPDF/tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
//class MYPDF extends TCPDF {
	// Page footer
//	public function Footer() {
	// Position at 15 mm from bottom
//		$this->SetY(-50);
	// Set font
//		$this->SetFont('helvetica', 'I', 8);
	// Page number
//		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, T', 'M');
//	}
//}
// then use MYPDF in new constructor


// create new PDF document
//	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf = new TCPDF($IAPOrient, "in", "LETTER", true, 'UTF-8', false);

// set document information
//	$pdf->SetCreator(PDF_CREATOR);
//	$pdf->SetAuthor('Nicola Asuni');
//	$pdf->SetTitle('TCPDF Example 001');
//	$pdf->SetSubject('TCPDF Tutorial');
//	$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Litehaus Consulting');
	$pdf->SetTitle($IAPTitle);
	$pdf->SetSubject($IAPSubject);
	$pdf->SetKeywords('TCPDF, LitehausConsulting');

// set default header data
//	$pdf->SetHeaderData("", "", $IAPTitle, "by Litehaus Consulting");
//	$pdf->SetHeaderData("", "", "", "");

// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
//	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetMargins(.5, .5, .5);
//	$pdf->SetHeaderMargin(.5);
//	$pdf->SetFooterMargin(0);

//set auto page breaks
//	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	$pdf->SetAutoPageBreak(TRUE, .5);

//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
	$pdf->setLanguageArray($l);

// add a page
	$pdf->AddPage();

// output data
	$IAPHeader = (array) $IAPHeader;
	if ($IAPOrient == "L") {
		$IAPLines = 42;			// Landscape Mod
	} else {
		$IAPLines = 56;			// Portrait Mod
	}
	$IAPCntr = 999;
	for($i = 0; $i < count($IAPReport); $i++) {
		if ($IAPCntr > $IAPLines
		or $IAPReport[$i] == "<(NP)>") {
//			$pdf->endPage();
			$IAPCntr = 0;
			$pdf->SetFont('courier', 'B', 10);
			foreach ($IAPHeader as $IAPH) {
				$pdf->Write(0, $IAPH, '', 0, 'L', true, 0, false, false, 0);
				$IAPCntr = $IAPCntr + 1;
			}
			$pdf->SetFont('courier', '', 10);
		}
		if ($IAPReport[$i] != "<(NP)>") {
			$pdf->Write(0, $IAPReport[$i], '', 0, 'L', true, 0, false, false, 0);
			$IAPCntr = $IAPCntr + 1;
		}
	}

// Close and output PDF document
	$IAPPDF = $pdf->Output('RenameMe.pdf', $IAPDest);
	if ($IAPDest == "S") {
		return($IAPPDF);
	} else {
		return(TRUE);
	}
}


error_reporting(E_ALL & ~E_NOTICE);

$IAPPath = str_replace("\\", "/", dirname(__FILE__));
$IAPPath = explode("/", $IAPPath);
array_pop($IAPPath);
$IAP = implode("/", $IAPPath);
require_once($IAP."/IAPSetVars.php");
include_once($IAP."/wp-config.php");
include_once($IAP."/wp-load.php");
include_once($IAP."/wp-includes/wp-db.php");

require_once(ABSPATH. "IAPServices.php");
IAP_Program_Init();


?>