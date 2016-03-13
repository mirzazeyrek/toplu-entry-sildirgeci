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

        sleep(2);

        if($is_logged_bool) {
            $this->setNick();
            echo "\n zaten giriş yapılmış \n";
            return true;

        } else {
            echo "login olunuyor";
            $response = $this->curl->get("https://eksisozluk.com/giris?returnUrl=https%3A%2F%2Feksisozluk.com%2F");
            sleep(2);

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

            $header_array = array();
            $header_array[] = 'Content-Length: '.strlen(http_build_query($post_array));
            $this->curl->setHeaderArray($header_array);

            $this->curl->setOutputHtml($this->curl->post("https://eksisozluk.com/giris",true));
            $is_logged_bool = $this->islogged($this->curl->getOutputHtml());

            if($is_logged_bool) {
                $this->setNick();
                return true;
            }

        }

        echo "\n login başarısız kullanıcı adı ve şifrenizi kontrol ediniz \n";
        die;
        return false;

    }


    public function ben() {
        sleep(rand(0,1));
        $url = "https://eksisozluk.com/biri/".$this->nick;
        $this->curl->setOutputHtml($this->curl->get($url));
        $this->curl->setLinks($this->curl->getOutputHtml());
        return $this->curl->getOutputHtml();

    }

    public function son_entryleri(){
        sleep(rand(0,1));
        $url = "https://eksisozluk.com/basliklar/istatistik/".$this->nick."/son-entryleri";
        $get_content = $this->curl->get($url);
        $this->curl->setOutputHtml($get_content);
        $this->curl->setLinks($this->curl->getOutputHtml(),"xpath",'//*[@id="content-body"]/ul');
        $this->setSonEntryleri($this->curl->getLinks());
        return $this->curl->getOutputHtml();
    }

    public function sil($entry_id) {
        $confirmation = false;
        while($confirmation == false) {
            $entry_id = str_replace("/entry/", "", $entry_id);
            if (!is_numeric($entry_id)) {
                echo "\n hatalı entry numarası $entry_id \n";
                return false;
            }

            $url = "https://eksisozluk.com/entry/" . $entry_id;
            $this->curl->get($url);

            sleep(rand(0, 1));

            $post_array['id'] = $entry_id;
            $this->curl->setPost(http_build_query($post_array));

            $header_array = array();
            $header_array[] = 'Host: eksisozluk.com';
            $header_array[] = 'Accept-Language: tr-TR,tr;q=0.8,en-US;q=0.5,en;q=0.3';
            $header_array[] = 'Connection: keep-alive';
            $header_array[] = 'Cache-Control: no-cache';
            //   $header_array[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
            $header_array[] = 'Pragma: no-cache';
            $header_array[] = 'Referer: https://eksisozluk.com/entry/' . $entry_id;
            $header_array[] = 'X-Requested-With: XMLHttpRequest';
            $header_array[] = 'Content-Length: ' . strlen("id=" . $entry_id);

            $this->curl->setHeaderArray($header_array);

            $return = $this->curl->post('https://eksisozluk.com/entry/sil');
            if(is_numeric(strpos($return,"HTTP/1.1 200 OK"))) {
            $confirmation = true;
            echo "\n $entry_id silindi gibi... 15 sn içinde sıradaki entry silinecek\n";
            } else {
            echo "\n $entry_id silinemedi... 15 sn içinde tekrar denenecek \n";
            sleep(15);
            }
        }
        return true;
    }


}
