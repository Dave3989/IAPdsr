<?php

//Database Routines

// --- MySQL Routines

function IAPProcessMySQL($IAPSqlAction, $IAPSql) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>> In IAPProcessMySQL with action of ".$IAPSqlAction." and sql of ".$IAPSql."<br />";
	}

	global $IAPDataConn;

	if ( !defined('ABSPATH') )
		define('ABSPATH', dirname(__FILE__) . '/');

	switch($IAPSqlAction) {
    	case "select":
      	case "insert":
    	case "update":
      	case "delete":
       		break;
      	default:

			if ($_REQUEST['debugme'] == "Y") {
				echo "...--- Error: wrong action<br />";
			}

			$IAPErr = array('retcode' => -200,
							'retmsg' => "Invalid sql action set in parm 1",
							'sql' => $IAPSql,
							'mserrno' => NULL,
							'mserrmsg' => NULL,
							'module' => basename(__FILE__),
							'line' => __LINE__);
			IAP_MySQL_Error($IAPErr);
			return($IAPErr);
   	}
   	if (!isset($IAPSql)) { 

		if ($_REQUEST['debugme'] == "Y") {
			echo "...--- Error: query missing<br />";
		}

		$IAPErr = array('retcode' => -201,
						'retmsg' => "No sql query set in parm 2",
						'sql' => $IAPSql,
						'mserrno' => NULL,
						'mserrmsg' => NULL,
						'module' => basename(__FILE__),
						'line' => __LINE__);
		IAP_MySQL_Error($IAPErr);
		return($IAPErr);
	}

	require_once($_REQUEST['iap_path']."IAPSetVars.php");

	if ($_REQUEST['debugme'] == "Y") {
		echo "IAPDataConn being used for query = ".$IAPDataConn->host_info."<br />";
	}

	$IAPRes = $IAPDataConn->query($IAPSql);

	if ($_REQUEST['debugme'] == "Y") {
		echo "...--- SQLSTATE ".$IAPDataConn->sqlstate."<br />";
	}

	if ($IAPDataConn->errno != 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...--- Error: query in error<br />";
		}

		$IAPErr = array('retcode' => -301,
						'retmsg' => "SQL Error",
						'sql' => $IAPSql,
						'mserrno' => $IAPDataConn->errno,
						'mserrmsg' => $IAPDataConn->error,
						'module' => basename(__FILE__),
						'line' => __LINE__);
		IAP_MySQL_Error($IAPErr);
		return($IAPErr);
	}

  	$d = array();
   	switch($IAPSqlAction) {
      	case "select":
			$r = $IAPRes->num_rows;

			if ($_REQUEST['debugme'] == "Y") {
				echo "...--- Returned ".strval($r)." rows<br />";
			}

			if ($r > 0) {

				$d = mysqli_fetch_all($IAPRes, MYSQLI_ASSOC);
/*
				$IAPRes->data_seek(0);
				while ($row = $IAPRes->fetch_assoc()) {
					$d[] = $row;
				}
*/
				$IAPRes->free();

				if ($_REQUEST['debugme'] == "Y") {
					echo "...--- Returned the data<br />";
				}
			}

         	break;
		case "insert":
			$r = $IAPDataConn->affected_rows;
			$i = $IAPDataConn->insert_id;

			if ($_REQUEST['debugme'] == "Y") {
				echo "...--- Affected ".strval($r)." rows and new record id is ".strval($i)."<br />";
			}

			$d = $i;

			$IAPDataConn->commit();
			break;
      	default:
			$r = $IAPDataConn->affected_rows;

			if ($_REQUEST['debugme'] == "Y") {
				echo "...--- Affected ".strval($r)." rows<br />";
			}

			$IAPDataConn->commit();
   	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...--- Returning good response<br />";
	}

	return(array('retcode' => 0,
			 	 'retmsg' => "Good return",
				 'numrows' => $r,
				 'mserrno' => NULL,
				 'mserrmsg' => NULL,
				 'data' => $d));
}

function IAP_Escape_Field($IAPFieldIn) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	global $IAPDataConn;

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>> In IAP_Escape_field with IAPFieldIn of ".$IAPFieldIn."<br />";
	}

	$IAPFieldOut = $IAPDataConn->real_escape_string($IAPFieldIn);

	if ($_REQUEST['debugme'] == "Y") {
		echo "... Returning IAPFieldOut of ".$IAPFieldOut."<br /";
	}

	return($IAPFieldOut);
}


// Miscellaneous database routines

function IAP_Set_Initial($IAPPass) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Set_initial.<br />";
	}

	$IAPSql = "SELECT COLUMN_NAME, COLUMN_DEFAULT, NUMERIC_PRECISION FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA= '".DB_NAME."' AND TABLE_NAME = '".$IAPPass['real_table']."';";

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---sql = ".$IAPSql."<br />";
	}

	$IAPRet = (array) IAPProcessMySQL("select", $IAPSql);
	if ($IAPRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		$_REQUEST['IAPmodule'] = basename(__FILE__);
		$_REQUEST['IAPline'] = __LINE__;
		IAP_WPDB_Errors("INFORMATION_SCHEMA.COLUMNS", "Select", $wpdb);
		return(array('retcode' => -102, 'retmsg' => "Cannot retrieve schema columns."));
	} elseif ($IAPRet['numrows'] == 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---cols returned null. return -102.<br />";
		}

		return(array('retcode' => -102, 'retmsg' => "Cannot retrieve schema columns."));
	}

	$IAPCols = $IAPRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---read<pre>";
		var_dump($IAPCols);
		echo "</pre>";
	}

	foreach ($IAPCols as $IAPCol) {
		$IAPCol = (array) $IAPCol;
		if ($IAPCol['COLUMN_NAME'] != "tblgrp"
		and $IAPCol['COLUMN_NAME'] != "tbljoin") {
			if (!is_null($IAPCol['COLUMN_DEFAULT'])
			and $IAPCol['COLUMN_DEFAULT'] != "NULL") {
				if (is_null($IAPCol['NUMERIC_PRECISION'])) {
					$IAPInitValue = substr($IAPCol['COLUMN_DEFAULT'], 1, strlen($IAPCol['COLUMN_DEFAULT']) - 2);
				} else {
					$IAPInitValue = $IAPCol['COLUMN_DEFAULT'];
				}
			} elseif (is_null($IAPCol['NUMERIC_PRECISION'])) {
				$IAPInitValue = "";
			} else {
				$IAPInitValue = "0";
			}
			$IAPColName = $IAPCol['COLUMN_NAME'];
			$IAPColArray[$IAPColName] = $IAPInitValue;
		}
	}

	$IAPColArray['status'] = "NEW";

	$IAPRet = array($IAPColArray);

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---returning<pre>";
		var_dump($IAPRet);
		echo "</pre>";
	}

	return($IAPRet);
}

