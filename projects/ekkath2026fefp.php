<?php
$currentYear = date("Y");
$birth_year = "";
$age = "";
$paidia = "";
$forologiteo = "";
$syntel1 = "";
$forol1 = "";
$foros1 = "";
$syntel2 = "";
$forol2 = "";
$foros2 = "";
$syntel3 = "";
$forol3 = "";
$foros3 = "";
$syntel4 = "34";
$forol4 = "";
$foros4 = "";
$syntel5 = "39";
$forol5 = "";
$foros5 = "";
$syntel6 = "44";
$forol6 = "";
$foros6 = "";
$meiosi_1_forou = "";
$meiosi_2_forou = "";
$meiosi_tel_forou = "";
$fmy_year = "";
        
function f($num) {
return number_format((float)$num, 2, ',' ,'.');
}

if ($_POST) {
    $forologiteo = $_POST['forologiteo'];
	$paidia = $_POST['paidia'];
    $birth_year = $_POST['birth_year'];

		$age = $currentYear - $birth_year;
        if ($age<=25) {
            $syntel1 = 0;
        }
            elseif ($paidia>=4) {
            $syntel1 = 0;
        }
            else {
            $syntel1 = 9;
        }
        if ($forologiteo>10000) {
            $forol1 = 10000;
        }
            else {
            $forol1 = $forologiteo;
        }
        $foros1 = $forol1 * (float)$syntel1 / 100;
        if ($age<=25) {
            $syntel2 = 0;
        }
            elseif ($age <=30) {
            $syntel2 = 9;
        }
            elseif ($paidia == 0) {
            $syntel2 = 20;
        }
            elseif ($paidia == 1) {
            $syntel2 = 18;
        }
            elseif ($paidia == 2) {
            $syntel2 = 16;
        }
            elseif ($paidia == 3) {
            $syntel2 = 9;
        }
            elseif ($paidia >= 4) {
            $syntel2 = 0;
        }
            else {
            echo "ΛΑΘΟΣ";
        }
        if ($forologiteo>10000) {
            if ($forologiteo>20000) {
                $forol2 = 10000;
            }
            else {
                $forol2 = $forologiteo-10000;
            }
        }
        $foros2 = (float)$forol2 * (float)$syntel2 / 100;
		if($paidia == 0) {
            $syntel3 = 26;
        }
            elseif($paidia == 1) {
                $syntel3 = 24; 
            }
            elseif($paidia == 2) {
                $syntel3 = 22;
            }
            elseif($paidia == 3) {
                $syntel3 = 20;
            }
            elseif($paidia == 4) {
                $syntel3 = 18;
            }
            elseif($paidia == 5) {
                $syntel3 = 16;
            }
            elseif($paidia == 6) {
                $syntel3 = 14;
            }
            else {
            echo "ΛΑΘΟΣ";
            }
		if ($forologiteo>20000) {
            if ($forologiteo>30000) {
                $forol3 = 10000;
            }
            else {
                $forol3 = $forologiteo-20000;
            }
		}
        $foros3 = (float)$forol3 * (float)$syntel3 / 100;
		if ($forologiteo>30000) {
            if ($forologiteo>40000) {
                $forol4 = 10000;
            }
            else {
                $forol4 = $forologiteo-30000;
            }
		}	
        $foros4 = (float)$forol4 * (float)$syntel4 / 100;
		if ($forologiteo>40000) {
            if ($forologiteo>60000) {
                $forol5 = 20000;
            }
            else {
                $forol5 = $forologiteo-40000;
            }
		}
        $foros5 = (float)$forol5 * (float)$syntel5 / 100;
		if ($forologiteo>60000) {
                $forol6 = $forologiteo-60000;
        }
        $foros6 = (float)$forol6 * (float)$syntel6 / 100;
		if ($paidia == 0) {
			$meiosi_1_forou = 777;
		}
			elseif ($paidia == 1) {
				$meiosi_1_forou = 900;
			}
			elseif ($paidia == 2) {
				$meiosi_1_forou = 1120;
			}
			elseif ($paidia == 3) {
				$meiosi_1_forou = 1340;
			}
			elseif ($paidia == 4) {
				$meiosi_1_forou = 1560;
			}
			elseif ($paidia == 5) {
				$meiosi_1_forou = 1780;
			}
		if ($forologiteo > 12000) {
            $meiosi_2_forou = (($forologiteo - 12000)/1000)*-20;
		}	
        $meiosi_tel_forou = (float)$meiosi_1_forou + (float)$meiosi_2_forou;	
        if ((float)$foros1 + (float)$foros2 + (float)$foros3 + (float)$foros4 + (float)$foros5 + (float)$foros6 - (float)$meiosi_tel_forou > 0) {
            $fmy_year = (float)$foros1 + (float)$foros2 + (float)$foros3 + (float)$foros4 + (float)$foros5 + (float)$foros6 - (float)$meiosi_tel_forou;
        }
            else {
                $fmy_year = 0;
            }
    }
?>

<!DOCTYPE html>
<html lang="el-GR">
<head>
<title>Υπολογισμός μισθοδοσίας & φόρου εισοδήματος</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f7fa;
    margin: 40px;
}

h3 {
    background: #2c3e50;
    color: white;
    padding: 10px;
    border-radius: 6px;
}

table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 30px;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

