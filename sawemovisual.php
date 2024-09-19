<?php

/*
sawemo visual
 */
//define('WP_DEBUG', false);
ini_set('display_errors', 'Off');

function ts($test)
{ // for debug/development only
    echo '<pre>';
    echo print_r($test, true);
    echo '</pre>';
}

class sawemo_visual
{
    public function __CONSTRUCT()
    {
        $this->stoppen = 0;
        $this->gebruiker = get_current_user_id();

        //$this->gebruiker = 863;
        //$this->gebruiker = 617;
        //$this->gebruiker = 3;

        $this->gemeenten = $this->haal_gemeenten();
        $this->data = $this->haal_data();
        $gem = $this->haal_gemeente_gebruiker();
        //ts($gem);
        $tara = 0;
        $nikita = 0;
        $okoteam = 0;

        if ($gem["id"] == 20 || $this->gebruiker == 863) {
            $tara = 1;
            $this->gemeente = 'neo';
            if ($_GET && key_exists('gemeente', $_GET) && !empty($_GET['gemeente'])) {
                $this->gemeente = $_GET['gemeente'];
            }
        }
        if ($gem["id"] == 23 || $this->gebruiker = 617) {
            $nikita = 1;
            $this->gemeente = 'hep';
            if ($_GET && key_exists('gemeente', $_GET) && !empty($_GET['gemeente'])) {
                $this->gemeente = $_GET['gemeente'];
            }
        }
        if ($gem["id"] == 35) {
            $okoteam = 1;
            $this->okoteam = 1;
            $this->gemeente = 'alles';

        }

        //ts($this->gemeente);

        if (!$okoteam && !$tara && !$nikita) {
            //ts('okee');
            $this->gemeente = $gem['naam'];
            $this->gemeenteId = $gem['id'];
            $pl = $this->haal_projectleiderdata($this->gemeenteId);
            //ts($pl); //exit;
            //ts($this->gebruiker);
            $tonen = false;
            foreach ($pl as $row) {
                if ($row["ID"] == $this->gebruiker) {
                    $tonen = true;
                }
            }
            if (!$tonen) {
                echo "<h4>Resultaten zijn nu alleen zichtbaar voor de projectleider van je kernteam en door haar/hem toegewezen deelnemers.</h4>";
                exit;
            }
        }

        $this->itemscores = array();
        $this->schaalscores = array();
        $this->regiokop = 'samenwerking in OKO-regio ' . $this->gemeente;
        $this->plugindir = plugin_dir_url(__FILE__);
        $this->adres = "https://" . $_SERVER['HTTP_HOST'];
        $this->ikonen = $this->maak_ikonen();

        //ts($this->ikonen);
        $this->items = $this->maak_items();
        $this->constructen = $this->haal_constructen();

        if ($okoteam) {
            $this->gemeente = 'alles';
        }
        //ts($this->gemeente);
        $itemscores = $this->maak_resultaten('items', $this->gemeente, $okoteam);

        if (!$itemscores) {
            $this->stoppen = 1;
            echo "<h4>Er hebben nog geen deelnemers uit je gemeente / regio meegedaan. </h4>";
            exit;
        }

        //ts($itemscores);
        //exit;

        //if ($itemscores < 5) {
        //    $this->stoppen = 1;
        //    echo "<h4>Er hebben nog geen deelnemers uit je gemeente / regio meegedaan. </h4>";
        //    exit;
        //}

        $schaalscores = $this->maak_resultaten('schaal', $this->gemeente, $this->okoteam);
        $rapportcijfers = $this->maak_resultaten('rapportcijfers', $this->gemeente, $this->okoteam);
        $overige_resultaten = $this->maak_overige_resultaten($this->gemeente, $this->okoteam);
        //ts($schaalscores);
        $alggem = $this->maak_algemeen_gemiddelde($schaalscores);
        $domeinen = $this->maak_resultaten('domeinen', $this->gemeente, $this->okoteam);
        $uren = $this->maak_resultaten('uren', $this->gemeente, $this->okoteam);
        //ts($domeinen);
        $datatussen = array_merge($alggem, $schaalscores);
        $datasamen = array_merge($datatussen, $itemscores);
        $this->uit[$this->gemeente] = $datasamen;
        $this->overig[$this->gemeente] = $overige_resultaten;
        $this->rapport[$this->gemeente] = $rapportcijfers;
        $this->domeinen[$this->gemeente] = $domeinen;
        $this->uren[$this->gemeente] = $uren;
    }

