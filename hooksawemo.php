<?php
///// formdesk webhook naar tabel
error_reporting(E_ALL);

// log voor de zekerheid elke call op de hook in een textfile
function loghookaction($logtekst)
{
    file_put_contents("logdata.txt", $logtekst, FILE_APPEND);
}

//log first for testing
loghookaction("\n ----- LOGGING WITH hooksawemo ----\n");

include_once "interfaceDB.php";

$t = new FFDb();

function cleantxt($str)
{
    $str = trim($str);
    //$str = utf8_encode($str);
    //$str = recode($str);
    return $str;
}

function recode($str)
{
    $ret = $str;
    switch (strtolower($str)) {
        case "helemaal mee oneens":
            $ret = "1";
            break;
        case "mee oneens":
            $ret = "2";
            break;
        case "niet mee eens, niet mee oneens":
            $ret = "3";
            break;
        case "mee eens":
            $ret = "4";
            break;
        case "helemaal mee eens":
            $ret = "5";
            break;
        case "weet ik niet / nvt":
            $ret = null;
            break;
    }
    return $ret;
}

loghookaction("Functies gedeclareerd...\n");
$time = date("d-m-y H:i:s");
$kenmerk = $_POST["submission_id"];
$koptype = "deelnemer";
$groep = $_POST["gemeente"];
$kop = $_POST["__submission"]["id"];
loghookaction("\nFormuliernummer: " . $kenmerk . "\n");
$vraagteller = 1;

$sql = "INSERT INTO prze_sawemo (kop, koptype, kenmerk, waarde, waardetype, peildatum, jaar, groep, meta) VALUES ";
$insert = "";
foreach ($_POST as $key => $val) {
    if (strpos($key, "raag_") > 0) {

        foreach ($val as $key2 => $val2) {
            $insert .= sprintf("(\"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\"),",
                $kop, $koptype, $key2, recode($val2), $key, $time, date("Y"), $groep, $vraagteller);
            $vraagteller++;
        }
    } elseif ($key == "domeinen") {

        foreach ($val as $key3 => $val3) {
            $insert .= sprintf("(\"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\"),",
                $kop, $koptype, $key3, $val3, $key, $time, date("Y"), $groep, $meta);
        }
    } else {
        $insert .= sprintf("(\"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\"),",
            $kop, $koptype, $kenmerk, $val, $key, $time, date("Y"), $groep, $meta);
    }
}
$sql .= substr($insert, 0, -1) . ";";
//loghookaction("\n$sql\n");
$result = $t->exeSQL($sql);

if ($result) {
    loghookaction("Formulier succesvol verwerkt\n");
    loghookaction($time);
} else {
    loghookaction("Formulier niet succesvol verwerkt\n");
    loghookaction($time);
    loghookaction($sql);
}

$sql2a = "UPDATE prze_sawemo pr, items i SET pr.meta = i.itemid
        WHERE kop = $kop AND pr.meta IS NULL AND pr.waardetype = i.field_name;";
$sql2b = "UPDATE prze_sawemo pr, items i SET pr.meta = i.itemid
        WHERE kop = $kop AND pr.kenmerk like i.verg;";

$result2b = $t->exeSQL($sql2a);
$result2a = $t->exeSQL($sql2b);
$result3 = $t->exeSQL("update prze_sawemo set waarde =3 where lcase(waarde) = 'niet mee eens, niet mee oneens';");
$result4 = $t->exeSQL("update prze_sawemo set waarde =5 where lcase(waarde) = 'helemaal mee eens';");
$result5 = $t->exeSQL("update prze_sawemo set waarde =1 where lcase(waarde) = 'helemaal mee oneens';");
$result6 = $t->exeSQL("update prze_sawemo set waarde =2 where lcase(waarde) = 'mee oneens';");
$result7 = $t->exeSQL("update prze_sawemo set waarde =4 where lcase(waarde) = 'mee eens';");
$result8 = $t->exeSQL("update prze_sawemo set waarde = null where lcase(waarde) = 'weet ik niet / nvt';");

if ($result2a) {
    loghookaction("items gekoppeld\n");
} else {
    loghookaction("items niet gekoppeld\n");
    loghookaction($time);
    loghookaction($sql2);
}

if ($result3) {
    loghookaction("waarden bijgewerkt\n");
} else {
    loghookaction("waarden niet bijgewerkt\n");
    loghookaction($time);
    loghookaction($sql2);
}
