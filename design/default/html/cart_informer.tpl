{* Информера корзины (отдаётся аяксом) *}

{if $cart->has_purchases()}
	В <a href="./cart/">корзине</a>
	{$cart->get_total_products()} {$cart->get_total_products()|plural:'товар':'товаров':'товара'}
	на {$cart->get_total_price()|convert} {$currency->sign|escape}
{else}
	Корзина пуста
{/if}