    public function cleantxt($str)
    {
        //$str=base64_decode($str);
        $str = trim($str);
        //        $str=addslashes($str);
        $str = utf8_encode($str);
        $str = $this->recode($str);

        return $str;
    }
    public function recode($str)
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
    public function haal_projectleiderdata($gid)
    {
        $FFdb = new FFDb();
        $bb = "select wp.* from
        (select * from oko_gemeenten where id = " . $gid . ") g
        inner join
        (select * from wp_fc_subscriber_pivot p) rest
        on g.fc_id = rest.object_id
        inner join
        (select * from wp_fc_subscriber_pivot p WHERE object_id = 2 or object_id=133) pl
        on rest.subscriber_id = pl.subscriber_id
        inner join wp_fc_subscribers sub on sub.id = pl.subscriber_id
        inner join
        (select * from wp_users ) wp
        on sub.user_id = wp.ID;";
        $aa = $FFdb->getArray($bb);
        //ts($bb);
        return $aa;
    }
    public function maak_prze()
    {
        $FFdb = new FFDb();

        $sql = "INSERT INTO prze_sawemo (kop, koptype, kenmerk, waarde, waardetype, peildatum, jaar, groep) VALUES ";

        $insert = "";
        foreach ($this->data as $row) {
            $groep = "";
            if ($row['field_name'] == 'gemeente') {
                $groep = $row['field_value'];
            }

            $kop = $row['submission_id'];

            $insert .= sprintf("(\"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\", \"%s\"),",
                $kop, 'deelnemer', $this->cleantxt($row['sub_field_name']), $this->cleantxt($row['field_value']), $this->cleantxt($row['field_name']), $time, date("Y"), $groep);
        }
        $sql .= substr($insert, 0, -1) . ";";
        $result = $FFdb->exeSQL("delete from prze_sawemo");
        $result = $FFdb->exeSQL($sql);
        $sql2 = "update prze_sawemo ps, (select groep, kop from prze_sawemo where waardetype = 'gemeente') ps2 set ps.groep = ps2.groep where ps.kop = ps2.kop;";
        $result = $FFdb->exeSQL($sql2);
        /*VANWEGE LELIJKE UTF OPSLAG:*/
        $sql3 = "update prze_sawemo pr, items i set pr.meta = i.itemid where i.item_lelijk = pr.kenmerk;";
        $result = $FFdb->exeSQL($sql3);

        $sql5 = "update prze_sawemo pr, items i set pr.meta = i.itemid where i.field_name = pr.waardetype and pr.meta is null;";
        $result = $FFdb->exeSQL($sql5);
    }

    public function haal_gemeente_gebruiker()
    {
        $FFdb = new FFDb();
        $zz = "select g.id, g.naam from oko_gemeenten g
                inner join wp_bp_groups b on b.id = g.bp_id
                inner join wp_bp_groups_members m on m.group_id = b.id
                inner join wp_users w on w.ID = m.user_id
                where w.ID = " . $this->gebruiker . " ORDER BY g.id;";
        $bb = $FFdb->getArray($zz);
        if ($bb) {
            foreach ($bb as $row) {
                if ($row["naam"] == 'OKO team') {
                    return $row;
                }
            }
            return $row;
        }
        //ts($zz);
        return false;
    }

    public function haal_gemeenten()
    {
        $FFdb = new FFDb();
        $aa = $FFdb->getArray("select distinct field_value from wp_fluentform_entry_details where field_name = 'gemeente' and form_id=24 and submission_id>268;");
        $aa = $FFdb->getArray("select distinct groep from prze_sawemo;");
        //ts($aa);
        $terug = array();
        foreach ($aa as $row) {
            $terug[] = $row["groep"];
        }
        sort($terug);
        //ts($terug);
        return $terug;
    }
    public function haal_data()
    {
        $FFdb = new FFDb();
        $aa = $FFdb->getArray("select * from wp_fluentform_entry_details where form_id=24 and submission_id>268;");
        return $aa;
    }
    public function markeer()
    {
        $aantalgems = 3;
        foreach ($this->tabeldata as $item => $gems) {
            $maks = max($gems);
            $mins = min($gems);
            foreach ($gems as $gem => $score) {
                if ($maks == $score) {
                    $this->tabeldata[$item]['H'] = $gem;
                }
                if ($mins == $score) {
                    $this->tabeldata[$item]['L'] = $gem;
                }
            }
        }
        //ts($this->tabeldata);
    }
    public function vervorm()
    {
        $verv = array();
        foreach ($this->uit as $gem => $x) {
            foreach ($x as $row) {
                $verv[$row['item']][$gem] = $row['score'];
            }
        }
        return $verv;
    }
    public function haal_items()
    {
        $it = new FFDb();
        $zql = "SELECT itemid, item FROM items ORDER BY itemid;";
        $itdata = $it->getArray($zql);
        $terug = array();
        //ts($zql);
        foreach ($itdata as $row) {
            $terug[$row["itemid"]] = $row["item"];
        }
        return $terug;
    }
    public function haal_constructen()
    {
        $it = new FFDb();
        $zql = "SELECT * FROM constructen ORDER BY id;";
        $itdata = $it->getArray($zql);
        $terug = array();
        //ts($zql);
        foreach ($itdata as $row) {
            $terug[$row["id"]]["item"] = $row["item"];
            $terug[$row["id"]]["vraag"] = $row["vraag"];
            $terug[$row["id"]]["pad"] = $row["pad"];
        }
        return $terug;
    }

