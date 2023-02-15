<?php

namespace App\Traits;

use App\Jobs\DataJobs;
use App\Jobs\KecamatanJobs;
use GuzzleHttp\Client;
use App\Models\Api\Muser;
use Illuminate\Support\Carbon;
use App\Models\Api\Ayopos\CartPos;
use Illuminate\Support\Facades\DB;
use App\Models\Api\Ayopos\TotalPos;
use Illuminate\Support\Facades\Http;
use App\Models\Api\Ayopos\MerchantPos;
use Stichoza\GoogleTranslate\GoogleTranslate;

trait MyHelper
{
    function singkat_price($n, $presisi = 1)
    {
        if ($n < 900) {
            $format_angka = number_format($n, $presisi,);
            $simbol = '';
        } else if ($n < 900000) {
            $format_angka = number_format($n / 1000, $presisi);
            if (str_replace('_', '-', app()->getLocale()) == 'id') {
                $simbol = 'Rb';
            } else {
                $simbol = 'K';
            }
        } else if ($n < 900000000) {
            $format_angka = number_format($n / 1000000, $presisi);
            if (str_replace('_', '-', app()->getLocale()) == 'id') {
                $simbol = 'Jt';
            } else {
                $simbol = 'M';
            }
        } else if ($n < 900000000000) {
            $format_angka = number_format($n / 1000000000, $presisi);
            $simbol = 'M';
        } else {
            $format_angka = number_format($n / 1000000000000, $presisi);
            $simbol = 'T';
        }

        if ($presisi > 0) {
            $pisah = '.' . str_repeat('0', $presisi);
            $format_angka = str_replace($pisah, '', $format_angka);
        }

        return $format_angka . ' ' . $simbol;
    }

