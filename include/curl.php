<?php
/**
 * Created by PhpStorm.
 * User: mirzazeyrek
 * Date: 11.04.15
 * Time: 21:52
 */

class Curl {

    protected $cookie;
    protected $timeout;
    protected $useragent;
    protected $header_array = array();
    protected $post;
    protected $output_html;
    protected $links;
    protected $request_info;


    /**
     * @param $cookie curl browser için çerez dosyası ayarı
     */
    function __construct($cookie = "tayyip") {

        $this->setCookie($cookie);
        $this->setTimeout(60);
        $this->setUseragent(2);


    }

    /**
     * @param $timeout kullanılacak timeout süresi
     */
    public function setTimeout($timeout) {
        if(is_numeric($timeout))
        $this->timeout = $timeout;
    }


    /**
     * @return limandan demir almak zamanı gelmiş mi ?
     */
    public function getTimeout() {
            return $this->timeout;
    }

    /**
     * @param $timeout yapılacak işleme göre kullanılacak timeout süresini belirleyebilelim
     */
    public function setCookie($cookie) {

            $this->cookie = "cookie/".$cookie.".txt";


            if(!file_exists($this->cookie)) {
            $fp = fopen($this->cookie, "x");
            if($fp)
            fclose($fp);
            }

            if(!file_exists($this->cookie)) {
                echo "\n Kurabiye ".$this->cookie." ayarlanamadı \n cookie folderına yazma izni vermelisiniz. \n";
                die;
            }


    }

    /**
     * @return kurabiye ye bakalım.
     */
    public function getCookie(){
        return $this->cookie;
    }


