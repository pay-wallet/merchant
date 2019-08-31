# PayWallet Merchant (https://pay-wallet.ru)

Этот пакет позволяет реализовать прием платежей на вашем сайте.

# Интеграция

## Установка composer
Прежде всего потребуется composer - пакетный менеджер уровня приложений для языка программирования PHP.
  
Подробная инструкция на сайте - https://getcomposer.org/download/.
___ 
## Установка пакета
После установки composer выполните следующие операции:
* Создайте файл `composer.json` в корне проекта
* Добавьте в него содержимое:
```json
{
       "require": {
           "pay-wallet/merchant": "dev-master"
       },
       "repositories": [
           {
               "type": "git",
               "url": "git@github.com:pay-wallet/merchant.git"
           }
       ]
}
```
Если у вас уже используется composer, то просто обновите секции `require` и `repositories`.
* Выполните `composer install`
* Если все прошло успешно, в корне проекта появится директория `vendor`
* Последний этап в установке пакета - загрузка библиотек к вашему проекту.  
Для этого добавьте в ваш скрипт `index.php` строку `require __DIR__ . '/vendor/autoload.php;'`.
`
___ 
## Настройка приема платежей
### Оплата
Скрипт позволит вам быстро сформировать ссылку на оплату, вы должны передать ее клиенту либо перенаправить его автоматически.
Пример формирования оплаты выглядит так:
```php
<?php
require __DIR__ . '/vendor/autoload.php';

$merchant_id = 000000;
$merchant_secret_key = 'XXXXXX';
$amount = 100.0;
$currency_code = 'RUB';
$payment_system_id = 1;
$order_id = rand(1,99999);

$merchant = new \PayWallet\PayWalletMerchant($merchant_id,$merchant_secret_key);


$url = $merchant->payment($amount,$currency_code,$payment_system_id,$order_id); 
```

* `$merchant_id` - идентификатор вашего мерчанта PayWallet
* `$merchant_secret_key` - секретный ключ вашего мерчанта PayWallet
* `$amount` - сумма платежа
* `$currency_code` - валюта платежа
* `$payment_system_id` - идентификатор платежной системы
* `$order_id` - числовой идентификатор заказа в базе данных
* `$url` - ссылка на оплату

### Проверка платежа

Для проверки платежей вам необходимо предварительно сохранить следующие данные в базе данных:
* Сумма. Тип float или int. 
* Код валюты большими буквами. Например USD,RUB,BTC...
* Идентификатор платежной системы


Перед скриптом обработки платежа необходимо получить данные из вашей БД по идентификатору платежа.
Идентификатор можно получить из массива `$_POST['order_id']`.  
Пример скрипта:
```php
<?php
require __DIR__ . '/vendor/autoload.php';

$merchant = new \PayWallet\PayWalletMerchant(000000, "XXXXXX");
$amount = 100.0;
$currency_code = 'RUB';
$payment_system_id = 1;
$order_id = rand(1, 99999);

$is_success = $merchant->paymentComplete($amount, $currency_code, $payment_system_id, $order_id);

if ($is_success === true) {
    // ваш код обработки успешной оплаты
}else{
    // ваш код обработки неуспешной оплаты
}
```

* `$merchant_id` - идентификатор вашего мерчанта PayWallet
* `$merchant_secret_key` - секретный ключ вашего мерчанта PayWallet
* `$amount` - сумма платежа
* `$currency_code` - валюта платежа
* `$payment_system_id` - идентификатор платежной системы
* `$order_id` - числовой идентификатор заказа на вашем сайте


*ВНИМАНИЕ!*
СТРОГО СОБЛЮДАЙТЕ ТИПЫ ДАННЫХ.   
Метод `paymentComplete` принимает следующие типы данных:
* `$amount` - тип float или int
* `$currency_code` - строка
* `$payment_system_id` - тип int
* `$order_id` - тип int

*ЕСЛИ ТИПЫ НЕ БУДУТ СОБЛЮДЕНЫ ТО ПЛАТЕЖ НЕ ПРОЙДЕТ ПРОВЕРКУ*


___

По вопросам обращайтесь на pay-wallet.ru@yandex.ru
