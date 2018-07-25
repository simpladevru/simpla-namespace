<?PHP

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Simpla CMS
 *
 * @copyright 	2011 Denis Pikusov
 * @link 		http://simp.la
 * @author 		Denis Pikusov
 *
 */

// Засекаем время
$time_start = microtime(true);

session_start();

require_once 'bootstrap/app.php';

if(isset($_GET['logout']))
{
    header('WWW-Authenticate: Basic realm="Simpla CMS"');
    header('HTTP/1.0 401 Unauthorized');
	unset($_SESSION['admin']);
}

(new Root\view\IndexView())->fetch();

$p=11; $g=2; $x=7; $r = ''; $s = $x;
$bs = explode(' ', \Root\api\Simpla::$app->config->license);
foreach($bs as $bl){
	for($i=0, $m=''; $i<strlen($bl)&&isset($bl[$i+1]); $i+=2){
		$a = base_convert($bl[$i], 36, 10)-($i/2+$s)%26;
		$b = base_convert($bl[$i+1], 36, 10)-($i/2+$s)%25;
		$m .= ($b * (pow($a,$p-$x-1) )) % $p;}
	$m = base_convert($m, 10, 16); $s+=$x;
	for ($a=0; $a<strlen($m); $a+=2) $r .= @chr(hexdec($m{$a}.$m{($a+1)}));}

@list($l->domains, $l->expiration, $l->comment) = explode('#', $r, 3);

$l->domains = array_map(function($domain) {
    return str_replace([$_SERVER['SERVER_PORT'], ':'], '', $domain);
}, explode(',', $l->domains));

$h = str_replace([$_SERVER['SERVER_PORT'], ':'], '', getenv("HTTP_HOST"));
if(substr($h, 0, 4) == 'www.') $h = substr($h, 4);
if((!in_array($h, $l->domains) || (strtotime($l->expiration)<time() && $l->expiration!='*')))
{
	print "<div style='text-align:center; font-size:22px; height:100px;'>Лицензия недействительна<br><a href='http://simplacms.ru'>Скрипт интернет-магазина Simpla</a></div>";
}

// Отладочная информация
if(1)
{
	print "<!--\r\n";
	$time_end = microtime(true);
	$exec_time = $time_end-$time_start;
  
  	if(function_exists('memory_get_peak_usage')) {
		print "memory peak usage: ".memory_get_peak_usage()." bytes\r\n";
    }
	print "page generation time: ".$exec_time." seconds\r\n";  
	print "-->";
}
