<?php

/**
 * OKO stappenanimatie voor Wordpress
 * @package      oko-sawemo
 * @link         https://www.hansei.nl/plugins/oko-anima/
 * @author       Erik Jan de Wilde <ej@hansei.nl>
 * @copyright    2021 Erik Jan de Wilde
 * @license      GPL v2 or later
 * Plugin Name:  OKO samenwerkingsmonitor visualisatie
 * Description:  Visuals voor OKO: samenwerkingsmonitor
 * Version:      1.5
 * Plugin URI:   https://www.hansei.nl/plugins
 * Author:       Erik Jan de Wilde, (c) 2022, HanSei
 * Text Domain:  oko-sawemo
 * Domain Path:  /languages/
 * Network:      true
 * Requires PHP: 5.3
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * version 1.3: eerste versie in platform
 * version 1.4: toevoegen taart + rapportcijfer
 * version 1.5: toevoegen weergave voor OKO team en beperken tot projectleider
 */

// first make sure this file is called as part of WP
//ini_set('display_errors', 'On');

defined('ABSPATH') or die('Hej då');

$plugin_root = substr(plugin_dir_path(__FILE__), 0, -5) . "/";

function monitor_start($atts)
{
    include_once("interfaceDB.php");
    include_once("sawemovisual.php");
    include_once("taart.php");
    //echo 'hallo';
    $a = shortcode_atts(array('kenmerk' => 'something'), $atts);
    $kenmerk = $a['kenmerk'];
    //$hoe=new InterfaceDB();
    $wat = new sawemo_visual();
    $ta = $wat->get_interface($kenmerk);
}

function sawemo_register_shortcode()
{
    add_shortcode('sawemo', 'monitor_start');
}

add_action('init', 'sawemo_register_shortcode');