<?php
@session_start();
ob_start();
ini_set("display_errors",1);
include "VeriKatmani.php";
$db = new veritabaniislem();
$dizin = "";
date_default_timezone_set('Europe/Istanbul');
$siteURL = $db->VeriOkuTek ("ayarlar","siteadresi","id",1);
$diskaynakwebsitesi = "http://superhabersitesi.com/kaynak";
$ayarlar = $db->VeriOkuCoklu("ayarlar")[0];
include"class.upload.php";
if(isset($_COOKIE["Kullanici"]))
{
    $_SESSION["KullaniciID"] = $_COOKIE["Kullanici"];
    $_SESSION["Yetki"] = $_COOKIE["Yetki"];
}else
{
    if(isset($_SESSION["Kullanici"]))
    {
        $_SESSION["KullaniciID"] = $_SESSION["Kullanici"];
        $_SESSION["Yetki"] = $_SESSION["Yetki"];
    }
}

include"titleler.php";
include "generalfunction.php";
$general_function = new General_Function();
Class Sistem
{
    function getRandomUserAgent()
    {
        $userAgents=array(
          "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6",
          "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
          "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)",
          "Opera/9.20 (Windows NT 6.0; U; en)",
          "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en) Opera 8.50",
          "Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 5.1) Opera 7.02 [en]",
          "Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; fr; rv:1.7) Gecko/20040624 Firefox/0.9",
          "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/48 (like Gecko) Safari/48"
        );
        $random = rand(0,count($userAgents)-1);

        return $userAgents[$random];
    }
    function PostMetodu($url)
    {

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_USERAGENT,$this->getRandomUserAgent());
        curl_setopt($ch,CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST);
        curl_setopt($ch, CURLOPT_POSTFIELDS);

        $output=curl_exec($ch);

        curl_close($ch);
        return $output;
    }
    function GetMetodu($url) {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_USERAGENT,$this->getRandomUserAgent());
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 90); // times out after 90s
        // curl_setopt($ch,CURLOPT_HEADER, false);

        $output=curl_exec($ch);
        curl_close($ch);
        return  $output;
    }
    function ara($bas, $son, $yazi)
    {
        @preg_match_all('/' . preg_quote($bas, '/') .
                        '(.*?)' . preg_quote($son, '/') . '/s', $yazi, $m);
        return @$m[1];
    }

    function mailGonder($konu, $mesaj, $mailadresleri = array(),$gonderen="",$imza="")
    {
        global $db;
        include 'class.phpmailer.php';
        if($imza=="")
        {
            $mesaj =$mesaj;
        }else
        {
            $mesaj=$mesaj." <br><br>".$imza;
        }
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = $db->VeriOkuTek("ayarlar","smtphost","id",1);
        $mail->Port = $db->VeriOkuTek("ayarlar","smtpport","id",1);
        $mail->Username = $db->VeriOkuTek("ayarlar","smtpuser","id",1);
        $mail->Password = $db->VeriOkuTek("ayarlar","smtpsifre","id",1);
        $mail->SMTPSecure =  $db->VeriOkuTek("ayarlar","smtpguvenlik","id",1);
        $mail->FromName=$gonderen;
        $mail->From=$db->VeriOkuTek("ayarlar","smtpuser","id",1);

        $mail->SetFrom($gonderen, $konu);
      
        foreach ($mailadresleri as $m)
        {
            $mail->AddBCC($m, 'Siz');
        }

        $mail->CharSet = 'UTF-8';
        $mail->Subject = $konu;
        $mail->MsgHTML($mesaj);
        if ($mail->Send())
        {
            return true;
        } else
        {
            return false;
        }
    }
    function parametrelimailGonder($sablonID,$parametreler=array(),$parametrelerkarsilik = array(),$mailadresleri = array())
    {
        global $db;
        include 'class.phpmailer.php';
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = $db->VeriOkuTek("genelayarlar", "SmtpHOST", "GenelID", 1);
        $mail->Port = $db->VeriOkuTek("genelayarlar", "SmtpPORT", "GenelID", 1);
        $mail->Username = $db->VeriOkuTek("genelayarlar", "SmtpUSER", "GenelID", 1);
        $mail->Password = $db->VeriOkuTek("genelayarlar", "SmtpPASS", "GenelID", 1);
        $mail->SMTPSecure = 'ssl';
        $mail->SetFrom($mail->Username, $db->VeriOkuTek ("mailsablonlari","SablonGONDEREN","SablonID",$sablonID));
        foreach ($mailadresleri as $m)
        {
            $mail->AddAddress($m, 'Siz');
        }

        $mail->CharSet = 'UTF-8';
        $mail->Subject = $db->VeriOkuTek ("mailsablonlari","SablonKONU","SablonID",$sablonID);

        $mail->MsgHTML(str_replace ($parametreler,$parametrelerkarsilik,$db->VeriOkuTek ("mailsablonlari","SablonACIKLAMA","SablonID",$sablonID)));
        if ($mail->Send())
        {

            return true;
        } else
        {
            return false;
        }
    }


    function parametrelismsGonder($sablonID,$parametreler=array(),$parametrelerkarsilik = array(),$numaralar = array())
    {
        global $db;
        $username = $db->VeriOkuTek("genelayarlar", "SmsUSER", "GenelID", 1);
        $pass = $db->VeriOkuTek("genelayarlar", "SmsPASS", "GenelID", 1);
        $header = $db->VeriOkuTek("genelayarlar", "SmsHEADER", "GenelID", 1);
        $telefonlar ="";
        foreach ($numaralar as $n)
        {
            $telefonlar.=str_replace(array("(", ")", " "), array("", "", ""), $n).",";
        }
        $telefonnumarasi =substr ($telefonlar,0,strlen ($telefonlar)-1);
        $startdate = date('d.m.Y H:i');
        $startdate = str_replace('.', '', $startdate);
        $startdate = str_replace(':', '', $startdate);
        $startdate = str_replace(' ', '', $startdate);

        $stopdate = date('d.m.Y H:i', strtotime('+1 day'));
        $stopdate = str_replace('.', '', $stopdate);
        $stopdate = str_replace(':', '', $stopdate);
        $stopdate = str_replace(' ', '', $stopdate);

        $mesaj =str_replace(" ","%20",str_replace ($parametreler,$parametrelerkarsilik,$db->VeriOkuTek ("smssablonlari","SablonMESAJ","SablonID",$sablonID)));
        $url = "https://api.netgsm.com.tr/bulkhttppost.asp?usercode=$username&password=$pass&gsmno=$telefonnumarasi&message=$mesaj&msgheader=$header&startdate=$startdate&stopdate=$stopdate&dil=TR";
        //echo $url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //  curl_setopt($ch,CURLOPT_HEADER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    function smsGonder($gsm, $mesaj)
    {
        global $db;
        $username = $db->VeriOkuTek("genelayarlar", "SmsUSER", "GenelID", 1);
        $pass = $db->VeriOkuTek("genelayarlar", "SmsPASS", "GenelID", 1);
        $header = $db->VeriOkuTek("genelayarlar", "SmsHEADER", "GenelID", 1);
        $telefonnumarasi = str_replace(array("(", ")", " "), array("", "", ""), $gsm);
        $startdate = date('d.m.Y H:i');
        $startdate = str_replace('.', '', $startdate);
        $startdate = str_replace(':', '', $startdate);
        $startdate = str_replace(' ', '', $startdate);

        $stopdate = date('d.m.Y H:i', strtotime('+1 day'));
        $stopdate = str_replace('.', '', $stopdate);
        $stopdate = str_replace(':', '', $stopdate);
        $stopdate = str_replace(' ', '', $stopdate);

        $mesaj = str_replace(" ","%20",$mesaj);


        $url = "https://api.netgsm.com.tr/bulkhttppost.asp?usercode=$username&password=$pass&gsmno=$telefonnumarasi&message=$mesaj&msgheader=$header&startdate=$startdate&stopdate=$stopdate&dil=TR";
        //echo $url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //  curl_setopt($ch,CURLOPT_HEADER, false);
        $output = curl_exec($ch);

        curl_close($ch);
        return $output;
    }


    function ucfirst_tr($metin)
    {
        $k_uzunluk = mb_strlen($metin, "UTF-8");
        $ilkKarakter = mb_substr($metin, 0, 1, "UTF-8");
        $kalan = mb_substr($metin, 1, $k_uzunluk - 1, "UTF-8");
        return mb_strtoupper($ilkKarakter, "UTF-8") . mb_strtolower($kalan, "UTF-8");
    }

    function trbuyut($str)
    {
        $str = str_replace(array('i', 'ı', 'ü', 'ğ', 'ş', 'ö', 'ç'), array('İ', 'I', 'Ü', 'Ğ', 'Ş', 'Ö', 'Ç'), $str);
        return strtoupper($str);
    }

    function active()
    {
        $file = explode("/", $_SERVER["SCRIPT_NAME"]);
        $bulunan = end($file);
        $active = "";


        if ($bulunan == "sayfadetay.php")
        {
            $active = 0;
        }
        if ($bulunan == "websiteleri.php")
        {
            $active = 1;
        }
        if ($bulunan == "webkategori.php")
        {
            $active = 1;
        }
        if ($bulunan == "referanslar.php")
        {
            $active = 2;
        }
        if ($bulunan == "blog.php")
        {
            $active = 3;
        }
        if ($bulunan == "blogdetay.php")
        {
            $active = 3;
        }
        if ($bulunan == "iletisim.php")
        {
            $active = 4;
        }
        return $active;
    }


    function gun_bas($tarih){
        global $db;
        $tarih=explode ("-",$db->cevir($tarih));
        $gun = date("l",mktime(0,0,0,$tarih[1],$tarih[0],$tarih[2]));
        $gun_ingilizce = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
        $turkce_gun = array('Pazartesi','Sal&#305;','&Ccedil;ar&#351;amba','Per&#351;embe','Cuma','Cumartesi','Pazar');
        $gun_degis = str_replace($gun_ingilizce,$turkce_gun,$gun);
        return $gun_degis;
    }
    function datetimeDuzelt($tarih)
    {
        $datetimeparcala = explode(" ",$tarih);
        $tarih = explode(".",$datetimeparcala[0]);

        $yil = $tarih[2]."-".$tarih[1]."-".$tarih[0]." ".$datetimeparcala[2].":00";

        return $yil;
    }
    function tarihYaz($tarih)
    {

        $tarihbol = explode("-", $tarih);

        $yil = $tarihbol[0];
        $gun = $tarihbol[2];

        $ay = $tarihbol[1];

        if ($ay < 10)
        {
            $ay = str_replace("0", "", $ay);
        } else
        {
            $ay = $ay;
        }
        $aylar = array("", "Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık");

        echo $gun . " " . $aylar[$ay] . " " . $yil;
    }

    function ayliveSaatliGunlucevir($tarih)
    {
        $aylar = array("", "Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık");

        $bol = explode(" ", $tarih);
        $saat = $bol[1];
        $tarihs = $bol[0];
        $tarihler = explode("-", $tarihs);
        $ay = $tarihler[1];
        if ($ay < 10)
        {
            $ay = str_replace("0", "", $ay);
        } else
        {
            $ay = $ay;
        }

        $gun = $this->gun_bas($tarihs);


        echo $tarihler[2] . " " . $aylar[$ay] . " " . $tarihler[0] . " $gun " . $saat;
    }
    function SaatliGunlucevir($tarih)
    {
        $aylar = array("", "Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık");

        $bol = explode(" ", $tarih);
        $saat = $bol[1];
        $tarihs = $bol[0];
        $tarihler = explode("-", $tarihs);
        $ay = $tarihler[1];
        if ($ay < 10)
        {
            $ay = str_replace("0", "", $ay);
        } else
        {
            $ay = $ay;
        }

        $gun = $this->gun_bas($tarihs);
        $saatbol = explode(":",$saat);


        echo $tarihler[2].".".$tarihler[1].".".$tarihler[0]. " $gun " . $saatbol[0].":".$saatbol[1];
    }
    function ayliveSaatlicevir($tarih)
    {
        $aylar = array("", "Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık");

        $bol = explode(" ", $tarih);
        $saat = $bol[1];
        $tarihs = $bol[0];
        $tarihler = explode("-", $tarihs);
        $ay = $tarihler[1];
        if ($ay < 10)
        {
            $ay = str_replace("0", "", $ay);
        } else
        {
            $ay = $ay;
        }


        echo $tarihler[2] . " " . $aylar[$ay] . " " . $tarihler[0] . " " . $saat;
    }


    function sifreUret()
    {
        return md5 (rand (0,100000));

    }


    function getBrowser()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Bilinmiyor';
        $mobil = 'Bilinmiyor';
        //Sonra hangi tarayıcı olduğuna  göz atalım
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent))
        {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent))
        {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent))
        {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent))
        {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent))
        {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent))
        {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        $iphone = strpos($u_agent, "iPhone");
        $android = strpos($u_agent, "Android");
        $ipod = strpos($u_agent, "iPod");
        if ($iphone == true || $android == true || $ipod == true)
        {
            $mobil = "Mobil";
        } else
        {
            $mobil = "Bilgisayar";
        }
        return array($bname, $mobil);
    }

    function tariheGunEkle($hangitarih,$kacgun)
    {
        $bugun =$hangitarih;
        $yenitarih = strtotime("$kacgun day",strtotime($bugun));
        $yenitarih = date('Y-m-d' ,$yenitarih );
        return $yenitarih;
    }
    function tarihtengunCikar($hangitarih,$kacgun)
    {
        $bugun =$hangitarih;
        $yenitarih = strtotime("-$kacgun day",strtotime($bugun));
        $yenitarih = date('Y-m-d' ,$yenitarih );
        return $yenitarih;
    }

    function duzenlerkenKeywordCek($tur,$id)
    {
        global $db;
        $keywordler ="";
        if($tur =="makale")
        {
            $db->VeriOkuCoklu("makale_keyword",array("MakaleID"),array($id));
        }
        if($tur =="video")
        {
            $db->VeriOkuCoklu("video_keyword",array("VideoID"),array($id));
        }
        foreach ($db->bilgial as $row)
        {
            $keywordler.=$db->VeriOkuTek("sistem_keyword","KeywordBASLIK","KeywordID",$row->KeywodID).",";
        }
        $keywordler = substr($keywordler,0,strlen($keywordler)-1);
        return $keywordler;

    }

    function HttpStatus($code) {
        $status = array(
          100 => 'Continue',
          101 => 'Switching Protocols',
          200 => 'OK',
          201 => 'Created',
          202 => 'Accepted',
          203 => 'Non-Authoritative Information',
          204 => 'No Content',
          205 => 'Reset Content',
          206 => 'Partial Content',
          300 => 'Multiple Choices',
          301 => 'Moved Permanently',
          302 => 'Found',
          303 => 'See Other',
          304 => 'Not Modified',
          305 => 'Use Proxy',
          306 => '(Unused)',
          307 => 'Temporary Redirect',
          400 => 'Bad Request',
          401 => 'Unauthorized',
          402 => 'Payment Required',
          403 => 'Forbidden',
          404 => 'Not Found',
          405 => 'Method Not Allowed',
          406 => 'Not Acceptable',
          407 => 'Proxy Authentication Required',
          408 => 'Request Timeout',
          409 => 'Conflict',
          410 => 'Gone',
          411 => 'Length Required',
          412 => 'Precondition Failed',
          413 => 'Request Entity Too Large',
          414 => 'Request-URI Too Long',
          415 => 'Unsupported Media Type',
          416 => 'Requested Range Not Satisfiable',
          417 => 'Expectation Failed',
          500 => 'Internal Server Error',
          501 => 'Not Implemented',
          502 => 'Bad Gateway',
          503 => 'Service Unavailable',
          504 => 'Gateway Timeout',
          505 => 'HTTP Version Not Supported');

        // gönderilen kod listede yok ise 500 durum kodu gönderilsin.
        return $status[$code] ? $status[$code] : $status[500];
    }

    // Header ayarlama fonksiyonu
    function SetHeader($code){
        header("HTTP/1.1 ".$code." ".$this->HttpStatus($code));
        header("Content-Type: application/json; charset=utf-8");
    }



    function yonlendir()
    {
        global $db;
        global $bulunan;
        $siteURL = $db->VeriOkuTek ("genelayarlar","SiteURL","GenelID",1);
        if($bulunan=="firmapenelianasayfa.php")
        {
            if(!isset($_SESSION["KullaniciID"]) OR $_SESSION["Yetki"]!="Firma")
            {
                $yonlendir = $siteURL;
                $saniye =0;
                $exit =true;
            }
        }elseif($bulunan=="uyepanelianasayfa.php")
        {
            if(!isset($_SESSION["KullaniciID"]) OR $_SESSION["Yetki"]!="Üye")
            {
                if(!isset($_POST["UyeID"]))
                {
                    $yonlendir = $siteURL;
                    $saniye =0;
                    $exit =true;
                }

            }
        }
        elseif($bulunan=="urundetay.php")
        {
            $kontrol = $db->VeriOkuTek("urunler","Kontrol","UrunID",$_GET["UrunID"]);
            if($kontrol==2 OR $kontrol==5 OR $kontrol==6 OR $kontrol==7 OR $kontrol==8)
            {
                $yonlendir = $siteURL;
                $saniye =0;
                $exit =true;
            }
        }
        if(isset($yonlendir))
        {
            ?>
            <script>
                setTimeout(function() { document.location = '<?=$yonlendir?>'; }, <?=$saniye?>)
            </script>
            <?php
            if($exit===true)
            {
                exit();
            }
        }

    }

    function guncelKur()
    {
        global $diskaynakwebsitesi;
        file_get_contents("$diskaynakwebsitesi/doviz/index.php");
        $kaynak = json_decode(file_get_contents("$diskaynakwebsitesi/doviz/cache.php"));
        return  $kaynak;

    }




}



$sistem = new Sistem();


