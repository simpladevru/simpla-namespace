<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 19:42
 */

namespace Root\api\components\design\smarty;

use Root\api\models\product\ImageHelper;
use Root\api\Simpla;

class AppExtension extends BaseExtension implements SmartyExtensionInterface
{
    public function register()
    {
        $this->smarty->registerPlugin('modifier', 'resize', array($this, 'resize_modifier'));
        $this->smarty->registerPlugin('modifier', 'token',  array($this, 'token_modifier'));
        $this->smarty->registerPlugin('modifier', 'plural', array($this, 'plural_modifier'));
        $this->smarty->registerPlugin('function', 'url',    array($this, 'url_modifier'));
        $this->smarty->registerPlugin('modifier', 'first',  array($this, 'first_modifier'));
        $this->smarty->registerPlugin('modifier', 'cut',    array($this, 'cut_modifier'));
        $this->smarty->registerPlugin('modifier', 'date',   array($this, 'date_modifier'));
        $this->smarty->registerPlugin('modifier', 'time',   array($this, 'time_modifier'));
        $this->smarty->registerPlugin('function', 'api',    array($this, 'api_plugin'));
    }

    private function is_mobile_browser()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $http_accept = isset($_SERVER['HTTP_ACCEPT'])?$_SERVER['HTTP_ACCEPT']:'';

        if(eregi('iPad', $user_agent))
            return false;

        if(stristr($user_agent, 'windows') && !stristr($user_agent, 'windows ce'))
            return false;

        if(eregi('windows ce|iemobile|mobile|symbian|mini|wap|pda|psp|up.browser|up.link|mmp|midp|phone|pocket', $user_agent))
            return true;

        if(stristr($http_accept, 'text/vnd.wap.wml') || stristr($http_accept, 'application/vnd.wap.xhtml+xml'))
            return true;

        if(!empty($_SERVER['HTTP_X_WAP_PROFILE']) || !empty($_SERVER['HTTP_PROFILE']) || !empty($_SERVER['X-OperaMini-Features']) || !empty($_SERVER['UA-pixels']))
            return true;

        $agents = array(
            'acs-'=>'acs-',
            'alav'=>'alav',
            'alca'=>'alca',
            'amoi'=>'amoi',
            'audi'=>'audi',
            'aste'=>'aste',
            'avan'=>'avan',
            'benq'=>'benq',
            'bird'=>'bird',
            'blac'=>'blac',
            'blaz'=>'blaz',
            'brew'=>'brew',
            'cell'=>'cell',
            'cldc'=>'cldc',
            'cmd-'=>'cmd-',
            'dang'=>'dang',
            'doco'=>'doco',
            'eric'=>'eric',
            'hipt'=>'hipt',
            'inno'=>'inno',
            'ipaq'=>'ipaq',
            'java'=>'java',
            'jigs'=>'jigs',
            'kddi'=>'kddi',
            'keji'=>'keji',
            'leno'=>'leno',
            'lg-c'=>'lg-c',
            'lg-d'=>'lg-d',
            'lg-g'=>'lg-g',
            'lge-'=>'lge-',
            'maui'=>'maui',
            'maxo'=>'maxo',
            'midp'=>'midp',
            'mits'=>'mits',
            'mmef'=>'mmef',
            'mobi'=>'mobi',
            'mot-'=>'mot-',
            'moto'=>'moto',
            'mwbp'=>'mwbp',
            'nec-'=>'nec-',
            'newt'=>'newt',
            'noki'=>'noki',
            'opwv'=>'opwv',
            'palm'=>'palm',
            'pana'=>'pana',
            'pant'=>'pant',
            'pdxg'=>'pdxg',
            'phil'=>'phil',
            'play'=>'play',
            'pluc'=>'pluc',
            'port'=>'port',
            'prox'=>'prox',
            'qtek'=>'qtek',
            'qwap'=>'qwap',
            'sage'=>'sage',
            'sams'=>'sams',
            'sany'=>'sany',
            'sch-'=>'sch-',
            'sec-'=>'sec-',
            'send'=>'send',
            'seri'=>'seri',
            'sgh-'=>'sgh-',
            'shar'=>'shar',
            'sie-'=>'sie-',
            'siem'=>'siem',
            'smal'=>'smal',
            'smar'=>'smar',
            'sony'=>'sony',
            'sph-'=>'sph-',
            'symb'=>'symb',
            't-mo'=>'t-mo',
            'teli'=>'teli',
            'tim-'=>'tim-',
            'tosh'=>'tosh',
            'treo'=>'treo',
            'tsm-'=>'tsm-',
            'upg1'=>'upg1',
            'upsi'=>'upsi',
            'vk-v'=>'vk-v',
            'voda'=>'voda',
            'wap-'=>'wap-',
            'wapa'=>'wapa',
            'wapi'=>'wapi',
            'wapp'=>'wapp',
            'wapr'=>'wapr',
            'webc'=>'webc',
            'winw'=>'winw',
            'winw'=>'winw',
            'xda-'=>'xda-'
        );

        if(!empty($agents[substr($_SERVER['HTTP_USER_AGENT'], 0, 4)]))
            return true;
    }


    public function resize_modifier($filename, $width=0, $height=0, $set_watermark=false)
    {
        return ImageHelper::resize($filename, $width, $height, $set_watermark);
    }

    public function token_modifier($text)
    {
        return Simpla::$container->config->token($text);
    }

    public function url_modifier($params)
    {
        if(is_array(reset($params))) {
            return Simpla::$container->request->url(reset($params));
        }
        else {
            return Simpla::$container->request->url($params);
        }
    }

    public function plural_modifier($number, $singular, $plural1, $plural2=null)
    {
        $number = abs($number);
        if(!empty($plural2))
        {
            $p1 = $number%10;
            $p2 = $number%100;
            if($number == 0)
                return $plural1;
            if($p1==1 && !($p2>=11 && $p2<=19))
                return $singular;
            elseif($p1>=2 && $p1<=4 && !($p2>=11 && $p2<=19))
                return $plural2;
            else
                return $plural1;
        }else
        {
            if($number == 1)
                return $singular;
            else
                return $plural1;
        }

    }

    public function first_modifier($params = array())
    {
        if(!is_array($params))
            return false;
        return reset($params);
    }

    public function cut_modifier($array, $num=1)
    {
        if($num>=0)
            return array_slice($array, $num, count($array)-$num, true);
        else
            return array_slice($array, 0, count($array)+$num, true);
    }

    public function date_modifier($date, $format = null)
    {
        if(empty($date))
            $date = date("Y-m-d");
        return date(empty($format)?Simpla::$container->settings->date_format:$format, strtotime($date));
    }

    public function time_modifier($date, $format = null)
    {
        return date(empty($format)?'H:i':$format, strtotime($date));
    }

    public function api_plugin($params, &$smarty)
    {
        if(!isset($params['module']))
            return false;
        if(!isset($params['method']))
            return false;

        $module = $params['module'];
        $method = $params['method'];
        $var = $params['var'];
        unset($params['module']);
        unset($params['method']);
        unset($params['var']);
        $res = $this->$module->$method($params);
        $smarty->assign($var, $res);
    }
}