function IAP_Build_New_Row($IAPPass) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Build_New_Row.<br />";
	}

	if (!(isset($IAPPass['real_table']))) {
		require_once("IAPDBTables.php");
		$IAPTable = IAP_Get_Table($IAPPass);
		if ($IAPTable < 0) {			
			return(array('retcode' => -101, 'retmsg' => "Table name not found."));
		}
		$IAPPass['real_table'] = $IAPTable;
	}

	$IAPRet = IAP_Set_Initial($IAPPass);
	if ($IAPRet < 0) {
		return($IAPRet);
	}
	$IAPRet = (array) $IAPRet;
	$IAPTemp = (array) $IAPRet[0];
	$IAPTemp['table'] = $IAPPass['real_table'];
	$IAPRet[0] = $IAPTemp;
	return($IAPRet);
}

// Database Routines
// --- General Database Routines

function IAP_Get_All_Rows($IAPTable, $IAPNew = "Y", $IAPOrder = NULL) {
// returns all records from a table

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_All_Rows with table = ".$IAPTable." and new = ".$IAPNew.".<br />";
	}

	$IAPPass['table'] = $IAPTable;
	if (! is_null($IAPOrder)) {
		$IAPPass['order'] = $IAPOrder;
	}
	$IAPRows = (array) IAP_Get_Rows($IAPPass);
	if ($IAPRows < 0) {
		return($IAPRows);
	}
	if ($IAPNew == "N") {
		$IAPR = (array) $IAPRows[0];
		if ($IAPR['status'] == "NEW") {
			$IAPRows = NULL;
		}
	}
	return($IAPRows);
}

function IAP_Get_Rows($IAPPass) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Rows.<br />";
	}

	$IAPPass = (array) $IAPPass;

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---pass = <pre>";
		var_dump($IAPPass);
		echo "</pre>";
	}

	$IAPSql = "SELECT ";

	if (isset($IAPPass['distinct'])) {
		$IAPSql = $IAPSql." ".$IAPPass['distinct'];
	}

	if (isset($IAPPass['cols'])) {
		$IAPSql = $IAPSql." ".$IAPPass['cols'];
	} else {
		$IAPSql = $IAPSql." *";
	}

	if (isset($IAPPass['real_table'])) {
		$IAPTable = $IAPPass['real_table'];
	} else {

		if ($_REQUEST['debugme'] == "Y") {
			echo "Requiring DBTables<br>";
		}

		require_once("IAPDBTables.php");

		if ($_REQUEST['debugme'] == "Y") {
			echo "Going to DBTables<br>";
		}

		$IAPTable = IAP_Get_Table($IAPPass);
		if (empty($IAPTable)) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "Table is empty<br>";
			}

			return(array('retcode' => -101, 'retmsg' => "Table name not found."));
		}
		$IAPPass['real_table'] = $IAPTable;
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "Table is ".$IAPTable."<br>";
	}

	$IAPSql = $IAPSql." FROM ".$IAPTable;

	if (isset($IAPPass['index'])) {
		$IAPSql = $IAPSql." USE INDEX (".$IAPPass['index'].")";
	}

	if (isset($IAPPass['join'])) {
		if (is_array($IAPPass['join'])) {
			$IAPSql = $IAPSql." ".IAP_Unload_Join_Array($IAPPass['join']);
		} else {
			$IAPSql = $IAPSql." ".$IAPPass['join'];	
		}
	}

	if (isset($IAPPass['where'])) {
		$IAPSql = $IAPSql." WHERE ".$IAPPass['where'];
	}

	if (isset($IAPPass['group'])) {
		$IAPSql = $IAPSql." GROUP BY ".$IAPPass['group'];
	}

	if (isset($IAPPass['having'])) {
		$IAPSql = $IAPSql." HAVING ".$IAPPass['order'];
	}

	if (isset($IAPPass['order'])) {
		$IAPSql = $IAPSql." ORDER BY ".$IAPPass['order'];
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---sql = ".$IAPSql."<br />";
	}

	$IAPRet = (array) IAPProcessMySQL("select", $IAPSql);
	if ($IAPRet['numrows'] == 0) {
		$IAPRet['data'] = (array) IAP_Set_Initial($IAPPass);
	} 

/*
	if ($IAPDataConn->errno != 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...--- Error: query in error<br />";
		}

		$IAPErr = array('retcode' => -301,
						'retmsg' => "SQL Error",
						'sql' => $IAPSql,
						'mserrno' => $IAPDataConn->errno,
						'mserrmsg' => $IAPDataConn->error,
						'module' => basename(__FILE__),
						'line' => __LINE__);
		IAP_MySQL_Error($IAPErr);
		return($IAPErr);
	}
*/

	return($IAPRet);
}