    /**
     * @param $useragent taklit edilecek internet tarayıcısını belirler
     */
    public function setUseragent($useragent) {

        if($useragent == "firefox" or $useragent == 1) {
            $this->useragent = "Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0";
        } else if($useragent == "ie11" or $useragent == 2) {
            $this->useragent = "Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko";
        } else if($useragent == "ie10" or $useragent == 3) {
            $this->useragent = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0; InfoPath.2; SV1; .NET CLR 2.0.50727; WOW64)";
        } else if($useragent == "chrome" or $useragent == 4) {
            $this->useragent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36";
        } else if ($useragent == "safari" or $useragent == 5) {
            $this->useragent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A";
        } else {
        $this->useragent = $useragent;
        }
    }

    /**
     * @return mevcut browser bilgisi
     */
    public function getUseragent() {
        return $this->useragent;
    }


    /**
     * @param $headers -> post esnasında gönderilecek header array'i
     */
    public function setHeaderArray($array) {
        // önce ortalığı bi temizleyelim.
        $this->header_array = array();
        if(is_array($array)) {
        $this->header_array = $array;
        } else {

        echo "\n Header sadece array olabilir \n";
        die;

        }
    }

    /**
     * @param html string'i puraya gönderiyoruz
     * source: http://www.the-art-of-web.com/php/html-xpath-query/
     */
    public function setLinks($html, $by = null, $value = null) {
         error_reporting(0);
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        // all links in document
         $links = array();

        if($by=="id") {

            $container = $doc->getElementById($value);
            $arr = $container->getElementsByTagName("a");

        } if($by=="class") {
            // error reporting kullanıyoruz çünkü dom document saniyede 5.000 warning fırlatabiliyor.
            $xpath = new DOMXpath($doc);
            $nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $value ')]");
            $tmp_dom = new DOMDocument();
            // birden fazla aynı class ismine sahip blok olabilir
            foreach ($nodes as $node) {
                $tmp_dom->appendChild($tmp_dom->importNode($node, true));
            }

            $arr = $tmp_dom->getElementsByTagName("a");

        } if($by=="xpath") {
            $xpath = new DOMXpath($doc);
            $nodes = $xpath->query($value);
            $tmp_dom = new DOMDocument();
            // birden fazla aynı xpath'e sahip blok olabilir (mi acaba ?)
            foreach ($nodes as $node) {
                $tmp_dom->appendChild($tmp_dom->importNode($node, true));
            }

            $arr = $tmp_dom->getElementsByTagName("a");
        } else {

            $arr = $doc->getElementsByTagName("a");

        }

        // DOMNodeList Object
        foreach($arr as $item) {
        // DOMElement Object
         $href = $item->getAttribute("href");
         $title = $item->getAttribute("title");
         $text = trim(preg_replace("/[\r\n]+/", " ", $item->nodeValue));
         $links[] = array( 'href' => $href, 'text' => $text, 'title' => $title );
        }
        error_reporting(1);
        $this->links = $links;
       // print_r($links);

    }

    public function getLinks() {
        return $this->links;
    }

    /**
     * @return array ayarlanmış header bilgileri
     */
    public function getHeaderArray(){
        return $this->header_array;
    }


    /**
     * @param $post = post esnasında gönderilecek header array'i veya string'i
     */
    public function setPost($post) {
        if(is_string($post) or is_array($post)) {
        $this->post = $post;
        } else {
        echo " post variable string veya array olmak zorunda ";
        die;
        }
    }


    /**
     * @return array ayarlanmış header bilgileri
     */
    public function getPost(){
        return $this->post;
    }

    /**
     * @return mixed
     */
    public function getOutputHtml()
    {
        return $this->output_html;
    }

    /**
     * @param mixed $output_html
     */
    public function setOutputHtml($output_html)
    {
        $this->output_html = $output_html;
    }

    /**
     * @param $url gidilmek istenen url
     * @return mixed request çıktısı eğer varsa
     */
    public function get($url, $noredirect = false) {

        if(count($this->header_array)<1) {
            echo "\n header array'i ayarlamadan bu işlemi yapamazsınız \n";
            die;
        }

        if(!preg_match("/(http:)|(https:)/",$url))
        $url = "http://".$url;
        echo "\n $url \n";

        $url = str_replace( "&amp;", "&", urldecode(trim($url)) );
        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_HTTPHEADER, $this->header_array);
        curl_setopt( $ch, CURLOPT_USERAGENT, $this->useragent );
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_COOKIEJAR, $this->cookie );
        curl_setopt( $ch, CURLOPT_COOKIEFILE, $this->cookie );

       // curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
      //  curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->timeout );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $this->timeout );
      //  curl_setopt( $ch, CURLOPT_VERBOSE, true);

        if($noredirect == true) {
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false);
        } else {
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        }
        $content = curl_exec( $ch );
        $this->request_info = curl_getinfo( $ch );
        curl_close ( $ch );
        return $content;
    }

    /**
     * @param $url post yapılacak olan url
     * @return mixed request çıktısı eğer varsa
     */
    public function post($url, $noredirect = false) {

        if(count($this->header_array)<1) {
            echo "\n header array'i ayarlamadan bu işlemi yapamazsınız \n";
            die;
        }

        if(count($this->post)<1) {
            echo "\n post ayarlamadan bu işlemi yapamazsınız \n";
            die;
        }

        if(!preg_match("/(http:)|(https:)/",$url))
            $url = "http://".$url;
        echo "\n $url \n";

        $url = str_replace( "&amp;", "&", urldecode(trim($url)) );
        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_HTTPHEADER, $this->header_array);
        curl_setopt( $ch, CURLOPT_USERAGENT, $this->useragent );
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_COOKIEJAR, $this->cookie );
        curl_setopt( $ch, CURLOPT_COOKIEFILE, $this->cookie );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $this->post);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->timeout );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $this->timeout );
        curl_setopt( $ch, CURLOPT_HEADER, true);
        curl_setopt( $ch, CURLOPT_POST, TRUE);

        if($noredirect == true) {
            print_r("\n REDIRECT YOK \n");
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false);
        } else {
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        }

        $content = curl_exec( $ch );
        $this->request_info = curl_getinfo( $ch );
        curl_close ( $ch );

        return $content;
    }



}