    // khusus untuk zipay payment
    public function encrypt_decrypt($action, $string)
    {
        $timestamp = (int)round(microtime(true) * 1000000);

        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = "899669c2f645e43c0f4ff732b265fe38";
        $secret_iv = $timestamp;

        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $secret_key, 0, (int)$secret_iv);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $secret_key, 0, $secret_iv);
        }

        return $output;
    }

    public function hpFormat($nohps)
    {
        $hps = 0;
        // kadang ada penulisan no hp 0811 239 345
        $nohps = str_replace(" ", "", $nohps);
        // kadang ada penulisan no hp (0274) 778787
        $nohps = str_replace("(", "", $nohps);
        // kadang ada penulisan no hp (0274) 778787
        $nohps = str_replace(")", "", $nohps);
        // kadang ada penulisan no hp 0811.239.345
        $nohps = str_replace(".", "", $nohps);

        $nohps = str_replace("-", "", $nohps);

        // cek apakah no hp mengandung karakter + dan 0-9
        if (!preg_match('/[^+0-9]/', trim($nohps))) {
            // cek apakah no hp karakter 1-3 adalah +62
            if (substr(trim($nohps), 0, 3) == '+62') {
                $hps = '' . substr(trim($nohps), 3);
            } // cek apakah no hp karakter 1 adalah 0
            elseif (substr(trim($nohps), 0, 2) == '62') {
                $hps = '' . substr(trim($nohps), 2);
            }
            // cek apakah no hp karakter 1 adalah 0
            elseif (substr(trim($nohps), 0, 1) == '0') {
                $hps = '' . substr(trim($nohps), 1);
            } else {
                $hps = $nohps;
            }
        } else {
            $hps = $nohps;
        }

        return $hps;
    }

    public function userDetected()
    {
        $u_agent     = $_SERVER['HTTP_USER_AGENT'];
        $bname       = 'Unknown';
        $platform     = 'Unknown';
        $version     = "";

        $os_array   =   array(
            '/windows nt 10.0/i'     =>  'Windows 10',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );

        foreach ($os_array as $regex => $value) {

            if (preg_match($regex, $u_agent)) {
                $platform    =   $value;
                break;
            }
        }

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } else {
            $bname = 'Unknown';
            $ub = "Unknown";
        }

        //  finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        $version = ($version == null || $version == "") ? "?" : $version;

        $data = array(
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'   => $pattern
        );

        $browser_agent = $data['name'] . ' v.' . $data['version'];
        $OS = $data;
        $os = $OS['platform'];

        return $os;
    }

    function mataUang()
    {
        if (str_replace('_', '-', app()->getLocale()) == 'id') {
            $mata_uang = 'Rp ';
        } else {
            $mata_uang = 'IDR ';
        }

        return $mata_uang;
    }

    static function mataUangs()
    {
        if (str_replace('_', '-', app()->getLocale()) == 'id') {
            $mata_uang = 'Rp ';
        } else {
            $mata_uang = 'IDR ';
        }

        return $mata_uang;
    }

    public function timeAgo($timestamp)
    {
        $translate = new GoogleTranslate();
        $time_ago = strtotime($timestamp);
        $current_time = time();
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;
        $minutes      = round($seconds / 60);        // value 60 is seconds
        $hours        = round($seconds / 3600);       //value 3600 is 60 minutes * 60 sec
        $days         = round($seconds / 86400);      //86400 = 24 * 60 * 60;
        $weeks        = round($seconds / 604800);     // 7*24*60*60;
        $months       = round($seconds / 2629440);    //((365+365+365+365+366)/5/12)*24*60*60
        $years        = round($seconds / 31553280);   //(365+365+365+365+366)/5 * 24 * 60 * 60

        if ($seconds <= 60) {

            if (str_replace('_', '-', app()->getLocale()) == 'id') :
                $sekarang = 'Sekarang';
            else :
                $sekarang = $translate->setTarget('en')->translate('Sekarang');
            endif;

            return $sekarang;
        } else if ($minutes <= 60) {

            if (str_replace('_', '-', app()->getLocale()) == 'id') :
                $menit = 'Menit lalu';
            else :
                $menit = $translate->setTarget('en')->translate('Menit lalu');
            endif;

            if ($minutes == 1) {
                return "1 " . $menit;
            } else {
                return "$minutes " . $menit;
            }
        } else if ($hours <= 24) {

            if (str_replace('_', '-', app()->getLocale()) == 'id') :
                $jam = 'Jam lalu';
            else :
                $jam = $translate->setTarget('en')->translate('Jam lalu');
            endif;

            if ($hours == 1) {
                return "1 " . $jam;
            } else {
                return "$hours " . $jam;
            }
        } else if ($days <= 7) {

            if (str_replace('_', '-', app()->getLocale()) == 'id') :
                $Kemarin = 'Kemarin';
                $hariLalu = 'Hari lalu';
            else :
                $Kemarin = $translate->setTarget('en')->translate('Kemarin');
                $hariLalu = $translate->setTarget('en')->translate('Hari lalu');
            endif;

            if ($days == 1) {
                return $Kemarin;
            } else {
                return "$days " . $hariLalu;
            }
        } else if ($weeks <= 4.3) {  //4.3 == 52/12

            if (str_replace('_', '-', app()->getLocale()) == 'id') :
                $mingguLalu = 'Minggu lalu';
            else :
                $mingguLalu = $translate->setTarget('en')->translate('Minggu lalu');
            endif;

            if ($weeks == 1) {
                return "1 " . $mingguLalu;
            } else {
                return "$weeks " . $mingguLalu;
            }
        } else if ($months <= 12) {

            if (str_replace('_', '-', app()->getLocale()) == 'id') :
                $bulanLalu = 'Bulan lalu';
            else :
                $bulanLalu = $translate->setTarget('en')->translate('Bulan lalu');
            endif;

            if ($months == 1) {
                return "1 " . $bulanLalu;
            } else {
                return "$months " . $bulanLalu;
            }
        } else {

            if (str_replace('_', '-', app()->getLocale()) == 'id') :
                $tahunLalu = 'Tahun lalu';
            else :
                $tahunLalu = $translate->setTarget('en')->translate('Tahun lalu');
            endif;

            if ($years == 1) {
                return "1 " . $tahunLalu;
            } else {
                return "$years " . $tahunLalu;
            }
        }
    }

    public function userAgentIp()
    {
        function ip_user()
        {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            return $ip;
        }

        $ip = ip_user();
        $array = $ip;
        return $array;
    }

    public function randomNumber()
    {
        $permitted_charsdd = '0123456789';
        function generate_stringdd($inputdd, $strengthdd = 16)
        {
            $input_lengthdd = strlen($inputdd);
            $random_stringdd = '';
            for ($idd = 0; $idd < $strengthdd; $idd++) {
                $random_characterdd = $inputdd[mt_rand(0, $input_lengthdd - 1)];
                $random_stringdd .= $random_characterdd;
            }
            return $random_stringdd;
        }
        $kodx = generate_stringdd($permitted_charsdd, 5);
        return $kodx;
    }

    public function getImageMimeType($imagedata)
    {
        $imagemimetypes = array(
            "jpeg" => "FFD8",
            "png" => "89504E470D0A1A0A",
            "gif" => "474946",
            "bmp" => "424D",
            "tiff" => "4949",
            "tiff" => "4D4D"
        );

        foreach ($imagemimetypes as $mime => $hexbytes) {
            $bytes = $this->getBytesFromHexString($hexbytes);
            if (substr($imagedata, 0, strlen($bytes)) == $bytes)
                return $mime;
        }

        return NULL;
    }

    public function getBytesFromHexString($hexdata)
    {
        for ($count = 0; $count < strlen($hexdata); $count += 2)
            $bytes[] = chr(hexdec(substr($hexdata, $count, 2)));

        return implode($bytes);
    }

    public function base_url()
    {
        $url = url('');

        return $url;
    }

    public function timeRand()
    {
        $timeRand = time();

        return $timeRand;
    }

    public function timeSignal($index = null)
    {
        // get date & time
        $carbon_time = Carbon::now();
        $date = Carbon::create($carbon_time->toDateString(), 'Asia/Jakarta')->format('Y-m-d');
        $time = $carbon_time->format('H:i:s');
        $timestamps = $carbon_time->format('Y-m-d\TH:i:s.uP');

        $array = [
            'date' => $date,
            'time' => $time,
            'timestamp' => $timestamps
        ];

        return $array[$index];
    }

    // function untuk encrypt pin
    public function encryptPin($data)
    {
        $key = 'qkwjdiw239&&jdafweihbrhnan&^%$ggdnawhd4njshjwuuO';

        $encryption_key = base64_decode($key);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    // function untuk decrypt pin
    public function decryptPin($data)
    {
        $key = 'qkwjdiw239&&jdafweihbrhnan&^%$ggdnawhd4njshjwuuO';

        $encryption_key = base64_decode($key);
        list($encrypted_data, $iv) = array_pad(explode('::', base64_decode($data), 2), 2, null);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    }

    public function unix_time()
    {
        $unix = strtotime(date('Y-m-d H:i:s'));
        return $unix;
    }

    public function CheckUrl($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code == 200) {
            $status = true;
        } else {
            $status = false;
        }
        return $status;
    }

    public static function days_id()
    {
        $hari = date("D");

        switch ($hari) {
            case 'Sun':
                $hari_ini = "Minggu";
                break;

            case 'Mon':
                $hari_ini = "Senin";
                break;

            case 'Tue':
                $hari_ini = "Selasa";
                break;

            case 'Wed':
                $hari_ini = "Rabu";
                break;

            case 'Thu':
                $hari_ini = "Kamis";
                break;

            case 'Fri':
                $hari_ini = "Jumat";
                break;

            case 'Sat':
                $hari_ini = "Sabtu";
                break;

            default:
                $hari_ini = "Tidak di ketahui";
                break;
        }

        return $hari_ini;
    }
}