function IAP_Unload_Join_Array($joins) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	$j = "";
	foreach($joins as $join) {
		if (isset($join['type'])) {
			switch($join['type']) {
				case " ":
					$j = $j."JOIN ";
					break;
				case "f";
					$j = $j."FULL JOIN ";
					break;
				case "l";
					$j = $j."LEFT JOIN ";
					break;
				case "r";
					$j = $j."RIGHT JOIN ";
					break;
				default:
					echo "<span class=iapError>IAP INTERNAL ERROR: JOIN - Improperly formatted join type. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
					die;
			}
		} else {
				echo "<span class=iapError>IAP INTERNAL ERROR: JOIN - No TYPE provided. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				die;
		}
		if (isset($join['table'])) {
			require_once("IAPDBTables.php");

			if ($_REQUEST['debugme'] == "Y") {
				echo ">>>--- Going to DBTables<br>";
			}

			$t = IAP_Get_Table(array("table" => $join['table']));
			if (empty($t)) {
				echo "<span class=iapError>IAP INTERNAL ERROR: JOIN - table in join list not found. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				die;
			}
			$join['real_table'] = $t;
		} else {
				echo "<span class=iapError>IAP INTERNAL ERROR: JOIN - No Table provided. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
				die;
		}

		if ($_REQUEST['debugme'] == "Y") {
			echo ">>>--- Join table is ".$t."<br>";
		}

		if (isset($join['real_table'])) {
			$j = $j.$join['real_table']." ";
		} else {
			echo "<span class=iapError>IAP INTERNAL ERROR: JOIN - No valid TABLE specified in join list. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			die;
		}
		if (isset($join['on'])) {
			$j = $j."ON ".$join['on']." ";
		} else {
			echo "<span class=iapError>IAP INTERNAL ERROR: JOIN - No ON clause specified in join list. [FATAL]<br>Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br>";
			die;
		}
	}

	return($j);	
}

function IAP_Update_Data($IAPInRec, $IAPTbl = NULL) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Update_Data with table of [".$IAPTbl."] InRec of <pre>";
		var_dump($IAPInRec);
		echo "</pre>";
	}

	$IAPInrec = (array) $IAPInRec;
	if (is_null($IAPTbl)) {
		$IAPTable = $IAPInRec['table'];
	} else {
		$IAPPass['table'] = $IAPTbl;
		require_once("IAPDBTables.php");
		$IAPTable = IAP_Get_Table($IAPPass);
		if ($IAPTable < 0) {
			return(array('retcode' => -101, 'retmsg' => "Table name not found."));
		}
		$IAPPass['real_table'] = $IAPTable;
	}
	$IAPSql = "SELECT COLUMN_NAME, NUMERIC_PRECISION, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA= '".DB_NAME."' AND TABLE_NAME = '".$IAPTable."';";

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---col sql = ".$IAPSql."<br />";
	}


	$IAPRet = (array) IAPProcessMySQL("select", $IAPSql);
	if ($IAPRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		$_REQUEST['IAPmodule'] = basename(__FILE__);
		$_REQUEST['IAPline'] = __LINE__;
		IAP_WPDB_Errors("INFORMATION_SCHEMA.COLUMNS", "Select", $wpdb);
		return(array('retcode' => -102, 'retmsg' => "Cannot retrieve schema columns."));	} elseif ($IAPRet['numrows'] == 0) {
		
		if ($_REQUEST['debugme'] == "Y") {
			echo "...---cols returned null. return -102.<br />";
		}

		return(array('retcode' => -102, 'retmsg' => "Cannot retrieve schema columns."));
	}

	$IAPCols = $IAPRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---back with cols of<pre>";
		var_dump($IAPCols);
		echo "</pre>";
	}

	if ($IAPInRec['status'] == "NEW") {
		$IAPRet = IAP_Insert_Row($IAPTable, $IAPCols, $IAPInRec);
		if ($IAPRet['retcode'] < 0) {
			return($IAPRet['retcode']);
		} else {
			return($IAPRet['data']);		
		}
	} else {
		$IAPRet = IAP_Update_Row($IAPTable, $IAPCols, $IAPInRec);
		if ($IAPRet['retcode'] < 0) {
			return($IAPRet['retcode']);
		}
	}
	return(TRUE);
}

function IAP_Insert_Row($IAPTable, $IAPCols, $IAPRow) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Insert_Row.<br />";
	}

	$IAPColNames = "";
	$IAPColValues = "";
	foreach ($IAPCols as $IAPCol) {
		$IAPCol = (array) $IAPCol;
		if ($IAPCol['COLUMN_NAME'] != "tblgrp"
		and $IAPCol['COLUMN_NAME'] != "tbljoin") {
			$IAPColName = $IAPCol['COLUMN_NAME'];
			if (isset($IAPRow[$IAPColName])) {
				$IAPColNames = $IAPColNames."`, `".$IAPColName;
				if (is_null($IAPCol['NUMERIC_PRECISION'])) {
					if ($IAPTable == "iap_temphold") {
						$IAPValue = "'".IAP_Escape_Field($IAPRow[$IAPColName])."'";
					} else {
						$IAPValue = "'".$IAPRow[$IAPColName]."'";
					}
				} else {
					$IAPValue = strval($IAPRow[$IAPColName]);
				}
				$IAPColValues = $IAPColValues.", ".$IAPValue;
			}
		}
	}
	$IAPSql = "INSERT INTO ".$IAPTable .
		" (".substr($IAPColNames, 2) .
		"`) Values (" .
		substr($IAPColValues, 2) .
		");";

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---sql = ".$IAPSql."<br />";
	}

	$IAPRet = (array) IAPProcessMySQL("insert", $IAPSql);
	return($IAPRet);
}

