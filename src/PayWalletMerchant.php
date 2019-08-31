<?php

namespace PayWallet;

use Psr\Http\Message\ResponseInterface;

class PayWalletMerchant
{
    private $endpoint = 'https://pay-wallet.ru/api';
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
        $response = $this->parseResponse($this->client->post('/merchant/payment', [
            'amount' => $amount,
            'currency_code' => $currency_code,
            'payment_system_id' => $payment_system_id,
            'merchant_id' => $this->merchant_id,
            'order_id' => $order_id
        ]));

        return $response['redirect_url'];
    }

    /**
     * @param $amount
     * @param $currency_code
     * @param $payment_system_id
     * @param $order_id
     * @return mixed
     */
    public function paymentComplete($amount, $currency_code, $payment_system_id, $order_id)
    {
        if (!$this->checkPost([
            'amount', 'currency_code',
            'desc', 'status', 'order_id', 'payment_system_id',
            'payer_account', 'transaction_id', 'sign_hash'
        ])) {
            return false;
        }

        if ($_POST['sign_hash'] != $this->calcSign(
                $amount,
                $currency_code,
                $payment_system_id,
                $order_id,
                $this->merchant_id,
                $this->merchant_secret_key
            )) {
            return false;
        }

        return $_POST['order_id'];
    }

    /**
     * @param ResponseInterface $response
     * @return mixed
     * @throws PayWalletMerchantException
     */
    private function parseResponse(ResponseInterface $response)
    {
        $data = \GuzzleHttp\json_decode($response, true);
        if ($response->getStatusCode() != 200) {
            $message = isset($data['message']) ? $data['message'] : 'Server Error. Code: ' . $response->getStatusCode();
            throw new PayWalletMerchantException($message, $response->getStatusCode());
        }
        return $data;
    }

    private function calcSign($amount, $currency_code, $payment_system_id, $order_id, $merchant_id, $secret_key)
    {
        return md5(implode(',', [$amount, $currency_code, $payment_system_id, $order_id, $merchant_id, $secret_key]));
    }

    /**
     * @param array $params
     * @return bool
     */
    private function checkPost(array $params)
    {
        foreach ($params as $param) {
            if (!isset($_POST[$param])) {
                return false;
            }
        }

        return true;
    }

}
