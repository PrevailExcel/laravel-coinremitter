# laravel-coinremitter

[![Latest Stable Version](https://poser.pugx.org/prevailexcel/laravel-coinremitter/v/stable.svg)](https://packagist.org/packages/prevailexcel/laravel-coinremitter)
[![License](https://poser.pugx.org/prevailexcel/laravel-coinremitter/license.svg)](LICENSE.md)

> A Laravel Package for working with Coinremitter seamlessly 

## Installation

[PHP](https://php.net) 5.4+ or [HHVM](http://hhvm.com) 3.3+, and [Composer](https://getcomposer.org) are required.

To get the latest version of Laravel Coinremitter, simply require it

```bash
composer require prevailexcel/laravel-coinremitter
```

Or add the following line to the require block of your `composer.json` file.

```
"prevailexcel/laravel-coinremitter": "1.0.*"
```

You'll then need to run `composer install` or `composer update` to download it and have the autoloader updated.


Once Laravel Coinremitter is installed, you need to register the service provider. Open up `config/app.php` and add the following to the `providers` key.

> If you use **Laravel >= 5.5** you can skip this step and go to [**`configuration`**](https://github.com/PrevailExcel/laravel-coinremitter#configuration)

```php
'providers' => [
    ...
    PrevailExcel\Coinremitter\CoinremitterServiceProvider::class,
    ...
]
```

* `PrevailExcel\Coinremitter\CoinremitterServiceProvider::class`

Also, register the Facade like so:

```php
'aliases' => [
    ...
    'Coinremitter' => PrevailExcel\Coinremitter\Facades\Coinremitter::class,
    ...
]
```

## Configuration

You can publish the configuration file using this command:

```bash
php artisan vendor:publish --provider="PrevailExcel\Coinremitter\CoinremitterServiceProvider"
```

A configuration-file named `coinremitter.php` with some sensible defaults will be placed in your `config` directory:

```php
<?php

return [

    'coins' => [

        'BTC' => [
            'api_key' => 'API_KEY_FROM_WEBSITE',
            'password' => 'PASSWORD',
        ],

        'LTC' => [
            'api_key' => 'API_KEY_FROM_WEBSITE',
            'password' => 'PASSWORD',
        ],
    ],
];
```



## General payment flow

Though there are multiple ways to pay an order, most payment gateways expect you to follow the following flow in your checkout process:

### 1. The customer is redirected to the payment provider
After the customer has gone through the checkout process and is ready to pay, the customer must be redirected to the site of the payment provider.

The redirection is accomplished by submitting a form with some hidden fields. The form must send a POST request to the site of the payment provider. The hidden fields minimally specify the amount that must be paid, the order id.

### 2. The customer pays on the site of the payment provider
The customer arrives on the site of the payment provider and gets to choose a payment method. All steps necessary to pay the order are taken care of by the payment provider.

### 3. The customer gets redirected back to your site
After having paid the order the customer is redirected back. In the redirection request to the shop-site some values are returned. The values are usually the order id, a payment result.



## Usage

>When using Facade or Helper, `request()->coin = 'COIN'` must be used to pass the coin. 
If not, use 

```php
use PrevailExcel\Coinremitter\Coinremitter;

$coinremitter = new Coinremitter('COIN');
$coinremitter->balance();
```
### 1. Redirect User to GateWay

```php
// Laravel 5.1.17 and above
Route::post('/pay', 'PaymentController@redirectToGateway')->name('pay');
```

OR

```php
Route::post('/pay', [
    'uses' => 'PaymentController@redirectToGateway',
    'as' => 'pay'
]);
```
OR

```php
// Laravel 8 & 9
Route::post('/pay', [App\Http\Controllers\PaymentController::class, 'redirectToGateway'])->name('pay');
```


```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use PrevailExcel\Coinremitter\Facades\Coinremitter;

class PaymentController extends Controller
{
    
    public function __construct()
    {
        if(!request()->coin)
            request()->coin = 'BTC'; //set a default coin
    }

    /**
     * Collect Order data and Redirect user to Payment gateway
     */
    public function redirectToGateway()
    {
        try{
            //You should collect the details you need from a form

           return Coinremitter::redirectToGateway();

           // or alternatively use the helper
           return coinremitter()->redirectToGateway();
            
        }catch(\Exception $e) {
            return Redirect::back()->withMessage(['msg'=>"There's an error in the data", 'type'=>'error']);
        }        
    }
}
```


```php
/**
 *  In the case where you need to pass the data from your 
 *  controller instead of a form
 *  Make sure to send:
 *  required: amount, currency
 *  optionally: name, expire_time, notify_url, suceess_url, description, custom_data1,custom_data2'
 *  e.g:
 *  
 */
$data = array(
            'amount' => 500, //required
            'name' => 'random-name', //Name accepts only numbers, letters, hyphens.
            'currency' => 'usd',
            'expire_time' => '20', //optional, invoice will expire in 20 minutes. 
            'notify_url'=>'https://yourdomain.com/notify-url', //optional,url on which you wants to receive notification
            'fail_url' => 'https://yourdomain.com/fail-url', //optional,url on which user will be redirect if user cancel invoice,
            'suceess_url' => 'https://yourdomain.com/success-url', //optional,url on which user will be redirect when invoice paid,    
            'description' => '',
            'custom_data1' => '',
            'custom_data2' => '',
        );

return Coinremitter::redirectToGateway($data);

// or alternatively use the helper
return coinremitter()->redirectToGateway($data);

```

### 2. If you want to use your own UI 
Without redirecting User to Coinremitter Payment page, you use `Coinremitter::createInvoice()`like shown below.

```php
// Laravel 5.1.17 and above
Route::post('/get-invoice', 'PaymentController@createCryptoPayment')->name('get.invoice');
```

OR

```php
Route::post('/get-invoice', [
    'uses' => 'PaymentController@createCryptoPayment',
    'as' => 'get.invoice'
]);
```
OR

```php
// Laravel 8 & 9
Route::post('/get-invoice', [App\Http\Controllers\PaymentController::class, 'createCryptoPayment'])->name('get.invoice');
```


```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use PrevailExcel\Coinremitter\Facades\Coinremitter;

class PaymentController extends Controller
{
    
    public function __construct()
    {
        if(!request()->coin)
            request()->coin = 'BTC'; //set a default coin
    }

    /**
     * Collect Order data and Redirect user to Payment gateway
     */
    public function createCryptoPayment()
    {
        try{
            //You should collect the details you need from a form

            $invoiceDetails = Coinremitter::createInvoice();

            // or alternatively use the helper
            $invoiceDetails = coinremitter()->createInvoice();


            dd($invoiceDetails);
            // Now you have the payment details,
            // you can store the authorization_code in your db to allow for recurrent subscriptions
            // you can then redirect or do whatever you want

            
        }catch(\Exception $e) {
            return Redirect::back()->withMessage(['msg'=>"There's an error in the data", 'type'=>'error']);
        }        
    }
}
```


```php
/**
 *  In the case where you need to pass the data from your 
 *  controller instead of a form
 *  Make sure to send:
 *  required: amount, currency
 *  optionally: name, expire_time, notify_url, suceess_url, description, custom_data1,custom_data2'
 *  e.g:
 *  
 */
$data = array(
            'amount' => 500, //required
            'name' => 'random-name', //Name accepts only numbers, letters, hyphens.
            'currency' => 'usd',
            'expire_time' => '20', //optional, invoice will expire in 20 minutes. 
            'notify_url'=>'https://yourdomain.com/notify-url', //optional,url on which you wants to receive notification
            'fail_url' => 'https://yourdomain.com/fail-url', //optional,url on which user will be redirect if user cancel invoice,
            'suceess_url' => 'https://yourdomain.com/success-url', //optional,url on which user will be redirect when invoice paid,    
            'description' => '',
            'custom_data1' => '',
            'custom_data2' => '',
        );

$invoiceDetails = Coinremitter::createInvoice($data);

// or alternatively use the helper
$invoiceDetails = coinremitter()->createInvoice($data);


dd($invoiceDetails);
// Now you have the payment details,
// you can store the authorization_code in your db to allow for recurrent subscriptions
// you can then redirect or do whatever you want

```




Some fluent methods this package provides are listed here.
```php

/**
 * This is the method to create an inoice. You need to provide your data as an array.
 * @returns array
 */
Coinremitter::createInvoice();

/**
 * Alternatively, use the helper.
 */
coinremitter()->createInvoice();


/**
 * This is the method to create an inoice and redirct user to payment gateway.
 * @returns array
 */
Coinremitter::redirectToGateway();

/**
 * Alternatively, use the helper.
 */
coinremitter()->redirectToGateway();


/**
 * Get balance of specified coin.
 * @returns array
 */
Coinremitter::balance();

/**
 * Alternatively, use the helper.
 */
coinremitter()->balance();


/**
 * Get crypto rate of given fiat_symbol and fiat_amount
 * @returns array
 */
Coinremitter::getRateFromFiat();

/**
 * Alternatively, use the helper.
 */
coinremitter()->getRateFromFiat();


/**
 * Get invoice details of given invoice id
 * @returns array
 */
Coinremitter::getInvoice()

/**
 * Alternatively, use the helper.
 */
coinremitter()->getInvoice();


/**
 * Get transaction details of given transaction address.
 */
Coinremitter::getTransactionByAddress();

/**
 * Alternatively, use the helper.
 */
coinremitter()->getTransactionByAddress();


/**
 *   Get transaction details of given transaction id.
 */
Coinremitter::getTransaction();

/**
 * Alternatively, use the helper.
 */
coinremitter()->getTransaction();


/**
 * Withdraw coin to specific address.
 * @returns array
 */
Coinremitter::withdraw();
/**
 * Alternatively, use the helper.
 */
coinremitter()->withdraw();


/**
 * Get new address for specified coin
 * @returns array
 */
Coinremitter::createAddress();
/**
 * Alternatively, use the helper.
 */
coinremitter()->createAddress();


/**
 * Validate address for specified coin.
 * @returns array
 */
Coinremitter::validateAddress();
/**
 * Alternatively, use the helper.
 */
coinremitter()->validateAddress();


/**
 *  Get all coins usd rate.
 * @returns array
 */
Coinremitter::getRates();
/**
 * Alternatively, use the helper.
 */
coinremitter()->getRates();
```

A sample form will look like so:

```html
<form method="POST" action="{{ route('pay') }}" accept-charset="UTF-8" class="form-horizontal" role="form">
@csrf
    <div class="row" style="margin-bottom:50px;">
        <div class="col-md-8 col-md-offset-2">
            <p>
                <div>
                    Deluxe Package
                    0.01 BTC
                </div>
            </p>
            <input type="hidden" name="amount" value="1.5"> {{-- required --}}
            <input type="hidden" name="coin" value="BNB"> {{-- required -- Make sure you have set up your BTC wallet and have added it in your config file--}}
            <input type="hidden" name="name" value="username">
            <input type="hidden" name="metadata" value="email" > {{-- For other necessary things you want to add to your payload. it is optional though --}}
            
            <p>
                <button class="btn btn-success btn-lg btn-block" type="submit" value="Pay Now!">
                    <i class="fa fa-plus-circle fa-lg"></i> Pay Now!
                </button>
            </p>
        </div>
    </div>
</form>
```

## Contributing

Please feel free to fork this package and contribute by submitting a pull request to enhance the functionalities.

## How can I thank you?

Why not star the github repo? I'd love the attention! Why not share the link for this repository on Twitter or HackerNews? Spread the word!

Don't forget to [follow me on twitter](https://twitter.com/EjimaduPrevail)!
Also check out my page on medium to catch articles and tutorials on Laravel [follow me on medium](https://medium.com/@prevailexcellent)!

Thanks!
Chimeremeze Prevail Ejimadu.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
