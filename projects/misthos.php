<?php
$mikta = "";
$oromisthio = "";
$apod_yperergasia = "";
$apod_kyriaki = "";
$apod_nyxta = "";
$apod_synolo = "";
$eisf_ergaz = "";
$eisf_ergod = "";
$eisf_synolo = "";
$currentYear = date("Y");
$birth_year = "";
$age = "";
$paidia = "";
$forologiteo = "";
$fmy = "";
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
$plirotees = "";
$kostos_month = "";
        
function f($num) {
return number_format((float)$num, 2, ',' ,'.');
}

if ($_POST) {
    $mikta_pliri = $_POST['mikta_pliri'];
    $ores_ebdomadas = $_POST['ores_ebdomadas'];
    $ores_yperergasia = $_POST['ores_yperergasia'];
    $ores_kyriaki = $_POST['ores_kyriaki'];
    $ores_nyxta = $_POST['ores_nyxta'];
    $efka_ergaz = $_POST['efka_ergaz'];
    $efka_ergod = $_POST['efka_ergod'];
    $paidia = $_POST['paidia'];
    $birth_year = $_POST['birth_year'];

        (float)$mikta = ($mikta_pliri / 40) * $ores_ebdomadas;
        $oromisthio = ($mikta_pliri / 25) / 40 * 6;
		$apod_yperergasia = (float)$oromisthio * 1.2 * (float)$ores_yperergasia;
		$apod_kyriaki = (float)$oromisthio * 1.75 * $ores_kyriaki;
		$apod_nyxta = (float)$oromisthio * 1.25 * $ores_nyxta;
		$apod_synolo = $mikta + $apod_yperergasia + $apod_kyriaki + $apod_nyxta;
		$eisf_ergaz = $apod_synolo * (float)$efka_ergaz / 100;
		$eisf_ergod = $apod_synolo * (float)$efka_ergod / 100;
		$eisf_synolo = $eisf_ergaz + $eisf_ergod;
		$age = $currentYear - $birth_year;
        $forologiteo = ($apod_synolo - $eisf_ergaz) * 14;
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
        $foros2 = $forol2 * (float)$syntel2 / 100;
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
        $fmy = $fmy_year / 14;	
        $plirotees = $forologiteo/14 - $fmy;
        $kostos_month = $apod_synolo + $eisf_ergod;
 

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
<h3>Υπολογισμός μισθοδοσίας</h3>

<form method="POST">
<table>

<tr>
<td class="num"><input type="number" name="mikta_pliri" value="<?= $_POST['mikta_pliri'] ?? '920.00' ?>"></td>
<td class="num"><?php echo f($oromisthio)." € ωρομίσθιο"; ?></td>
<td>Μικτές αποδοχές (πλήρη απασχόληση - 40 ώρες)</td>
</tr>

<tr>
<td class="num"><?php echo f($mikta); ?></td>
<td class="num"><input type="number" name="ores_ebdomadas" value="<?= $_POST['ores_ebdomadas'] ?? '40' ?>"></td>
<td>Ώρες εργασίας / εβδομάδα</td>
</tr>

<tr>
<td class="num"><?php echo f($apod_yperergasia); ?></td>
<td class="num"><input type="number" name="ores_yperergasia" value="<?= $_POST['ores_yperergasia'] ?? '0' ?>"></td>
<td>Ώρες υπερεργασίας / μήνα (+20%)</td>
</tr>

<tr>
<td class="num"><?php echo f($apod_kyriaki); ?></td>
<td class="num"><input type="number" name="ores_kyriaki" value="<?= $_POST['ores_kyriaki'] ?? '0' ?>"></td>
<td>Ώρες Κυριακής / μήνα (+75%)</td>
</tr>

<tr>
<td class="num"><?php echo f($apod_nyxta); ?></td>
<td class="num"><input type="number" name="ores_nyxta" value="<?= $_POST['ores_nyxta'] ?? '0' ?>"></td>
<td>Ώρες νύχτα / μήνα (22:00-06:00) (+25%)</td>
</tr>

<tr>
<td class="num"><?php echo f($apod_synolo); ?></td>
<td class="num"></td>
<td>Σύνολο μικτών αποδοχών</td>
</tr>

<tr>
<td class="num"><?php echo f($eisf_ergaz); ?></td>
<td class="num"><input type="" name="efka_ergaz" value="<?= $_POST['efka_ergaz'] ?? '13.37' ?>"></td>
<td>Ασφαλιστικές εισφορές εργαζόμενου (%)</td>
</tr>

<tr>
<td class="num"><?php echo f($eisf_ergod); ?></td>
<td class="num"><input type="" name="efka_ergod" value="<?= $_POST['efka_ergod'] ?? '21.79' ?>"></td>
<td>Ασφαλιστικές εισφορές εργοδότη (%)</td>
</tr>

<tr>
<td class="num"><?php echo f($eisf_synolo); ?></td>
<td class="num"></td>
<td>Σύνολο ασφαλιστικών εισφορών</td>
</tr>

<tr>
<td class="num"></td>
<td class="num"><input type="number" name="paidia" value="<?= $_POST['paidia'] ?? '0' ?>"></td>
<td>Αριθμός παιδιών</td>
</tr>

<tr>
<td class="num"><?php echo $age." ετών"; ?></td>
<td class="num"><input type="number" name="birth_year" value="<?= $_POST['birth_year'] ?? '2000' ?>"></td>
<td>Έτος γέννησης</td>
</tr>

<tr>
<td class="num"><?php echo f($forologiteo)." €"; ?></td>
<td class="num"></td>
<td>Φορολογητέο εισόδημα</td>
</tr>

<tr>
<td class="num"><?php echo f($fmy); ?></td>
<td class="num"></td>
<td>Φόρος μισθωτών υπηρεσιών</td>
</tr>

<tr>
<td class="num"><?php echo f($plirotees); ?></td>
<td class="num"></td>
<td>Πληρωτέες αποδοχές (x14 μήνες)</td>
</tr>

<tr>
<td class="num"><?php echo f($kostos_month); ?></td>
<td class="num"></td>
<td>Μηνιαίο κόστος μισθοδοσίας (x14 μήνες)</td>
</tr>

<tr>
<td class="num"></td>
<td class="num"><input type="submit" value="Υπολογισμός"></td>
<td></td>
</tr>

</table>    


<h3>Υπολογισμός φόρου</h3>

Φορολογητέο εισόδημα: <?php echo f($forologiteo)." €"; ?>
<br><br>

<table style="text-align:center;">
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
<td></td>
<td></td>
<td>Ετήσια ποσά</td>
<td></td>
<td class="num1"><?php echo f($fmy_year); ?></td>
<td width="20px"></td>
<td width="100px"></td>
<td width="100px"></td>
</tr>

<tr>
<td></td>
<td></td>
<td>Μηνιαία ποσά</td>
<td></td>
<td class="num1"><?php echo f($fmy); ?></td>
<td width="20px"></td>
<td width="100px"></td>
<td width="100px"></td>
</tr>

</table>

</form>

</body>
</html>