    public function maak_taart_series()
    {
        //ts($this->gemeente);
        //ts($this->domeinen);
        $arrdat = array();

        foreach ($this->domeinen[$this->gemeente] as $row) {
            $arrdat[$row["waarde"]]++;
        }
        //ts($arrdat);
        $datstr = '';
        foreach ($arrdat as $key => $val) {
            $datstr .= "{name: '" . $key . "', y: " . $val . "},";
        }
        $datstr = substr($datstr, 0, -1);
        return $datstr;
    }

    public function maak_tabel()
    {
        $aantalgemeenten = count($this->gemeenten);
        $this->gemeente = strtolower($this->gemeente);

        $it = new FFDb();

        if ($this->gemeente == 'neo') {
            $gem = "(lcase(groep) = 'nunspeet' OR lcase(groep) = 'elburg' OR lcase(groep) = 'oldebroek')";
        } elseif ($this->gemeente == 'hep') {
            $gem = "(lcase(groep) = 'harderwijk' OR lcase(groep) = 'ermelo' OR lcase(groep) = 'putten')";
        } else {
            $gem = 'lcase(groep) = "' . $this->gemeente . '"';
        }

        $zql = "select distinct kop from prze_sawemo where meta<41;";
        $zql2 = "select distinct kop from prze_sawemo where meta<41 and " . $gem . ";";

        $data = $it->getArray($zql);
        $data2 = $it->getArray($zql2);
        //ts($zql2);

        if ($data) {
            $aantaldeelnemers = count($data);
        }
        if ($data2) {
            $aantaldeelnemersgemeente = count($data2);
        }
        //ts($aantaldeelnemers);
        if ($aantaldeelnemers < 1) {
            $this->stoppen = 1;
        } elseif ($aantaldeelnemers < 5) {
            $this->stoppen = 2;
        } elseif ($aantalgemeenten < 3) {
            $this->stoppen = 3;
        } else {
            $this->stoppen = 0;
        }

        //$this->stoppen = 3;
        $terug = "<table class = 'sawemotabel'>";
        $terug .= "<tr><td class = 'tabelkopje'>respons</td></tr>";
        $terug .= "<tr>";
        $terug .= "<tr><td>aantal deelnemende regio's: " . $aantalgemeenten . "</td></tr>";
        $terug .= "<tr><td>aantal deelnemers in alle regio's: " . $aantaldeelnemers . "</td></tr>";
        if (!$this->okoteam) {
            $terug .= "<tr><td>aantal deelnemers in " . $this->gemeente . ": " . $aantaldeelnemersgemeente . "</td></tr>";
        }
        $terug .= "</table>";

        if ($this->stoppen > 0) {
            return $terug;
        }
        //ts($this->stoppen);
        //ts($aantal);
        $this->items = $this->haal_items();
        //domeinen
        $terug .= "<table class = 'sawemotabel'>";
        $terug .= "<tr><td class = 'tabelkopje'>domeinen </td></tr>";
        $terug .= "<tr><td>De vraag werd gesteld: <i>In welk OKO-domein bent u of is uw organisatie actief of gaat u actief worden (meerdere antwoorden mogelijk)?</i></td></tr>";
        $domtaart = new Taart();
        $terug .= "</table>";
        $terug .= '<div id = "domtaart"></div>';
        //ts($this->domeinen[$this->gemeente]);
        //ts($this->gemeente);

        $taartseries = $this->maak_taart_series();
        $terug .= $domtaart->generate($taartseries);

        //rapportcijfers
        $bbb = $this->rapport[$this->gemeente];
        //ts($bbb);
        $raps = array();
        foreach ($bbb as $row) {
            $raps[$row["itemid"]]["label"] = $row["item"];
            $raps[$row["itemid"]]["gem"] = $row["score"];
            $raps[$row["itemid"]]["n"] = $row["n_sc"];
            $raps[$row["itemid"]]["bm_gem"] = $row["score_bm"];
            $raps[$row["itemid"]]["bm_gem"] = $row["n_bm"];
        }

        $terug .= "<table class = 'sawemotabel'>";
        $terug .= "<tr><td class = 'tabelkopje'>rapportcijfers voor samenwerking</td></tr>";
        foreach ($raps as $key => $val) {
            $terug .= "<tr><td>De vraag <i>" . $val["label"] . "</i> werd door <b>" . $val["n"] . "</b> respondenten gemiddeld beoordeeld met het cijfer <span class='rapportcijfer'>" . $val["gem"] . "</span>.</td></tr>";
        }
        //$terug .= $this->maak_overig_tabel_deel_openvragen($i);
        $terug .= "</table>";

        //open vragen
        $this->tabel = array();
        foreach ($this->overig[$this->gemeente] as $row) {
            $this->tabel[$row['meta']][] = $row['waarde'];
        }
        $terug .= "<table class = 'sawemotabel'>";
        $terug .= "<tr><td class = 'tabelkopje'>open vragen</td></tr>";

        for ($i = 41; $i < 43; $i++) {
            $terug .= $this->maak_overig_tabel_deel_openvragen($i);
        }
        $terug .= "</table>";

//uren
        $this->tabel = array();
        foreach ($this->overig[$this->gemeente] as $row) {
            $this->tabel[$row['meta']][] = $row['waarde'];
        }
        $terug .= "<table class = 'sawemotabel'>";
        $terug .= "<tr><td class = 'tabelkopje'>inzet</td></tr>";
        $terug .= $this->maak_uren_tabel();
        $terug .= "</table>";

        //items en schalen
        if ($this->okoteam) {
            $terug .= "<table class = 'sawemotabel'>";
            $terug .= "<tr><td class = 'tabelkopje'>alle aspecten en items</td></tr>";
            $terug .= "<tr><td class = 'tabelkopje_li'><img src = 'https://kansrijkeomgeving.nl/wp-content/plugins/oko-sawemo/images/" . $this->constructen[0]['pad'] . ".png' width = '80px'>
        &nbsp;&nbsp;Overall beeld van de samenwerking</td><td class = 'tabelkopje_ov'>alle deelnemers</td></tr>";
            foreach ($this->uit[$this->gemeente] as $row) {
                if ($row["kop"] == 0) {
                    $terug .= "<tr><td class = 'tabelitem'>" . $row["item"] . "</td><td class = 'tabelgetal'>" . $row["score"] . "</td></tr>";

                }
            }

            for ($i = 1; $i < 10; $i++) {
                $terug .= "<tr><td class = 'tabelkopje_li'>
            <img src = 'https://kansrijkeomgeving.nl/wp-content/plugins/oko-sawemo/images/" . $this->constructen[$i]['pad'] . ".png' width = '70px'>
            &nbsp;&nbsp;" . $this->constructen[$i]["item"] . "</td><td class = 'tabelkopje_lice' colspan='2'><i>" . $this->constructen[$i]["vraag"] . "</i></td></tr>
            <tr><td></td><td class = 'tabelkopje_ov'>alle deelnemers</td></tr>";

                foreach ($this->uit[$this->gemeente] as $row) {
                    if ($row["kop"] == $i) {
                        $terug .= "<tr><td class = 'tabelitem'>" . $row["item"] . "</td><td class = 'tabelgetal'>" . $row["score"] . "</td></tr>";
                    }
                }
            }

            $terug .= '<tr><td colspan = "2" class = "tabelkopje_li"><a href=""><img onmouseover="" onclick="window.print(); return false; " width = "30" src="https://kansrijkeomgeving.nl/wp-content/plugins/oko-sawemo/images/print.png" /></a></td></tr>';
            $terug .= "</table>";
        } else {
            $terug .= "<table class = 'sawemotabel'>";
            $terug .= "<tr><td class = 'tabelkopje'>alle aspecten en items</td></tr>";
            $terug .= "<tr><td class = 'tabelkopje_li'><img src = 'https://kansrijkeomgeving.nl/wp-content/plugins/oko-sawemo/images/" . $this->constructen[0]['pad'] . ".png' width = '80px'>
        &nbsp;&nbsp;Overall beeld van de samenwerking</td><td class = 'tabelkopje_ov'>" . $this->gemeente . "</td><td class = 'tabelkopje_ov'>anderen</tr>";
            foreach ($this->uit[$this->gemeente] as $row) {
                if ($row["kop"] == 0) {
                    $terug .= "<tr><td class = 'tabelitem'>" . $row["item"] . "</td><td class = 'tabelgetal'>" . $row["score"] . "</td><td class = 'tabelgetal'>" . $row["score_bm"] . "</td></tr>";

                }
            }

            for ($i = 1; $i < 10; $i++) {
                $terug .= "<tr><td class = 'tabelkopje_li'>
            <img src = 'https://kansrijkeomgeving.nl/wp-content/plugins/oko-sawemo/images/" . $this->constructen[$i]['pad'] . ".png' width = '70px'>
            &nbsp;&nbsp;" . $this->constructen[$i]["item"] . "</td><td class = 'tabelkopje_lice' colspan='2'><i>" . $this->constructen[$i]["vraag"] . "</i></td></tr>
            <tr><td></td><td class = 'tabelkopje_ov'>" . $this->gemeente . "</td><td class = 'tabelkopje_ov'>anderen</tr>";

                foreach ($this->uit[$this->gemeente] as $row) {
                    if ($row["kop"] == $i) {
                        $terug .= "<tr><td class = 'tabelitem'>" . $row["item"] . "</td><td class = 'tabelgetal'>" . $row["score"] . "</td><td class = 'tabelgetal'>" . $row["score_bm"] . "</td></tr>";
                    }
                }
            }

            $terug .= '<tr><td colspan = "3" class = "tabelkopje_li"><a href=""><img onmouseover="" onclick="window.print(); return false; " width = "30" src="https://kansrijkeomgeving.nl/wp-content/plugins/oko-sawemo/images/print.png" /></a></td></tr>';
            $terug .= "</table>";
        }
        return $terug;

    }
    public function maak_uren_tabel()
    {
        //ts($this->uren);
        //return;
        //ts($this->tabel[$nummer]);
        if ($this->uren) {
            $vraag = "De vraag werd gesteld: <i>Hoeveel uur per week heeft u om met OKO bezig te zijn?</i>";
            $terug = "<tr><td colspan = '2' class = 'tabelkopje_kl'>" . $vraag . "</td></tr>";

            foreach ($this->uren as $kop => $sco) {
                foreach ($sco as $key => $val) {
                    $terug .= "<tr><td  class = 'tabelitem'>respondent " . $key . ":</td><td>" . $val["waarde"] . " uur.</td><tr>";
                }
            }

            return $terug;
        }
    }
    public function maak_overig_tabel_deel_openvragen($nummer)
    {
        //ts($nummer);
        //ts($this->tabel[$nummer]);
        if ($this->tabel[$nummer]) {

            foreach ($this->tabel[$nummer] as $kop => $sco) {

                $terug = "<td class = 'tabelkopje_kl'>" . $this->items[$nummer] . "</td>";
                foreach ($this->tabel[$nummer] as $waarde) {
                    if ($waarde) {
                        $terug .= "<tr><td  class = 'tabelitem'>" . $waarde . "</td><tr>";
                    }
                }
            }

            return $terug;
        }
    }
    public function pas_bms_aan()
    {

        //$aantal = count($this->itemscores);
        for ($i = 0; $i < 34; $i++) {

            if (!$this->itemscores[$i]['score_bm']) {
                $this->itemscores[$i]['score_bm'] = 3;
            }
        }
        //$aantal = count($this->schaalscores);
        for ($i = 0; $i < 8; $i++) {

            if (!$this->schaalscores[$i]['score_bm']) {
                $this->schaalscores[$i]['score_bm'] = 3;
            }
        }
    }
    public function combineer()
    {
        //ts($this->schaalscores);
        $data = array();

        $tel = 0;
        $data[$tel]['item'] = 'algemeen gemiddelde'; //substr($row['item'], 0, strpos($row['item'], '(gemiddelde)'));
        $data[$tel]['kop'] = 0;
        $data[$tel]['score'] = round($this->alggem['score'], 1);
        $data[$tel]['score_bm'] = round($this->alggem['score_bm'], 1);
        $data[$tel]['pad'] = '';
        //ts($data);
        $tel = 1;
        foreach ($this->schaalscores as $row) {

            $data[$tel]['item'] = $row['item']; //substr($row['item'], 0, strpos($row['item'], '(gemiddelde)'));
            $data[$tel]['kop'] = 0;
            $data[$tel]['score'] = $row['score'];
            $data[$tel]['score_bm'] = $row['bm'];
            $data[$tel]['pad'] = $row['pad'];
            $tel++;
        }
        //ts($data);
        //toevoegen gemiddelden aan constructpags
        foreach ($this->schaalscores as $row) {

            $data[$tel]['item'] = 'gemiddelde'; //substr($row['item'], 0, strpos($row['item'], '(gemiddelde)'));
            $data[$tel]['kop'] = $row['construct'];
            $data[$tel]['score'] = $row['score'];
            $data[$tel]['score_bm'] = $row['bm'];
            $data[$tel]['pad'] = $row['pad'];
            $tel++;
        }
        //ts($data);

        return $data;
    }

