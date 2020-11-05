<?php

use Api\components\cart\base\Purchase;
use Api\entities\shop\catalog\Variant;
use Root\api\components\cart\base\Cart;
use Root\api\components\design\smarty\AppExtension;
use Root\api\components\design\smarty\RegisterSmartyExtension;
use Root\api\Simpla;

session_start();

require_once __DIR__ . '/../bootstrap/app.php';

/** @var Simpla $simpla */
$simpla = Simpla::$container;

RegisterSmartyExtension::extensions([
    AppExtension::class,
]);

/** @var Cart $cart */
$cart = simpla('cart');

$cart->add_purchase(new Purchase(
    Variant::findOrFail($simpla->request->get('variant')),
    (int) $simpla->request->get('amount')
));

$cart->save();

$currencies = $simpla->money->get_currencies(['enabled' => 1]);

if (isset($_SESSION['currency_id'])) {
    $currency = $simpla->money->get_currency($_SESSION['currency_id']);
} else {
    $currency = reset($currencies);
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($simpla->design->fetch('cart_informer.tpl', [
    'currency' => $currency,
    'cart'     => $cart,
]));