function IAP_Update_Row($IAPTable, $IAPCols, $IAPRow) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Update_Row.<br />";
	}

	$IAPColValues = "";
	$IAPWhere = "";
	foreach ($IAPCols as $IAPCol) {
		$IAPCol = (array) $IAPCol;
		if ($IAPCol['COLUMN_NAME'] != "tblgrp"
		and $IAPCol['COLUMN_NAME'] != "tbljoin") {
			$IAPColName = $IAPCol['COLUMN_NAME'];

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---column = ".$IAPColName."(".$IAPCol['COLUMN_KEY'].")<br />";
			}

			if (isset($IAPRow[$IAPColName])) {

				if ($_REQUEST['debugme'] == "Y") {
					echo "...---...processing column<br />";
					var_dump($IAPCol);
				}

				if (is_null($IAPCol['NUMERIC_PRECISION'])) {
					if ($IAPTable == "iap_temphold") {
						$IAPValue = '"'.IAP_Escape_Field($IAPRow[$IAPColName]).'"';
					} else {
						$IAPValue = '"'.$IAPRow[$IAPColName].'"';
					}
				} else {
					$IAPValue = strval($IAPRow[$IAPColName]);
				}

				if ($_REQUEST['debugme'] == "Y") {
					echo "...---...value = ".$IAPValue."<br />";
				}

				if ($IAPCol['COLUMN_KEY'] == "PRI") {
					$IAPWhere = $IAPWhere." AND ".$IAPColName." = ".$IAPValue;
				} else {
					$IAPColValues = $IAPColValues.", ".$IAPColName." = ".$IAPValue;
				}

				if ($_REQUEST['debugme'] == "Y") {
					echo "...---...columns = ".$IAPColValues." - where ".$IAPWhere."<br />";
				}
			} elseif ($IAPCol['COLUMN_KEY'] == "PRI") {
				return(-4);
			}
		}
	}
	$IAPSql = "UPDATE ".$IAPTable." SET ".substr($IAPColValues, 2)." WHERE ".substr($IAPWhere, 5).";";

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---sql = ".$IAPSql."<br />";
	}

	$IAPRet = (array) IAPProcessMySQL("update", $IAPSql);
	return($IAPRet);
}

function IAP_Delete_Row($IAPInRec, $IAPTbl = NULL, $IAPLike = "N") {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Delete_Row with table of ".$IAPTbl." and InRec of <pre>";
		var_dump($IAPInRec);
		echo "</pre>";
	}

	$IAPInrec = (array) $IAPInRec;
	if (is_null($IAPTbl)) {
		$IAPTable = $IAPInRec['table'];
	} else {
		$IAPPass['table'] = $IAPTbl;
		require_once("IAPDBTables.php");
		$IAPTable = IAP_Get_Table($IAPPass);
		if ($IAPTable < 0) {
			return(array('retcode' => -101, 'retmsg' => "Table name not found."));
		}
		$IAPPass['real_table'] = $IAPTable;
	}
	$IAPSql = "SELECT COLUMN_NAME, NUMERIC_PRECISION, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA= '".DB_NAME."' AND TABLE_NAME = '".$IAPTable."' AND COLUMN_KEY = 'PRI';";

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---col sql = ".$IAPSql."<br />";
	}

	$IAPRet = (array) IAPProcessMySQL("select", $IAPSql);
	if ($IAPRet['retcode'] < 0) {
		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		$_REQUEST['IAPmodule'] = basename(__FILE__);
		$_REQUEST['IAPline'] = __LINE__;
		IAP_WPDB_Errors("INFORMATION_SCHEMA.COLUMNS", "Select", $wpdb);
		return(array('retcode' => -102, 'retmsg' => "Cannot retrieve schema columns."));
	} elseif ($IAPRet['numrows'] == 0) {
		
		if ($_REQUEST['debugme'] == "Y") {
			echo "...---cols returned null. return -102.<br />";
		}

		return(array('retcode' => -102, 'retmsg' => "Cannot retrieve schema columns."));
	}

	$IAPCols = $IAPRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---cols are<pre>";
		var_dump($IAPCols);
		echo "</pre>";
	}

	$IAPWhere = "";
	foreach ($IAPCols as $IAPCol) {
		$IAPCol = (array) $IAPCol;
		if ($IAPCol['COLUMN_NAME'] != "tblgrp"
		and $IAPCol['COLUMN_NAME'] != "tbljoin") {
			$IAPColName = $IAPCol['COLUMN_NAME'];

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---column = ".$IAPColName."(".$IAPCol['COLUMN_KEY'].")<br />";
			}

			if (isset($IAPInRec[$IAPColName])) {

				if ($_REQUEST['debugme'] == "Y") {
					echo "...---...processing column<br />";
					var_dump($IAPCol);
				}

				if (is_null($IAPCol['NUMERIC_PRECISION'])) {
					$IAPValue = "'".trim($IAPInRec[$IAPColName]);
				} else {
					$IAPValue = strval($IAPInRec[$IAPColName]);
				}
				if ($IAPLike == "Y") {
					$IAPValue = $IAPValue."%";
				}
				if (is_null($IAPCol['NUMERIC_PRECISION'])) {
					$IAPValue = $IAPValue."'";
				}

				if ($_REQUEST['debugme'] == "Y") {
					echo "...---...value = ".$IAPValue."<br />";
				}

				if ($IAPCol['COLUMN_KEY'] == "PRI") {
					if ($IAPValue != "ALL"
					and $IAPValue != "'ALL'") {
						if ($IAPLike == "Y") {
							$IAPWhere = $IAPWhere." AND ".$IAPColName." LIKE ".$IAPValue;
						} else {
							$IAPWhere = $IAPWhere." AND ".$IAPColName." = ".$IAPValue;
						}
					}
				}

				if ($_REQUEST['debugme'] == "Y") {
					echo "...---...where ".$IAPWhere."<br />";
				}
			} elseif ($IAPCol['COLUMN_KEY'] == "PRI") {
				if (strpos($IAPColName, "seq") === FALSE
				and strpos($IAPColName, "session") === FALSE) {
					if ($IAPLike != "Y") {
						return(array('retcode' => -4));
					}
				}

				if ($_REQUEST['debugme'] == "Y") {
					echo "...---...Seq not specified. processing generic key.<br />";
				}
			}
		}
	}
	$IAPSql = "DELETE FROM ".$IAPTable." WHERE ".substr($IAPWhere, 5).";";

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---sql = ".$IAPSql."<br />";
	}

	$IAPRet = (array) IAPProcessMySQL("delete", $IAPSql);
	return($IAPRet['retcode']);

}