    public function get_interface($kenmerk)
    {

        $output = $this->get_style();

        $this->rapport = $this->maak_tabel();

        if ($this->stoppen == 1) {
            echo "<h4>Er hebben in 2024 nog geen deelnemers uit je gemeente / regio meegedaan. </h4>";
            echo $output . $this->rapport;
            exit;
        } elseif ($this->stoppen == 2) {
            echo "<h4>Nog even geduld. Er hebben in 2024 nog niet voldoende deelnemers uit je gemeente / regio meegedaan om de gegevens onherleidbaar te laten zien (5). </h4> ";
            echo $output . $this->rapport;
            exit;
        } elseif ($this->stoppen == 3) {
            echo "<h4>Nog even geduld. Er hebben in 2024 nog niet voldoende regio's / gemeenten meegedaan om de gegevens onherleidbaar tot gemeente te laten zien (3).</h4>";
            echo $output . $this->rapport;
            exit;
        }
        //ts($this->doorgaan);
        $output .= "<script src='https://code.highcharts.com/highcharts.js'></script>";
        if (!$this->okoteam) {
            $output .= "<div><h1>" . $this->regiokop . "</h1></div>";
        } else {
            $output .= "<div><h1>alle inzendingen</h1></div>";
        }

        $output .= "<div id='kopikonen'></div>";
        $output .= "<div id='koptitels'></div>";
        //$output .= "<div id='printknop'></div>";
        $output .= "<div id='koptitelvragen'></div>";
        //$output .= "        <div id='items'></div>";
        $output .= "<div class = 'section'>";
        $output .= "    <div id = 'links'>";
        $output .= "         <div class = 'kol' id='barcharts' onresize='pasAan()'></div>";
        $output .= "    </div>";
        $output .= "    <div class = 'kol1' id='rechts'></div>";
        $output .= "    <div id='rapport'>" . $this->rapport . "</div>";
        $output .= "</div>";
        //*/
        $output .= '<footer>';
        //ts(json_encode($this->ikonen));
        if ($this->okoteam) {
            $output .= '    <script type="text/JavaScript">var OKOteam = 1</script>';
        } else {
            $output .= '    <script type="text/JavaScript">var OKOteam = 0</script>';
        }

        $output .= '    <script type="text/JavaScript">var ikonen =' . json_encode($this->ikonen) . '</script>';
        $output .= '    <script type="text/JavaScript">var databegin = ' . json_encode($this->uit[$this->gemeente]) . '</script>';
        $output .= '    <script type="text/JavaScript">var gemeente = "' . $this->gemeente . '"</script>';
        $output .= $this->get_biebs();
        $output .= '</footer>';
        //}
        echo $output;
    }
    public function get_style()
    {
        wp_enqueue_style('sawemo', $this->plugindir . 'css/hiersawemo.css');
        wp_enqueue_style('sawemoprint', $this->plugindir . 'css/print.css', array(), '20221912', 'print');
    }
    public function get_biebs()
    {
        $output = "<script type='text/javascript' src='" . $this->plugindir . "js/d3.v3.min.js'></script>";
        $output .= "<script type='text/javascript' src='" . $this->plugindir . "js/d3.tip.js'></script>";
        $output .= "<script type='text/javascript' src='" . $this->plugindir . "js/sawemo.js'></script>";

        return $output;
    }
    public function MaakDataString($gebr)
    {
        $aantalfasen = count($this->knoptekst);
        //ts($this->knoptekst);
        $output = 'var datastring = [';
        //$key=0;
        for ($key = 0; $key < $aantalfasen; $key++) {
            $output .= '{"fase":       "' . $key . '",';
            $output .= '"doeltekst":   "' . $this->fasenaam[$key] . '",';
            $output .= '"knoptekst":   "' . $this->knoptekst[$key] . '",';
            $output .= '"aantalitems":  ' . $this->aantalitems[$key] . ',';
            $output .= '"startitem":    ' . $this->startitem[$key] . '},';
        }

        $output = substr($output, 0, -1) . "];";
        //ts($output);
        return $output;

    }
    public function maak_items()
    {

        $it = new FFDb();
        $zql = "select * from items order by id";
        //ts($zql);
        $data = $it->getArray($zql);
        return $data;
    }
    public function maak_overige_resultaten($gemeente, $alles)
    {
        $it = new FFDb();

        $gemeente = strtolower($gemeente);

        $it = new FFDb();
        //ts('soort: ' . $soort);
        //ts('gemeente: ' . $gemeente);
        //ts('alles: ' . $alles);

        if ($gemeente == 'neo') {
            $wgemeente = "(lcase(groep) = 'nunspeet' OR lcase(groep) = 'elburg' OR lcase(groep) = 'oldebroek')";
        } elseif ($gemeente == 'hep') {
            $wgemeente = "(lcase(groep) = 'harderwijk' OR lcase(groep) = 'ermelo' OR lcase(groep) = 'putten')";
        } else {
            $wgemeente = "lcase(groep) = '" . $gemeente . "'";
        }
        //ts($gemeente);

        if (!$alles) {
            $zql = "SELECT meta, groep, waarde FROM prze_sawemo WHERE meta>40 AND meta <43 AND " . $wgemeente . ";";
        } else {
            $zql = "SELECT meta, groep, waarde FROM prze_sawemo WHERE meta>40  AND meta <43;";
        }
        //ts($zql);
        $data = $it->getArray($zql);
        return $data;
    }