td {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

tr:hover {
    background: #f9f9f9;
}

input {
    padding: 6px;
    width: 100px;
}

input[type="submit"] {
    background: #27ae60;
    color: white;
    border: none;
    cursor: pointer;
    padding: 10px 15px;
    border-radius: 5px;
}

input[type="submit"]:hover {
    background: #219150;
}

/* δεξιά στοίχιση αριθμών */
.num {
    text-align: right;
    font-weight: bold;
}
.num1 {
    text-align: center;
}

/* τίτλοι πίνακα */
th {
    background: #34495e;
    color: white;
    padding: 10px;
}
</style>
</head>



<body>
<br><br><br><br>

<h3>Εκκαθάριση φόρου εισοδήματος έτους 2026</h3>
<br><br>

<form method="POST">
<table style="text-align:center;">

<tr>
<td class="num"><input type="number" name="forologiteo" value="<?= $_POST['forologiteo'] ?? '0' ?>"></td>
<td align="left"><b>Φορολογητέο εισόδημα</b></td>
<td colspan="2"><input type="submit" value="Υπολογισμός"></td>
<td colspan="4"></td>
</tr>


<tr>
<td class="num"><input type="number" name="paidia" value="<?= $_POST['paidia'] ?? '0' ?>"></td>
<td align="left"><b>Αριθμός παιδιών</b></td>
<td colspan="6"></td>
</tr>

<tr>
<td class="num"><input type="number" name="birth_year" value="<?= $_POST['birth_year'] ?? '2000' ?>"></td>
<td align="left"><b>Έτος γέννησης</b></td>
<td colspan="6"></td>
</tr>

<tr>
<td class="num"><?php echo $age?></td>
<td align="left"><b>ετών</b></td>
<td colspan="6"></td>
</tr>

<tr>
<td class="num"><?php echo f($fmy_year); ?></td>
<td align="left"><b>Ετήσιος φόρος εισοδήματος</b></td>
<td colspan="6"></td>
</tr>

<tr>
<td colspan="8"><br></td>
</tr>

<tr>
<td colspan="8" align="left"><b>Ανάλυση εκκαθάρισης φόρου</b></td>
</tr>

<tr>
<td width="100px">Eισόδημα από</td>
<td width="100px">Eισόδημα έως</td>
<td width="100px">% φόρου</td>
<td width="100px">Φορολ. εισόδημα</td>
<td width="100px">Φόρος κλίμακας</td>
<td width="20px">|</td>
<td width="100px">Μειώσεις φόρου</td>
<td width="100px"></td>
</tr>

<tr>
<td>0,00</td>
<td>10.000,00</td>
<td class="num1"><?php echo f($syntel1); ?></td>
<td class="num1"><?php echo f($forol1); ?></td>
<td class="num1"><?php echo f($foros1); ?></td>
<td width="20px">|</td>
<td width="100px">777,00</td>
<td width="100px">Χωρίς παιδιά</td>
</tr>

<tr>
<td>10.000,01</td>
<td>20.000,00</td>
<td class="num1"><?php echo f($syntel2); ?></td>
<td class="num1"><?php echo f($forol2); ?></td>
<td class="num1"><?php echo f($foros2); ?></td>
<td width="20px">|</td>
<td width="100px">900,00</td>
<td width="100px">Με 1 παιδί</td>
</tr>

<tr>
<td>20.000,01</td>
<td>30.000,00</td>
<td class="num1"><?php echo f($syntel3); ?></td>
<td class="num1"><?php echo f($forol3); ?></td>
<td class="num1"><?php echo f($foros3); ?></td>
<td width="20px">|</td>
<td width="100px">1.120,00</td>
<td width="100px">Με 2 παιδιά</td>
</tr>

<tr>
<td>30.000,01</td>
<td>40.000,00</td>
<td class="num1"><?php echo f($syntel4); ?></td>
<td class="num1"><?php echo f($forol4); ?></td>
<td class="num1"><?php echo f($foros4); ?></td>
<td width="20px">|</td>
<td width="100px">1.340,00</td>
<td width="100px">Με 3 παιδιά</td>
</tr>

<tr>
<td>40.000,01</td>
<td>60.000,00</td>
<td class="num1"><?php echo f($syntel5); ?></td>
<td class="num1"><?php echo f($forol5); ?></td>
<td class="num1"><?php echo f($foros5); ?></td>
<td width="20px">|</td>
<td width="100px">1.560,00</td>
<td width="100px">Με 4 παιδιά</td>
</tr>

<tr>
<td>60.000,01</td>
<td>και άνω</td>
<td class="num1"><?php echo f($syntel6); ?></td>
<td class="num1"><?php echo f($forol6); ?></td>
<td class="num1"><?php echo f($foros6); ?></td>
<td width="20px">|</td>
<td width="100px">1.780,00</td>
<td width="100px">Με 5 παιδιά</td>
</tr>

<tr>
<td></td>
<td>Μείωση φόρου</td>
<td class="num1"><?php echo f($meiosi_1_forou); ?></td>
<td class="num1"><?php echo f($meiosi_2_forou); ?></td>
<td class="num1"><?php echo f($meiosi_tel_forou); ?></td>
<td width="20px"></td>
<td width="100px"></td>
<td width="100px"></td>
</tr>

<tr>
<td colspan="3"></td>
<td>Ετήσιος φόρος εισοδήματος</td>
<td class="num1"><?php echo f($fmy_year); ?></td>
<td colspan="3"></td>
</tr>

</table>

</form>

</body>
</html>