function IAP_Delete_PartKey($IAPInRec, $IAPTbl = NULL) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Delete_PartKey with table of ".$IAPTbl." and InRec of <pre>";
		var_dump($IAPInRec);
		echo "</pre>";
	}

	$IAPInrec = (array) $IAPInRec;
	if (is_null($IAPTbl)) {
		$IAPTable = $IAPInRec['table'];
	} else {
		$IAPPass['table'] = $IAPTbl;
		require_once("IAPDBTables.php");
		$IAPTable = IAP_Get_Table($IAPPass);
		if ($IAPTable < 0) {
			return(array('retcode' => -101, 'retmsg' => "Table name not found."));
		}
		$IAPPass['real_table'] = $IAPTable;
	}
	$IAPSql = "SELECT COLUMN_NAME, NUMERIC_PRECISION, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA= '".DB_NAME."' AND TABLE_NAME = '".$IAPTable."' AND COLUMN_KEY = 'PRI';";

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---col sql = ".$IAPSql."<br />";
	}

	$IAPRet = (array) IAPProcessMySQL("select", $IAPSql);
	if ($IAPRet['retcode'] < 0) {
		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		$_REQUEST['IAPmodule'] = basename(__FILE__);
		$_REQUEST['IAPline'] = __LINE__;
		IAP_WPDB_Errors("INFORMATION_SCHEMA.COLUMNS", "Select", $wpdb);
		return(array('retcode' => -102, 'retmsg' => "Cannot retrieve schema columns."));
	} elseif ($IAPRet['numrows'] == 0) {
		
		if ($_REQUEST['debugme'] == "Y") {
			echo "...---cols returned null. return -102.<br />";
		}

		return(array('retcode' => -102, 'retmsg' => "Cannot retrieve schema columns."));
	}

	$IAPCols = $IAPRet['data'];

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---cols are<pre>";
		var_dump($IAPCols);
		echo "</pre>";
	}

	$IAPWhere = "";
	foreach ($IAPCols as $IAPCol) {
		$IAPCol = (array) $IAPCol;
		if ($IAPCol['COLUMN_NAME'] != "tblgrp"
		and $IAPCol['COLUMN_NAME'] != "tbljoin") {
			$IAPColName = $IAPCol['COLUMN_NAME'];

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---column = ".$IAPColName."(".$IAPCol['COLUMN_KEY'].")<br />";
			}

			if (isset($IAPInRec[$IAPColName])) {

				if ($_REQUEST['debugme'] == "Y") {
					echo "...---...processing column<br />";
					var_dump($IAPCol);
				}

				if (is_null($IAPCol['NUMERIC_PRECISION'])) {
					$IAPValue = "'".trim($IAPInRec[$IAPColName]);
				} else {
					$IAPValue = strval($IAPInRec[$IAPColName]);
				}

				if (is_null($IAPCol['NUMERIC_PRECISION'])) {
					$IAPValue = $IAPValue."'";
				}

				if ($_REQUEST['debugme'] == "Y") {
					echo "...---...value = ".$IAPValue."<br />";
				}

				if ($IAPCol['COLUMN_KEY'] == "PRI") {
					if ($IAPValue != "ALL"
					and $IAPValue != "'ALL'") {
						$IAPWhere = $IAPWhere." AND ".$IAPColName." = ".$IAPValue;
					}
				}

				if ($_REQUEST['debugme'] == "Y") {
					echo "...---...where ".$IAPWhere."<br />";
				}
			}
		}
	}
	$IAPSql = "DELETE FROM ".$IAPTable." WHERE ".substr($IAPWhere, 5).";";

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---sql = ".$IAPSql."<br />";
	}

	$IAPRet = (array) IAPProcessMySQL("delete", $IAPSql);
	return($IAPRet['retcode']);

}


// --- Savearea Table

function IAP_Create_Savearea($IAPSource, $IAPToSave, $IAPClient = -999) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Create_Savearea with source of ".$IAPSource.".<br />";
	}

	if ($IAPClient == -999) {
		$IAPCurrentUser = wp_get_current_user();
		$IAPUser = $IAPCurrentUser->ID;
	} else {
		$IAPUser = $IAPClient;
	}

	$IAPUserIP = str_replace(".", "_", $_SERVER['REMOTE_ADDR']);
	if ($IAPUserIP == "::1") {
		$IAPUserIP = "127_0_0_1";
	}
	if ($_REQUEST['debugme'] == "Y") {
		echo "...---User IP is [";
		var_dump($IAPUserIP);
		echo "]<br />";
	}

	$IAPSaveAreaKey = $IAPUserIP."-".$IAPSource."-".$IAPUser;
	if ($IAPClient != 0) {
		$IAPKDate = date("Yz");
		$IAPKRan = strval(rand());

		$IAPSaveAreaKey = $IAPSaveAreaKey."-".$IAPKDate.$IAPKRan;
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "......making sure no other savearea exists with key of ".$IAPSaveAreaKey.".<br />";
	}

	if (!IAP_Remove_Savearea($IAPSource, $IAPClient)) {
		return(-301);
	}

	if ($IAPClient != 0) {
		$IAPKRow = array("thk_key" => $IAPSaveAreaKey , "thk_ip_addr" => $IAPUserIP, "thk_source" => $IAPSource, "thk_client" => $IAPUser, "thk_date" => $IAPKDate, "thk_rannum" => $IAPKRan, "status" => "NEW");

		$IAPRet = IAP_Update_Data($IAPKRow, "iaptkey");
		if ($IAPRet['retcode'] < 0) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---returned an error.<br />";
			}

			return(-302);
		}
	}

	$IAPSerSave = serialize($IAPToSave);
	if (is_null($IAPSerSave)) {
		echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: Error compressing the savearea [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
		return(-303);
	}

	$IAPSerSave = str_replace('"', "~", $IAPSerSave);

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---serialized data <pre>";
		var_dump($IAPSerSave);
		echo "</pre>";
	}

	$IAPSeq = 1;
	$IAPToWrite = str_split($IAPSerSave, 500);
	$IAPToWrite = (array) $IAPToWrite;

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---array of records to write:<pre>";
		var_dump($IAPToWrite);
		echo "</pre>";
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---preparing to write savearea with key of ".$IAPSaveAreaKey." and ".strval(count($IAPToWrite))." segements.<br />";
	}

	foreach ($IAPToWrite as $IAPRowValue) {
		$IAPRow = array("th_key" => $IAPSaveAreaKey , "th_seq" => $IAPSeq, "th_value" => $IAPRowValue, "th_source" => $IAPSource, "status" => "NEW");

		if (IAP_Update_Data($IAPRow, "iaptemp") < 0) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---returned an error. Going to error routine.<br />";
			}

			return(-304);
		}

		$IAPSeq = $IAPSeq + 1;
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---returning saveareakey of ".$IAPSaveAreaKey."<br />";
	}

	return($IAPSaveAreaKey);
}

