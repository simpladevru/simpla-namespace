<?PHP

use Mpociot\Pipeline\Pipeline;
use Root\api\components\pipeline\CallableMiddlewareDecorator;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'bootstrap/app.php';

// configure app

$middleware = [
    \Root\middleware\TimerMiddleware::class,
    \Root\middleware\SessionStartMiddleware::class,
    \Root\middleware\LogoutMiddleware::class,
    \Root\middleware\RegisterExtensionsSmartyFromFrontend::class,
    \Root\middleware\CheckLicenseMiddleware::class,
    new CallableMiddlewareDecorator(function() {
        return (new \Root\view\IndexView())->fetch();
    }),
];

// run

(new Pipeline)->send([])->through($middleware)->then(function(){
    throw new Exception();
});






