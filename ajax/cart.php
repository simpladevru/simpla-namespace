<?php

use Api\components\cart\base\Purchase;
use Api\entities\shop\catalog\Variant;
use Root\api\Simpla;

session_start();

require_once __DIR__ . '/../bootstrap/app.php';
$simpla = Simpla::$container;

$simpla->cart->add_purchase(new Purchase(
    Variant::findOrFail($simpla->request->get('variant')),
    (int) $simpla->request->get('amount')
));

$simpla->cart->save();

$currencies = $simpla->money->get_currencies(['enabled' => 1]);

if (isset($_SESSION['currency_id'])) {
    $currency = $simpla->money->get_currency($_SESSION['currency_id']);
} else {
    $currency = reset($currencies);
}

$simpla->design->assign('currency', $currency);
$simpla->design->assign('cart', $simpla->cart);

$result = $simpla->design->fetch('cart_informer.tpl');

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($result);
