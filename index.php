<?php
/**
 * Created by PhpStorm.
 * User: mirzazeyrek
 * Date: 11.04.15
 * Time: 21:48
 */

require("include/require.php");

parse_str(implode('&', array_slice($argv, 1)), $_GET);

if(!$_GET['sifre'] or !$_GET['kullanici']) {
    echo " kullanıcı adı ve şifre girmeyi unuttunuz. örnek giriş: \n 'php index.php sifre=1234 kullanici=ssg' \n";
    die;
}

/*
$curl = new Curl();

echo $curl->get("www.eksisozluk.com");
*/

$eksisozluk = new Eksisozluk();



$eksisozluk->login($_GET['kullanici'],$_GET['sifre']);
$eksisozluk->ben();
$eksisozluk->son_entryleri();
while(count($eksisozluk->getSonEntryleri())>0) {
    foreach($eksisozluk->getSonEntryleri() as $key=>$entry) {
        $eksisozluk->sil($entry["href"]);
        echo "\n bir dürüm yeme süresi* kadar bekletiyorum. ";
        echo "\n *15 saniyeye tekabül etmektedir. \n";
        sleep(15);
        // dürümsel sebeplerle bu beklemeyi yapmak zorundayız maalesef!!!

    }
    $eksisozluk->son_entryleri();
}

echo "\n veda zamanı gelirken teletabilerin :( \n";
echo "\n ebesini sikeyim dürümcü pezevenklerin :) \n"; 
