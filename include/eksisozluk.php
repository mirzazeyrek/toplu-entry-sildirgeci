<?php
/**
 * Created by PhpStorm.
 * User: mirzazeyrek
 * Date: 12.04.15
 * Time: 01:06
 */

require_once("curl.php");

class Eksisozluk extends Curl {

    private $username;
    private $password;
    private $curl;
    private $nick;
    private $son_entryleri = array();


    function __construct($cookie = "eksisozluk") {
        $this->curl = new curl($cookie);

        $header_array[0] = 'Host: eksisozluk.com';
        $header_array[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
        $header_array[] = 'Accept-Language: tr-TR,tr;q=0.8,en-US;q=0.5,en;q=0.3';
        $header_array[] = 'Connection: keep-alive';
        $header_array[] = 'Content-Type: text/html; charset=utf-8';

        $this->curl->setHeaderArray($header_array);
    }

    /**
     * @param $username yüzbaşı malum (?!) ekşi sözlük kullanıcı adımız
     */
    public function setUsername($string) {
        if(is_string($string)) {
            $this->username = $string;
        } else {
            echo "birami döktün ve sevgilim birazdan gelecek";
            die;
        }
    }


    /**
     * @return mixed username i napmıştık ?
     */
    public function getUsername() {
        return $this->username;
    }

    public function setPassword($string) {
        if(is_string($string)) {
            $this->password = $string;
        } else {
            echo "sen çok yanlış geldin. ";
            die;
        }
    }

    public function getPassword($string) {
        return $this->password;
    }

    /**
     * @return array
     */
    public function getSonEntryleri()
    {
        return $this->son_entryleri;
    }

    /**
     * @param array $son_entryleri
     */
    public function setSonEntryleri($son_entryleri)
    {
        foreach($son_entryleri as $key=>$entry) {

            $entry_id = str_replace("/entry/", "", $son_entryleri[$key]["href"]);
            if(is_numeric($entry_id))
            $son_entryleri[$key]["id"] = $entry_id;

         }

        $this->son_entryleri = $son_entryleri;
    }

    /**
     * yazarın nickname'ini ayarlar.
     */
    public function setNick()
    {
        $this->curl->setLinks($this->curl->getOutputHtml());
        foreach($this->curl->getLinks() as $link) {
            if($link['text'] == "ben") {
                $nick = $link['title'];
            }
        }
        if(is_string($nick)) {
            $this->nick = $nick;
        } else {
            echo "\n nick ayarlanamadı. işlem sonlandrılıyor. \n";
            die;
        }
        return $this->getNick();
    }

    /**
     * @return mixed
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * @param $response html çıktısını string olarak alıp login olunup olunmadığını kontrol eder
     * @return bool
     */
    public function islogged($response) {

        preg_match('/<a href="\/terk">terk<\/a>/i',$response,$giris);


        if($giris) {
            echo "\n login başarılı \n";
        return true;
        } else {
        return false;
        }

    }

    public function login($username, $password) {

        $this->setUsername($username);
        $this->setPassword($password);


        $this->curl->setOutputHtml($this->curl->get("https://eksisozluk.com"));
        $is_logged_bool = $this->islogged($this->curl->getOutputHtml());

        sleep(1);

        if($is_logged_bool) {
            $this->setNick();
            echo "\n zaten giriş yapılmış \n";
            return true;

        } else {

            $response = $this->curl->get("https://eksisozluk.com/giris?returnUrl=https%3A%2F%2Feksisozluk.com%2F");
            sleep(1);

            // error reporting 0 yapıyoruz çünkü domdocument saniyede 5.000 warning fırlatıyor.
            error_reporting(0);
            $dom = new DOMDocument();
            $dom->loadHTML($response);
            $xp = new DOMXpath($dom);
            $nodes = $xp->query('//input[@name="__RequestVerificationToken"]');
            $node = $nodes->item(0);
            $token = $node->getAttribute('value');
            error_reporting(1);

            $post_array['__RequestVerificationToken'] = $token;
            $post_array['UserName'] = $this->username;
            $post_array['Password'] = $this->password;
            // RememberMe'yı dangalaklar iki tane postluyor :)
            $post_array['RememberMe'] = "true";
            $this->curl->setPost(http_build_query($post_array));

            // print_r($this->curl->getPostArray());
            $this->curl->setOutputHtml($this->curl->post("https://eksisozluk.com/giris"));
            $is_logged_bool = $this->islogged($this->curl->getOutputHtml());

            if($is_logged_bool) {
                $this->setNick();
                return true;
            }

        }

        echo "\n login başarısız kullanıcı adı ve şifrenizi kontrol ediniz \n";
        die;


    }


    public function ben() {
        sleep(rand(0,1));
        $url = "https://eksisozluk.com/biri/".$this->nick;
        $this->curl->setOutputHtml($this->curl->get($url));
        $this->curl->setLinks($this->curl->getOutputHtml());
        return $this->curl->getOutputHtml();

    }

    public function son_entryleri(){
        sleep(1);

        $header_array[0] = 'Host: eksisozluk.com';
        $header_array[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
        $header_array[] = 'Accept-Language: tr-TR,tr;q=0.8,en-US;q=0.5,en;q=0.3';
        $header_array[] = 'Connection: keep-alive';
        $header_array[] = 'Content-Type: text/html; charset=utf-8';
        $this->curl->setHeaderArray($header_array);

        $url = "https://eksisozluk.com/basliklar/istatistik/".$this->nick."/son-entryleri";
        $this->curl->setOutputHtml(mb_convert_encoding($this->curl->get($url), "UTF-8", "auto"));
        $this->curl->setLinks($this->curl->getOutputHtml(),"xpath","/html/body/div[1]/div[2]/div/section/ul[@class='topic-list']");
        $this->setSonEntryleri($this->curl->getLinks());
        return $this->curl->getOutputHtml();
    }

    public function sil($entry_id) {

        if(!is_numeric($entry_id)) {
            echo "\n sıçızladı. hatalı entry numarası : ' $entry_id ' \n";
            die;
            return false;
        }

      //  $url = "https://eksisozluk.com/entry/".$entry_id;
      //  $this->curl->get($url);



        $post_array['id'] = $entry_id;
        $this->curl->setPost(http_build_query($post_array));

        $header_array = array();
        $header_array[] = 'Host: eksisozluk.com';
        $header_array[] = 'Accept-Language: tr-TR,tr;q=0.8,en-US;q=0.5,en;q=0.3';
        $header_array[] = 'Connection: keep-alive';
        $header_array[] = 'Cache-Control: no-cache';
        $header_array[] = 'Pragma: no-cache';
        $header_array[] = 'Referer: https://eksisozluk.com/entry/'.$entry_id;
        $header_array[] = 'X-Requested-With: XMLHttpRequest';

        $this->curl->setHeaderArray($header_array);

        $this->curl->post('https://eksisozluk.com/entry/sil');
        echo "\n $entry_id silindi gibi \n";

        return true;
    }


}