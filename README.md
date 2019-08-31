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
* Последний этап в установке пакета - подключение библиотек к вашему проекту.
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

* `$merchant_id` - Идентификатор вашего мерчанта PayWallet
* `$merchant_secret_key` - Секретный ключ вашего мерчанта PayWallet
* `$amount` - сумма платежа
* `$currency_code` - валюта платежа
* `$payment_system_id` - идентификатор платежной системы
* `$order_id` - идентификатор заказа в базе данных
* `$url` - ссылка на оплату

### Проверка платежа