function IAP_Get_Savearea($IAPSource, $IAPClient = -999) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Savearea with source of ".$IAPSource." and client of ".$IAPClient.".<br />";
	}

	if ($IAPClient == -999) {
		$IAPCurrentUser = wp_get_current_user();
		$IAPUser = $IAPCurrentUser->ID;
	} else {
		$IAPUser = $IAPClient;
	}
	
	$IAPUserIP = strtr($_SERVER['REMOTE_ADDR'], ".", "_");
	if ($IAPUserIP == "::1") {
		$IAPUserIP = "127_0_0_1";
	}

	if ($IAPClient == 0) {
		$IAPSaveAreaKey = $IAPUserIP."-".$IAPSource."-".$IAPUser;
	} else {
		if ($_REQUEST['debugme'] == "Y") {
			echo "...---retreiving savearea key for ".$IAPUserIP."-".$IAPSource."-".$IAPUser." from temphold_keys.<br />";
		}

		$IAPKPartKey = $IAPUserIP."-".$IAPSource."-".$IAPUser;

		$IAPPass = array();
		$IAPPass['table'] = "iaptkey";
		$IAPPass['cols'] = "thk_key";
		$IAPPass['where'] = "thk_key LIKE '".$IAPKPartKey."%'";
		$IAPRet = IAP_Get_Rows($IAPPass);
		if ($IAPRet['retcode'] < 0) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---returned an error.<br />";
			}

			return(-311);
		}
		if ($IAPRet['numrows'] == 0) {
		
			if ($_REQUEST['debugme'] == "Y") {
				echo "...---could not retrieve savearea key of ".$IAPKPartKey." returning NULL.<br />";
			}

			return(NULL);
		}
		$IAPSK = (array) $IAPRet['data'];
		$IAPSaveAreaKey = $IAPSK[0]['thk_key'];
	}
	if ($_REQUEST['debugme'] == "Y") {
		echo "...---saveareakey is ".$IAPSaveAreaKey.".<br />";
	}

	$IAPPass = array();
	$IAPPass['table'] = "iaptemp";
	$IAPPass['where'] = "th_key LIKE '".$IAPSaveAreaKey."%'";
	$IAPPass['order'] = "th_seq";
	$IAPRet = IAP_Get_Rows($IAPPass);
	if ($IAPRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-312);
	}
	if ($IAPRet['numrows'] == 0) {
	
		if ($_REQUEST['debugme'] == "Y") {
			echo "...---could not retrieve savearea for key of ".$IAPSaveAreaKey." returning NULL.<br />";
		}

		return(NULL);		
	}
	$IAPData = (array)$IAPRet['data'];

// These sources expire at a specific interval
	switch ($IAPSource) {
		case "IAPUID":
			$IAPExp = 1;
			break;
		default:
			$IAPExp = 0;
	}

	$IAPSaveValue = "";
	foreach ($IAPData as $IAPRow) {
		$IAPRow = (array) $IAPRow;

		if ($_REQUEST['debugme'] == "Y") {
			echo "<br />...---processing row for ".$IAPRow['th_value'].".<br />";
		}

		if ($IAPExp > 0) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---checking if expired with date of ".$IAPRow['th_when'].".<br />";
			}

			if (!(LHC_Sec_Check_Expired($IAPRow['th_when'], $IAPExp))) {
				$IAPSaveValue = $IAPSaveValue.$IAPRow['th_value'];
			}
		} else {
			$IAPSaveValue = $IAPSaveValue.$IAPRow['th_value'];
		}
	}
	if ($IAPSaveValue == "") {
		return(NULL);
	}

	$IAPSaveValue = str_replace("~", '"', $IAPSaveValue);
	$IAPSaveArea = unserialize($IAPSaveValue);
	if ($IAPSaveArea === FALSE) {
		echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: Error uncompressing the savearea. [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		return(-313);
	}

	if ($IAPSource == "IAPUID") {
// These sources get rewritten to update the time stamp in th_when

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---rewriting record to update time stamp.<br />";
		}

		IAP_Update_Savearea($IAPSource, $IAPSaveArea, $IAPClient);
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---leaving IAP_Get_Savearea<br />";
	}

	return($IAPSaveArea);
}

