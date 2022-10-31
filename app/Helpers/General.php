<?php

namespace App\Helpers;

use DateTime;
use DateInterval;
use File;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class General
{
    public static function screenLoad()
    {
        if (!Cookie::get('loadScreen')) {
            Cookie::queue('loadScreen', true, 2628000);
        }
    }

    public  static function checkScreenLoad()
    {
        $res = false;
        if (Cookie::get('loadScreen')) {
            $res = true;
        }
        return $res;
    }

    /**
     * return random code string
     *
     * @param array $not_in
     * @param int $length
     * @param bool $capital
     * @return string
     */
    public static function RandomCode($not_in = [], $length = 6, $capital = true)
    {
        $code = str_random($length);
        if ($capital) {
            $code = strtoupper($code);
        }

        if (in_array($code, $not_in)) {
            $code = self::RandomCode();
        }

        return $code;
    }

    /**
     * return session delegate
     *
     * @return mixed
     */
    public static function getSessionDelegate()
    {
        return Session::get('delegate_session');
    }

    /**
     * split word
     *
     * @param $word
     * @param int $get
     * @return mixed
     */
    public static function sliptWord($word, $get = 0)
    {
        $split = str_split($word);
        return $split[$get];
    }

    /**
     * make slug
     *
     * @param $title
     * @return string
     */
    public static function makeSlug($title)
    {
        $unique_text = ['?', '#', '$', '%', '^', '&', '*', '(', ')', '+', '=', '!', '@', '{', '}', '[', ']', '/', '<', '>', '–', '_', ':', ';', '|', '~'];
        $symbol_text = [',', '.', '"', "'", '’', '®'];

        $replace = str_replace($unique_text,'',$title);
        $replace = str_replace($symbol_text, '', $replace);
        $replace = str_replace('-', '', $replace);
        $replace = str_replace(' ','-', $replace);
        return strtolower($replace);
    }

    /**
     * @param $array
     * @param $parts
     * @return array
     */
    public static function fill_chunck($array, $parts)
    {
        $t = 0;
        $result = array_fill(0, $parts - 1, array());
        $max = ceil(count($array) / $parts);
        foreach($array as $v) {
            count($result[$t]) >= $max and $t ++;
            $result[$t][] = $v;
        }
        return $result;
    }

    /**
     * @param $array
     * @param $parts
     * @return array
     */
    public static function alternate_chunck($array, $parts) {
        $t = 0;
        $result = array();
        $max = ceil(count($array) / $parts);
        foreach(array_chunk($array, $max) as $v) {
            if ($t < $parts) {
                $result[] = $v;
            } else {
                foreach($v as $d) {
                    $result[] = array($d);
                }
            }
            $t += count($v);
        }
        return $result;
    }

    /**
     * @param $array
     * @param $size
     * @return array
     */
    public static function multiple_array($array, $size)
    {
        if (!empty($array)) {
            if (count($array) > 0) {
                $list = [];
                foreach ($array as $row) {
                    $list[] = $row;
                }
                return array_chunk($list, $size);
            }
        }
        return [];
    }

    /**
     * @param $start
     * @param $end
     * @return array
     * @throws \Exception
     */
    public static function dateArray($start, $end)
    {
        $result = [];

        $period = new \DatePeriod(
            new \DateTime($start),
            new \DateInterval('P1D'),
            new \DateTime($end)
        );

        foreach ($period as $key => $value) {
            $result[] = $value->format('Y-m-d');
        }
        $result[] = date('Y-m-d',strtotime($end));

        return $result;
    }

    /**
     * @param $n
     * @return string|void
     */
    public static function ToOrdinal($n) {
        /* Convert a cardinal number in the range 0 - 999 to an ordinal in
           words. */

        /* The ordinal will be collected in the variable $ordinal.
         Initialize it as an empty string.*/
        $ordinal = "";

        /* Check that the number is in the permitted range. */
        if ($n >= 0 && $n <= 999)
            null;
        else{
            echo "<br />You have called the function ToOrdinal with this value: $n, but
it is not in the permitted range, from 0 to 999, inclusive.<br />";
            return;
        }
        /* Extract the units. */
        $u = $n % 10;

        /* Extract the tens. */
        $t = floor(($n / 10) % 10);

        /* Extract the hundreds. */
        $h = floor($n / 100);

        /* Determine the hundreds */
        if ($h > 0) {

            /* ToCardinalUnits() works with numbers from 0 to 9, so it's okay
               for finding the number of hundreds, which must lie within this
               range. */
            $ordinal .= self::ToCardinalUnits($h);
            $ordinal .= " hundred";

            /* If tens and units are zero, append "th" and quit */
            if ($t == 0 && $u == 0) {
                $ordinal .=  "th";
            } else {
                /* Otherwise put in a blank space to separate the hundreds from
               what follows. */
                $ordinal .= " ";
            }
        }

        /* Determine the tens, unless there is just one ten.  If units are 0,
           handle them separately */
        if ($t >= 2 && $u != 0) {
            switch ($t) {
                case 2:
                    $ordinal .= "twenty-";
                    break;
                case 3:
                    $ordinal .= "thirty-";
                    break;
                case 4:
                    $ordinal .= "forty-";
                    break;
                case 5:
                    $ordinal .= "fifty-";
                    break;
                case 6:
                    $ordinal .= "sixty-";
                    break;
                case 7:
                    $ordinal .= "seventy-";
                    break;
                case 8:
                    $ordinal .= "eighty-";
                    break;
                case 9:
                    $ordinal .= "ninety-";
                    break;
            }
        }
        /* Print the tens (unless there is just one ten) with units == 0 */
        if ($t >= 2 && $u == 0) {
            switch ($t) {
                case 2:
                    $ordinal .= "twentieth";
                    break;
                case 3:
                    $ordinal .= "thirtieth";
                    break;
                case 4:
                    $ordinal .= "fortieth";
                    break;
                case 5:
                    $ordinal .= "fiftieth";
                    break;
                case 6:
                    $ordinal .= "sixtieth";
                    break;
                case 7:
                    $ordinal .= "seventieth";
                    break;
                case 8:
                    $ordinal .= "eightieth";
                    break;
                case 9:
                    $ordinal .= "ninetieth";
                    break;
            }
        }


        /* Print the teens, if the tens is 1. */
        if ($t == 1) {
            switch ($u) {
                case 0:
                    $ordinal .= "tenth";
                    break;
                case 1:
                    $ordinal .= "eleventh";
                    break;
                case 2:
                    $ordinal .= "twelfth";
                    break;
                case 3:
                    $ordinal .= "thirteenth";
                    break;
                case 4:
                    $ordinal .= "fourteenth";
                    break;
                case 5:
                    $ordinal .= "fifteenth";
                    break;
                case 6:
                    $ordinal .= "sixteenth";
                    break;
                case 7:
                    $ordinal .= "seventeenth";
                    break;
                case 8:
                    $ordinal .= "eighteenth";
                    break;
                case 9:
                    $ordinal .= "nineteenth";
                    break;
            }
        }

        /* Print the units. */
        if ($t != 1) {
            switch ($u) {
                case 0:
                    if ($n == 0)
                        $ordinal .= "zeroth";
                    break;
                case 1:
                    $ordinal .= "first";
                    break;
                case 2:
                    $ordinal .= "second";
                    break;
                case 3:
                    $ordinal .= "third";
                    break;
                case 4:
                    $ordinal .= "fourth";
                    break;
                case 5:
                    $ordinal .= "fifth";
                    break;
                case 6:
                    $ordinal .= "sixth";
                    break;
                case 7:
                    $ordinal .= "seventh";
                    break;
                case 8:
                    $ordinal .= "eighth";
                    break;
                case 9:
                    $ordinal .= "ninth";
                    break;
            }
        }
        return $ordinal;
    }

    /**
     * @param $n
     * @return string
     */
    public static function ToCardinalUnits($n) {
        /* Convert a number in the range 0 to 9 into its word equivalent. */

        /* Make sure the number is in the permitted range. */
        if ($n >= 0 && $n <= 9)
            null;
        else
        {
            echo "<br />You have called ToCardinal() with an argument $n, but the permitted range is 0 to 9, inclusive.<br />";
        }

        switch ($n) {
            case 0:
                return "zero";
            case 1:
                return "one";
            case 2:
                return "two";
            case 3:
                return "three";
            case 4:
                return "four";
            case 5:
                return "five";
            case 6:
                return "six";
            case 7:
                return "seven";
            case 8:
                return "eight";
            case 9:
                return "nine";
        }
    }

    /**
     * @param $name
     */
    public static function menuTag($name)
    {
        return Session::put('menu_tag', $name);
    }

    /**
     * @return mixed
     */
    public static function getMenuTag()
    {
        return Session::get('menu_tag');
    }

    /**
     * @param $url
     * @return mixed|string
     */
    public static function YoutubeTakeID($url)
    {
        $video_youtube = explode("?v=", $url);
        if (empty($video_youtube[1]))
            $video_youtube = explode("/v/", $url);

        $video_youtube = explode("&", $video_youtube[1]);
        $video_youtube = $video_youtube[0];

        return $video_youtube;
    }

    /**
     * @param $title
     * @param $message
     * @return string
     */
    public static function alertShow($title, $message)
    {
        return "return swal('$title', '$message', {
                    icon : 'warning',
                    buttons: {
                        confirm: {
                            className : 'btn-login-top'
                        }
                    },
                });";
    }

    /**
     * @param $s
     * @return bool
     */
    public static function isBase64($s){
        if (filter_var($s, FILTER_VALIDATE_URL) !== FALSE) return false;

        // get string base 64
        if ($s) $s = explode( ',', $s)[1];

        // Check if there are valid base64 characters
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s)) return false;

        // Decode the string in strict mode and check the results
        $decoded = base64_decode($s, true);
        if(false === $decoded) return false;

        // Encode the string again
        if(base64_encode($decoded) != $s) return false;

        return true;
    }

    /**
     * @return bool
     */
    public static function segmentCompany()
    {
        return in_array(Request::segment(1), ['company-profile', 'media-resource', 'company-news', 'project', 'product', 'bussiness-card', 'suggest-meeting', 'company-events', 'personal-profile']);
    }

    /**
     * generate link icon name
     * @param $name
     * @param int $size
     * @param string $background
     * @param string $color
     * @param bool $bold
     * @param bool $rounded
     * @return string
     */
    public static function makeICName($name, $size = 80, $bold = true, $rounded = false, $color = 'ffffff', $background = null)
    {
        $bgColor = ['8e44ad', '16a085', 'e67e22', '2980b9', 'e74c3c'];
        $background = (!empty($background)?$background:$bgColor[random_int(0, 4)]);
        return "https://ui-avatars.com/api/?background=$background&bold=$bold&rounded=$rounded&color=$color&size=$size&name=$name";
    }

    /**
     * @param $youtube_id
     * @return Object
     */
    public static function getContentYoutube($youtube_id) : Object
    {
        $API_KEY = env('YOUTUBE_API_KEY');
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.googleapis.com/youtube/v3/videos?part=contentDetails,snippet&fields=items/snippet(title,thumbnails/medium/url),items/contentDetails/duration&key=$API_KEY&id=$youtube_id",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 1000,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response, true);
        return static::setObjectContentYoutube($data);
    }

    /**
     * @param $data
     * @return Object
     */
    public static function setObjectContentYoutube($data): Object
    {
        $title = (!empty($data['items'][0]['snippet']['title'])?$data['items'][0]['snippet']['title']:"");
        $description = (!empty($data['items'][0]['snippet']['description'])?$data['items'][0]['snippet']['description']:"");
        $thumbnail = (!empty($data['items'][0]['snippet']['thumbnails']['medium']['url'])?$data['items'][0]['snippet']['thumbnails']['medium']['url']:"");
        $duration = (!empty($data['items'][0]['contentDetails']['duration'])?$data['items'][0]['contentDetails']['duration']:"");
        $duration = static::covTimeYoutube($duration);

        return (object) [
            "title" => $title,
            "description" => $description,
            "thumbnail" => $thumbnail,
            "duration" => $duration
        ];
    }

    /**
     * share button media social
     *
     * @param $social
     * @param $link
     * @param string $title
     * @return string
     */
    public static function socialShare($social, $link, $title = '')
    {
        switch ($social) {
            case 'linkedin':
                $url = "http://www.linkedin.com/shareArticle?mini=true&amp;url=$link";
                break;
            case 'facebook':
                $url = "http://www.facebook.com/share.php?u=$link";
                break;
            case 'twitter':
                $url = "https://twitter.com/share?url=$link&amp;text=$title&amp;hashtags=indonesiaminer";
                break;
            case 'whatsapp':
                if(preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"])){
                    $url = "whatsapp://send?text=$link";
                } else {
                    $url = "https://web.whatsapp.com/send?text=$link";
                }
                break;
            default:
                break;
        }

        return $url;
    }

    /**
     * return message
     *
     * @return string
     */
    public static function showMessage()
    {
        $message = Session::get('message');
        $show = Session::get('show');
        $html = '';
        if ($show === true) {
            $html .= '
                <div class="alert-text-danger">
                    <i class="ri-information-line"></i> '.$message.'
                </div>';
        }
        return $html;
    }

    /**
     * @return string
     */
    public static function showMessageRes()
    {
        $message = Session::get('message');
        $show = Session::get('status');
        $html = '';
        if ($show) {
            $html .= '
                <div class="alert-text-danger">
                    <i class="ri-information-line"></i> '.$message.'
                </div>';
        }
        return $html;
    }

    public static function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }

    public static function covTimeYoutube($youtube_time){
        if($youtube_time !== 0) {
            $start = new DateTime('@0'); // Unix epoch
            $start->add(new DateInterval($youtube_time));
            $hours = $start->format('H');
            $minutes = $start->format('i');
            $hours = (int)$hours * 60;
            $youtube_time = $hours + (int)$minutes;
        }
        
        return $youtube_time;
    }   

    public static function getFileSizeInMb($path)
    {
        if (File::exists(public_path($path))) {
            $filesize = filesize($path); // bytes
            $filesize = round($filesize / 1024 / 1024, 2);
            return $filesize . ' mb';
        } else {
            return null;
        }
    }
}