    public function maak_resultaten($soort, $gemeente, $alles)
    {
        $gemeente = strtolower($gemeente);

        $it = new FFDb();
        //ts('soort: ' . $soort);
        //ts('gemeente: ' . $gemeente);
        //ts('alles: ' . $alles);

        if ($gemeente == 'neo') {
            $wgemeente = "(lcase(groep) = 'nunspeet' OR lcase(groep) = 'elburg' OR lcase(groep) = 'oldebroek')";
            $ngemeente = "(lcase(groep) <> 'nunspeet' AND lcase(groep) <> 'elburg' AND lcase(groep) <> 'oldebroek')";
            $sgemeente = "(lcase(p.groep) = 'nunspeet' OR lcase(p.groep) = 'elburg' OR lcase(p.groep) = 'oldebroek')";
            $sngemeente = "(lcase(p.groep) <> 'nunspeet' AND lcase(p.groep) <> 'elburg' AND lcase(p.groep) <>'oldebroek')";

        } elseif ($gemeente == 'hep') {
            $wgemeente = "(lcase(groep) = 'harderwijk' OR lcase(groep) = 'ermelo' OR lcase(groep) = 'putten')";
            $ngemeente = "(lcase(groep) <> 'harderwijk' AND lcase(groep) <> 'ermelo' AND lcase(groep) <>'putten')";
            $sgemeente = "(lcase(p.groep) = 'harderwijk' OR lcase(p.groep) = 'ermelo' OR lcase(p.groep) = 'putten')";
            $sngemeente = "(lcase(p.groep) <> 'harderwijk' AND lcase(p.groep) <> 'ermelo' AND lcase(p.groep) <>'putten')";
        } else {
            $wgemeente = "lcase(groep) = '" . $gemeente . "'";
            $ngemeente = "lcase(groep) <> '" . $gemeente . "'";
            $sgemeente = "lcase(p.groep) <> '" . $gemeente . "'";
            $sngemeente = "lcase(p.groep) <> '" . $gemeente . "'";
        }
        //ts($gemeente);
        ///ts($ngemeente);
        //ts($sgemeente);
        //ts($sngemeente);

        if (!$alles) {
            switch ($soort) {
                case 'domeinen':
                    $zql = "select waarde from prze_sawemo where " . $wgemeente . " and meta = 44 order by waarde;";
                    //echo '1';
                    break;
                case 'uren':
                    $zql = "select waarde from prze_sawemo where " . $wgemeente . " and meta = 45 order by waarde;";
                    //echo '1';
                    break;
                case 'rapportcijfers':
                    $zql = "select its.itemid, its.construct as kop, its.item, round(gems.gemiddelde,1) as score, gems.aantal as n_sc, round(bm.gemiddelde,1) as score_bm, bm.aantal as n_bm from
                (select meta, avg(waarde) as gemiddelde, count(waarde) as aantal from prze_sawemo where " . $wgemeente . " and meta>48 and meta<53 group by meta) gems
                inner join
                (select meta, avg(waarde) as gemiddelde, count(waarde) as aantal from prze_sawemo where  " . $ngemeente . " and meta>48 and meta<53 group by meta) bm
                on gems.meta = bm.meta
                inner join items its on its.itemid = gems.meta
                order by its.itemid";
                    //ts($zql);
                    break;
                case 'items':
                    $zql = "select its.itemid, its.construct as kop, its.item, round(gems.gemiddelde,1) as score, round(bm.gemiddelde,1) as score_bm from
                    (select meta, avg(waarde) as gemiddelde, count(waarde) as aantal from prze_sawemo where " . $wgemeente . " and meta<41 group by meta) gems
                    inner join
                    (select meta, avg(waarde) as gemiddelde, count(waarde) as aantal from prze_sawemo where " . $ngemeente . " and meta<41 group by meta) bm
                    on gems.meta = bm.meta
                    inner join items its on its.itemid = gems.meta
                    order by its.itemid";
                    //echo '3';
                    //ts($zql);
                    break;
                case 'schaal':
                    $zql = "select  xx.id as kop, xx.item as construct, xx.score, xx.score_bm, xx.item, xx.vraag, xx.toelichting from
                (select * from
                (select a.construct, a.score as score, b.score as score_bm from
                (select gem.construct, round(avg(IF(gem.richting = 'neg', 6-waarde, waarde)),1) as score  from
                (select * from prze_sawemo p inner join items i on p.meta = i.itemid where p.meta<41 and " . $sgemeente . ") gem
                group by gem.construct) a
                inner join
                (select ngem.construct, round(avg(IF(ngem.richting = 'neg', 6-waarde, waarde)),1) as score  from
                (select * from prze_sawemo p inner join items i on p.meta = i.itemid where p.meta<41 and  " . $sngemeente . ") ngem
                group by ngem.construct) b
                on a.construct = b.construct) zz
                inner join constructen con on con.ID = zz.construct) xx";
                    //echo '4';
                    break;
            }
        } else {
            switch ($soort) {
                case 'domeinen':
                    $zql = "select waarde from prze_sawemo where meta = 44 order by waarde;";
                    //ts($zql);
                    break;
                case 'uren':
                    $zql = "select waarde from prze_sawemo where meta = 44 order by waarde;";
                    //ts($zql);
                    break;
                case 'rapportcijfers':
                    $zql = "select its.itemid, its.construct as kop, its.item, round(gems.gemiddelde,1) as score, gems.aantal as n_sc from
                (select meta, avg(waarde) as gemiddelde, count(waarde) as aantal from prze_sawemo where meta>48 and meta<53 group by meta) gems
                inner join items its on its.itemid = gems.meta
                order by its.itemid";
                    break;
                case 'items':
                    $zql = "select its.itemid, its.construct as kop, its.item, round(gems.gemiddelde,1) as score from
                    (select meta, avg(waarde) as gemiddelde, count(waarde) as aantal from prze_sawemo where meta<41 group by meta) gems
                    inner join items its on its.itemid = gems.meta
                    order by its.itemid";
                    //ts($zql);
                    break;
                case 'schaal':
                    $zql = "select  xx.id as kop, xx.item as construct, xx.score, xx.item, xx.vraag, xx.toelichting from
                    (select * from
                               (select gem.construct, round(avg(IF(gem.richting = 'neg', 6-waarde, waarde)),1) as score  from
                                    (select * from prze_sawemo p inner join items i on p.meta = i.itemid where p.meta<41) gem group by gem.construct) a
                inner join constructen con on con.ID = a.construct) xx";
                    break;
            }
        }
        //ts($zql);

        $data = $it->getArray($zql);

        return $data;
    }

