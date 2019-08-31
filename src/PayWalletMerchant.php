<?php

namespace PayWallet;

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
     * @param $code
     * @return \Psr\Http\Message\StreamInterface
     * @throws PayWalletException
     */
    private function getCurrencyByCode($code)
    {
        try {
            return $this->parseReponse(
                $this->client->get("/currency/by-code/$code")
            );
        } catch (PayWalletException $exception) {
            switch ($exception->getCode()) {
                case 404:
                    throw new PayWalletException('Валюта не найдена', $exception->getCode());
                    break;
                default:
                    throw new PayWalletException($exception->getMessage(), $exception->getCode());
            }
        }
    }

    /**
     * @param $amount
     * @param $currency_code
     * @param $payment_system_id
     * @return mixed
     * @throws PayWalletException
     */
    public function payment($amount, $currency_code, $payment_system_id, $order_id)
    {
        $currency = $this->getCurrencyByCode($currency_code);
        $response = $this->parseReponse($this->client->post('/merchant/payment', [
            'amount' => $amount,
            'currency_id' => $currency['id'],
            'payment_system_id' => $payment_system_id,
            'merchant_id' => $this->merchant_id,
        ]));

        return $response['redirect_url'];
    }

    /**
     * @param $successCallback
     * @param $failCallback
     * @return mixed
     * @throws PayWalletException
     */
    public function paymentComplete($successCallback, $failCallback)
    {
        $this->checkPost([
            'amount', 'currency_code', 'currency_id',
            'desc', 'status', 'order_id', 'payment_system_id',
            'payer_account', 'transaction_id', 'sign_hash'
        ]);

        if ($_POST['sign_hash'] == $this->calcSign(
                $_POST['amount'],
                $_POST['currency_id'],
                $_POST['payment_system_id'],
                $this->merchant_id,
                $this->merchant_secret_key
            )) {
            return $successCallback($_POST);
        } else {
            return $failCallback($_POST);
        }
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return mixed
     * @throws PayWalletException
     */
    private function parseReponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $data = \GuzzleHttp\json_decode($response, true);
        if ($response->getStatusCode() != 200) {
            $message = isset($data['message']) ? $data['message'] : 'Server Error. Code: ' . $response->getStatusCode();
            throw new PayWalletException($message, $response->getStatusCode());
        }
        return $data;
    }

    private function calcSign($amount, $currency_id, $payment_system_id, $merchant_id, $secret_key)
    {
        return md5(implode(',', [$amount, $currency_id, $payment_system_id, $merchant_id, $secret_key]));
    }

    /**
     * @param array $params
     * @throws PayWalletException
     */
    private function checkPost(array $params)
    {
        foreach ($params as $param) {
            if (!isset($_POST[$param])) {
                throw new PayWalletException("В HTTP запросе отсутствует параметр [$param]");
            }
        }
    }

}