function IAP_Get_Savearea2($IAPSource, $IAPClient = -999) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Get_Savearea2 with source of ".$IAPSource." and client of ".$IAPClient.".<br />";
	}

	$IAPUserIP = strtr($_SERVER['REMOTE_ADDR'], ".", "_");
	if ($IAPUserIP == "::1") {
		$IAPUserIP = "127_0_0_1";
	}
	$IAPSaveAreaKey = $IAPUserIP."-".$IAPSource."-".$IAPClient;

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---saveareakey is ".$IAPSaveAreaKey.".<br />";
	}

	$IAPPass = array();
	$IAPPass['table'] = "iaptemp";
	$IAPPass['where'] = "th_key LIKE '".$IAPSaveAreaKey."%'";
	$IAPPass['order'] = "th_seq";
	$IAPRet = IAP_Get_Rows($IAPPass);
	if ($IAPRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-311);
	}
	if ($IAPRet['numrows'] == 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---could not retrieve savearea for key of ".$IAPSaveAreaKey." returning NULL.<br />";
		}

		return(NULL);
	}
	$IAPData = (array) $IAPRet['data'];

// These sources expire at a specific interval
	switch ($IAPSource) {
		case "IAPUID":
			$IAPExp = 1;
			break;
		default:
			$IAPExp = 0;
	}

	$IAPSaveValue = "";
	foreach ($IAPData as $IAPRow) {
		$IAPRow = (array) $IAPRow;

		if ($_REQUEST['debugme'] == "Y") {
			echo "<br />...---processing row for ".$IAPRow['th_value'].".<br />";
		}

		if ($IAPExp > 0) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---checking if expired with date of ".$IAPRow['th_when'].".<br />";
			}

			if (!(IAP_Sec_Check_Expired($IAPRow['th_when'], $IAPExp))) {
				$IAPSaveValue = $IAPSaveValue.$IAPRow['th_value'];
			}
		} else {
			$IAPSaveValue = $IAPSaveValue.$IAPRow['th_value'];
		}
	}
	if ($IAPSaveValue == "") {
		return(NULL);
	}

	$IAPSaveValue = str_replace('~', '"', $IAPSaveValue);
	$IAPSaveArea = unserialize($IAPSaveValue);
	if (is_null($IAPSaveArea)) {
		echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: Error uncompressing the savearea - ".IAP_JSON_Errors($IAPJError)." [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</span><br />";
		return(-313);
	}

	if ($IAPSaveArea == FALSE) {
		IAP_Error_Handler("SE", basename(__FILE__),__LINE__, "Unserialize Error", $php_errormsg);
		return(NULL);
	}

	if ($IAPSource == "IAPUID") {
// These sources get rewritten to update the time stamp in th_when

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---rewriting record to update time stamp.<br />";
		}

		IAP_Update_Savearea($IAPSource, $IAPSaveArea, $IAPClient);
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---leaving IAP_Get_Savearea<br />";
	}

	return($IAPSaveArea);
}

function IAP_Update_Savearea($IAPSource, $IAPToSave, $IAPClient = -999) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Update_SaveArea with source of ".$IAPSource." and client of ".strval($IAPClient).".<br />";
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "<pre>";
		var_dump($IAPToSave);
		echo "</pre>";
	}

	if ($IAPClient == -999) {
		$IAPCurrentUser = wp_get_current_user();
		$IAPUser = $IAPCurrentUser->ID;
	} else {
		$IAPUser = $IAPClient;
	}

	$IAPUserIP = strtr($_SERVER['REMOTE_ADDR'], ".", "_");
	if ($IAPUserIP == "::1") {
		$IAPUserIP = "127_0_0_1";
	}
	if ($IAPClient == 0) {
		$IAPSaveAreaKey = $IAPUserIP."-".$IAPSource."-".$IAPUser;
	} else {
		$IAPKPartKey = $IAPUserIP."-".$IAPSource."-".$IAPUser;
		$IAPPass = array();
		$IAPPass['table'] = "iaptkey";
		$IAPPass['where'] = "thk_key LIKE '".$IAPKPartKey."%'";
		$IAPRet = IAP_Get_Rows($IAPPass);
		if ($IAPRet['retcode'] < 0) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---returned an error.<br />";
			}

			return(-321);
		}
		if ($IAPRet['numrows'] == 0) {
		
			if ($_REQUEST['debugme'] == "Y") {
				echo "...---could not retrieve savearea key of ".$IAPKPartKey." returning NULL.<br />";
			}

			return(NULL);		
		}
		$IAPSK = (array)$IAPRet['data'];
		$IAPSaveAreaKey = $IAPSK[0]['thk_key'];
		$IAPKDate = $IAPSK[0]['thk_date'];
		$IAPKRan = $IAPSK[0]['thk_rannum'];
	}
	if ($_REQUEST['debugme'] == "Y") {
		echo "...---saveareakey is ".$IAPSaveAreaKey.". removing old savearea.<br />";
	}

	IAP_Remove_Savearea($IAPSource, $IAPClient);

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---savearea removed. now prepare and write updated savearea.<br />";
	}

	$IAPSerSave = serialize($IAPToSave);
	if (is_null($IAPSerSave)) {
		echo "<span style='color:red;'><strong>IAP INTERNAL ERROR: Error compressing the savearea [FATAL]<br />Please notify Support and provide this reference of /".basename(__FILE__)."/".__LINE__."</font><br />";
		return(-322);
	}


	$IAPSerSave = str_replace('"', '~', $IAPSerSave);

	$IAPSeq = 1;
	$IAPToWrite = str_split($IAPSerSave, 500);
	$IAPToWrite = (array) $IAPToWrite;

	if ($_REQUEST['debugme'] == "Y") {
		echo "<pre>";
		var_dump($IAPToWrite);
		echo "</pre>";
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---preparing to write savearea with key of ".$IAPSaveAreaKey." and ".strval(count($IAPToWrite))." segements.<br />";
	}

	foreach ($IAPToWrite as $IAPRowValue) {

		$IAPRow = array("th_key" => $IAPSaveAreaKey , "th_seq" => $IAPSeq, "th_value" => $IAPRowValue, "th_source" => $IAPSource, "status" => "NEW");
		if (IAP_Update_Data($IAPRow, "iaptemp") < 0) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---returned an error. Going to error routine.<br />";
			}

			return(-323);
		}
		$IAPSeq = $IAPSeq + 1;
	}

	if ($IAPClient != 0) {
		$IAPKRow = array("thk_key" => $IAPSaveAreaKey , "thk_ip_addr" => $IAPUserIP, "thk_source" => $IAPSource, "thk_client" => $IAPUser,"thk_date" => $IAPKDate, "thk-rannum" => $IAPKRan, "status" => "NEW");

		if (IAP_Update_Data($IAPKRow, "iaptkey") < 0) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---returned an error.<br />";
			}

			return(-324);
		}
	}

	return(TRUE);
}