    public function maak_algemeen_gemiddelde($dat)
    {
        $tot1 = 0;
        $tot2 = 0;
        $data = array();
        $data[0]['kop'] = 0;
        $data[0]['construct'] = 'algemeen gemiddelde';
        $data[0]['item'] = 'algemeen gemiddelde';

        //ts($dat);
        for ($i = 1; $i < (count($dat) + 1); $i++) {
            $data[$i]['kop'] = 0;
            $data[$i]['construct'] = $dat[$i - 1]["construct"];
            $data[$i]['score'] = $dat[$i - 1]["score"];
            if (!$this->okoteam) {
                $data[$i]['score_bm'] = $dat[$i - 1]["score_bm"];
            }
            $data[$i]['item'] = $dat[$i - 1]["item"];
            $tot1 += $dat[$i - 1]["score"];
            if (!$this->okoteam) {
                $tot2 += $dat[$i - 1]["score_bm"];
            }
        }

        //$gems = array_column($this->schaalscores, 'score');
        //$bm = array_column($this->schaalscores, 'bm');
        //exit;
        $alg_gem = $tot1 / count($dat);
        if (!$this->okoteam) {
            $bm_gem = $tot2 / count($dat);
        }
        //ts($this->schaalscores);
        $data[0]['score'] = round($alg_gem, 1);
        if (!$this->okoteam) {
            $data[0]['score_bm'] = round($bm_gem, 1);
        }
        //ts($data);

        //sort($data);
        //ts($data);
        return $data;
    }

