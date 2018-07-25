<?php

namespace Root\api;

/**
 * Основной класс Simpla для доступа к API Simpla
 *
 * @copyright 	2014 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 * @property Config $config
 * @property Request $request
 * @property Database $db
 * @property Settings $settings
 * @property Design $design
 * @property Products $products
 * @property Variants $variants
 * @property Categories $categories
 * @property Brands $brands
 * @property Features $features
 * @property Money $money
 * @property Pages $pages
 * @property Blog $blog
 * @property Cart $cart
 * @property Image $image
 * @property Delivery $delivery
 * @property Payment $payment
 * @property Orders $orders
 * @property Users $users
 * @property Coupons $coupons
 * @property Comments $comments
 * @property Feedbacks $feedbacks
 * @property Notify $notify
 * @property Managers $managers
 *
 */
class Simpla
{
	// Свойства - Классы API
	private $classes = array(
		'config'     => Config::class,
		'request'    => Request::class,
		'db'         => Database::class,
		'settings'   => Settings::class,
		'design'     => Design::class,
		'products'   => Products::class,
		'variants'   => Variants::class,
		'categories' => Categories::class,
		'brands'     => Brands::class,
		'features'   => Features::class,
		'money'      => Money::class,
		'pages'      => Pages::class,
		'blog'       => Blog::class,
		'cart'       => Cart::class,
		'image'      => Image::class,
		'delivery'   => Delivery::class,
		'payment'    => Payment::class,
		'orders'     => Orders::class,
		'users'      => Users::class,
		'coupons'    => Coupons::class,
		'comments'   => Comments::class,
		'feedbacks'  => Feedbacks::class,
		'notify'     => Notify::class,
		'managers'   => Managers::class
	);
	
	// Созданные объекты
	private static $objects = array();
	
	/**
	 * Конструктор оставим пустым, но определим его на случай обращения parent::__construct() в классах API
	 */
	public function __construct()
	{
		//error_reporting(E_ALL & !E_STRICT);
	}

	/**
	 * Магический метод, создает нужный объект API
	 */
	public function __get($name)
	{
		// Если такой объект уже существует, возвращаем его
		if(isset(self::$objects[$name])) {
			return(self::$objects[$name]);
		}
		
		// Если запрошенного API не существует - ошибка
		if(!array_key_exists($name, $this->classes)) {
			return null;
		}
		
		// Определяем имя нужного класса
		$class = $this->classes[$name];

		// Сохраняем для будущих обращений к нему
		self::$objects[$name] = new $class();
		
		// Возвращаем созданный объект
		return self::$objects[$name];
	}
}