function IAP_Remove_Savearea($IAPSource, $IAPClient = -999) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Remove_Savearea with source of ".$IAPSource." and client is ".$IAPClient.".<br />";
	}

	if ($IAPClient == -999) {
		$IAPCurrentUser = wp_get_current_user();
		$IAPUser = $IAPCurrentUser->ID;
	} else {
		$IAPUser = $IAPClient;
	}

	$IAPUserIP = strtr($_SERVER['REMOTE_ADDR'], ".", "_");
	if ($IAPUserIP == "::1") {
		$IAPUserIP = "127_0_0_1";
	}
	if ($IAPClient == 0) {
		$IAPSaveAreaKey = $IAPUserIP."-".$IAPSource."-".$IAPUser;
	} else {
		$IAPKPartKey = $IAPUserIP."-".$IAPSource."-".$IAPUser;
		$IAPPass = array();
		$IAPPass['table'] = "iaptkey";
		$IAPPass['cols'] = "thk_key";
		$IAPPass['where'] = "thk_key LIKE '".$IAPKPartKey."%'";
		$IAPRet = IAP_Get_Rows($IAPPass);
		if ($IAPRet['retcode'] < 0) {

			if ($_REQUEST['debugme'] == "Y") {
				echo "...---returned an error.<br />";
			}

			return(-331);
		}
		if ($IAPRet['numrows'] == 0) {
		
			if ($_REQUEST['debugme'] == "Y") {
				echo "...---could not retrieve savearea key of ".$IAPKPartKey." returning NULL.<br />";
			}

			return(TRUE);
		}
		$IAPSK = (array)$IAPRet['data'];
		$IAPSaveAreaKey = $IAPSK[0]['thk_key'];
	}
	if ($_REQUEST['debugme'] == "Y") {
		echo "...---saveareakey is ".$IAPSaveAreaKey.".<br />";
	}

	if (is_null($IAPSaveAreaKey)) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---saveareakey is NULL so return good return (TRUE).<br />";
		}

		return(TRUE);
	}
	$IAPRet = IAP_Remove_Savearea_By_Key($IAPSaveAreaKey);

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---back from Remove_Savearea_By_Key with return of ".strval($IAPRet).".<br />";
	}

	if ($IAPRet <> TRUE) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returning error (-1).<br />";
		}

		return(-332);
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---returning good return (TRUE).<br />";
	}

	return(TRUE);
}

function IAP_Remove_Savearea_By_Key($IAPSaveAreaKey) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Remove_Savearea_By_Key with key of ".$IAPSaveAreaKey.".<br />";
	}

	$IAPRow = array("th_key" => $IAPSaveAreaKey);
	if (IAP_Delete_Row($IAPRow, "iaptemp") < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error.<br />";
		}

		return(-341);
	}

	$IAPKRow = array("thk_key" => $IAPSaveAreaKey);
	if (IAP_Delete_Row($IAPKRow, "iaptkey") < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error.<br />";
		}

		return(-342);
	}

	if ($_REQUEST['debugme'] == "Y") {
		echo "...---returning good return (TRUE).<br />";
	}
	return(TRUE);
}

function IAP_Remove_Appl_Savearea($IAPAppl) {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_Remove_Appl_Savearea with application of ".$IAPAppl.".<br />";
	}

	$IAPUserIP = strtr($_SERVER['REMOTE_ADDR'], ".", "_");
	if ($IAPUserIP == "::1") {
		$IAPUserIP = "127_0_0_1";
	}

	$IAPSql = "DELETE FROM iap_temphold WHERE th_key like '".$IAPUserIP."%' AND th_source like '".$IAPAppl."%';";
	$IAPRet = (array) IAPProcessMySQL("delete", $IAPSql);
	if ($IAPRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error.<br />";
		}

		return(-351);
	}

	$IAPSql = "DELETE FROM iap_temphold_keys WHERE thk_key like '".$IAPUserIP."%' AND thk_source like '".$IAPAppl."%';";
	$IAPRet = (array) IAPProcessMySQL("delete", $IAPSql);
	if ($IAPRet['retcode'] < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error.<br />";
		}

		return(-352);
	}

	return(TRUE);
}

function IAP_CleanUp_SaveArea() {

	$_REQUEST['ModTrace'][] = basename(__FILE__)."-(".__FUNCTION__.")-(".__LINE__.")";

	if ($_REQUEST['debugme'] == "Y") {
		echo ">>>In IAP_CleanUp_Savearea.<br />";
	}

	$IAPUserIP = strtr($_SERVER['REMOTE_ADDR'], ".", "_");
	if ($IAPUserIP == "::1") {
		$IAPUserIP = "127_0_0_1";
	}
	if ($_REQUEST['debugme'] == "Y") {
		echo "...---saveareakey of ".$IAPUserIP."%.<br />";
	}

	$IAPRow = array("th_key" => $IAPUserIP);
	if (IAP_Delete_Row($IAPRow, "iaptemp", "Y") < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-361);
	}

	$IAPKRow = array("thk_key" => $IAPUserIP);
	if (IAP_Delete_Row($IAPKRow, "iaptkey", "Y") < 0) {

		if ($_REQUEST['debugme'] == "Y") {
			echo "...---returned an error. Going to error routine.<br />";
		}

		return(-362);
	}

	return(TRUE);
}

?>