    public function maak_ikonen()
    {
        //ts($this->teksten);
        //$data=array();
        $it = new FFDb();
        $zql = "select * from constructen order by id;";
        //ts($zql);
        $data = $it->getArray($zql);
        if ($data) {
            $aantal = count($data);
        } else {
            $aantal = 0;
        }
        ;

        $zql = "select * from teksten";

        $tekst = $it->getArray($zql);

        //ts($tekst);

        $txt = array();
        foreach ($tekst as $row) {
            if ($row["wanneer"] == 'altijd') {
                $txt[$row["construct"]]["alg"] = str_replace("###", $this->gemeente, $row["tekst"]);
            } elseif ($row["wanneer"] == '<4') {
                $txt[$row["construct"]]["min"] = str_replace("###", $this->gemeente, $row["tekst"]);
            } elseif ($row["wanneer"] == '>4') {
                $txt[$row["construct"]]["plus"] = str_replace("###", $this->gemeente, $row["tekst"]);
            } elseif ($row["wanneer"] == '<4org') {
                $txt[$row["construct"]]["min"] = str_replace("###", $this->gemeente, $row["tekst"]);
            } elseif ($row["wanneer"] == '<4gem') {
                $txt[$row["construct"]]["min"] = "(voor gemeenten:)" . $row["construct"]["min"] . "<br>(voor organisaties:)" . str_replace("###", $this->gemeente, $row["tekst"]);
            }
        }
        //ts($txt);
        //ts($this->schaalscores);
        $terug = array();
        for ($i = 0; $i < 10; $i++) {
            $terug[$i] = $txt[$i]["alg"];
            //ts($terug[$teller]);
            if ($row["score"] < 4) {
                $terug[$i] = $terug[$i] . "</br></br><p style='color:#011763;'>" . $txt[$i]["min"] . "</p>";
            } else {
                $terug[$i] = $terug[$i] . "</br></br><p style='color:#3db28f;'>" . $txt[$i]["plus"] . "</p>";
            }
        }

        //ts($terug);
        //ts('hola');

        for ($i = 0; $i < 10; $i++) {
            $data[$i]['x'] = 30 + 80 * $data[$i]['id'];
            //$data[$i]['id'] = $data[$i]['id'] + 1;
            $data[$i]['y'] = 0;
            $data[$i]['pad'] = $this->plugindir . 'images/' . $data[$i]['pad'] . '.png';
            $data[$i]['toelichting'] = $terug[$i];
        }

        return $data;
    }

}
