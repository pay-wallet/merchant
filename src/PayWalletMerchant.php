<?php

namespace PayWallet;

use GuzzleHttp\Exception\ClientException;

class PayWalletMerchant
{
    private $endpoint = 'https://pay-wallet.ru/';
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;
    private $merchant_id;
    private $merchant_secret_key;

    public function __construct($merchant_id, $merchant_secret_key)
    {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $this->endpoint,
        ]);
        $this->merchant_id = $merchant_id;
        $this->merchant_secret_key = $merchant_secret_key;
    }

    /**
     * @param $amount
     * @param $currency_code
     * @param $payment_system_id
     * @param $order_id
     * @return mixed
     * @throws PayWalletMerchantException
     */
    public function payment($amount, $currency_code, $payment_system_id, $order_id)
    {
        try {
            $response = $this->client->post('api/merchant/payment', [
                'json' => [
                    'amount' => $amount,
                    'currency_code' => $currency_code,
                    'payment_system_id' => $payment_system_id,
                    'merchant_id' => $this->merchant_id,
                    'order_id' => $order_id
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true)['redirect_url'];
        } catch (ClientException $exception) {
            throw new PayWalletMerchantException($exception->getMessage());
        }

    }

    /**
     * @param $amount
     * @param $currency_code
     * @param $payment_system_id
     * @param $order_id
     * @return mixed
     */
    public function paymentComplete($currency_code, $payment_system_id, $order_id)
    {

        if (!$this->checkPost([
            'sign_hash', 'status', 'order_id'
        ])) {
            return false;
        }

        if ($_REQUEST['sign_hash'] != $this->calcSign(
                $currency_code, $payment_system_id, $order_id,
                $this->merchant_id, $this->merchant_secret_key
            )
            or $_REQUEST['status'] != 'SUCCESS') {
            return false;
        }

        return $_REQUEST['order_id'];
    }

    private function calcSign($currency_code, $payment_system_id, $order_id, $merchant_id, $secret_key)
    {
        $data = [strtoupper($currency_code), intval($payment_system_id), intval($order_id), intval($merchant_id), md5($secret_key)];
        return md5(implode(',', $data));
    }

    /**
     * @param array $params
     * @return bool
     */
    private function checkPost(array $params)
    {
        foreach ($params as $param) {
            if (!isset($_REQUEST[$param])) {
                return false;
            }
        }

        return true;
    }

}
