<?php

namespace PrevailExcel\Coinremitter;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

/*
 * This file is part of the Laravel Coinremitter package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Coinremitter
{
    /**
     * Issue API Key from your Coinremitter Dashboard
     * @var string
     */
    protected $coin;

    /**
     * Issue API Key from your Coinremitter Dashboard
     * @var string
     */
    protected $version;

    /**
     * Instance of HTTP Client
     * @var Http
     */
    protected $client;

    /**
     *  Response from requests made to Coinremitter
     * @var mixed
     */
    protected $response;

    /**
     * Coinremitter API base Url
     * @var string
     */
    protected $baseUrl;

    public function __construct(string $coin = null)
    {
        $this->setCoin($coin);
        $this->setRequestOptions();
    }

    /**
     * initialize Coin
     */
    private function setCoin($coin = null)
    {
        if (!$coin) {
            if (request()->coin)
                $coin = request()->coin;
            else
                throw new IsNullException("No coin detected");
        }
        $coin = strtoupper($coin);
        $this->coin = $coin;
    }

    /**
     * Set options for making the Http request
     */
    private function setRequestOptions()
    {
        $this->version = Config::get('coinremitter.version', 'v3');
        $this->baseUrl = Config::get('coinremitter.url', 'https://coinremitter.com/api/') .
            $this->version . '/' . $this->coin;
        $userAgent = 'PECR@' . $this->version . ',laravel plugin@' . '1.0.0';

        $this->client = Http::withHeaders([
            'User-Agent' => $userAgent,
        ]);
    }

    /**
     * @param string $relativeUrl
     * @param string $method
     * @param array $body
     * @return Coinremitter
     * @throws IsNullException
     */
    private function setHttpResponse($relativeUrl, $method, $body = [])
    {
        if (is_null($method))
            throw new IsNullException("Empty method not allowed");


        $credentials = config('coinremitter.coins.' . $this->coin, []);

        if (count($credentials) > 0)
            $body = array_merge($credentials, $body);
        else {
            throw new IsNullException('Please set API_KEY and PASSWORD for ' . $this->coin);
        }
        try {
            $this->response = $this->client->{strtolower($method)}(
                $this->baseUrl . $relativeUrl,
                $body
            );
        } catch (\Throwable $th) {
            dd($th);
        }

        return $this;
    }

    /**
     * Get the whole response from a get operation
     * @return array
     */
    private function getResponse()
    {
        return json_decode($this->response->getBody(), true);
    }

    /**
     * Get balance of specified coin.
     * @return array
     */
    public function balance(): array
    {
        return $this->setHttpResponse("/get-balance", 'POST')->getResponse();
    }

    /**
     * Get new address for specified coin.
     * @param null|string $label
     * @return array
     */
    public function createAddress(string $label = null): array
    {
        if (!$label)
            $label = request()->label ?? null;
        return $this->setHttpResponse("/get-new-address", 'POST')->getResponse();
    }

    /**
     * Validate address for specified coin.
     * @param null|string $address
     * @return array
     */
    public function validateAddress(string $address = null): array
    {
        if (!$address)
            $address = request()->address ?? null;
        return $this->setHttpResponse("/validate-address", 'POST', ['address' => $address])->getResponse();
    }

    /**
     * Withdraw coin to specific address.
     * to_address and amount in array to withdraw amount.
     * @param array $data
     * @return array
     */
    public function withdraw(array $data = null): array
    {
        if (!$data)
            $data = [
                'to_address' => request()->address ?? null,
                'amount' => request()->amount ?? null
            ];
        return $this->setHttpResponse("/withdraw", 'POST', $data)->getResponse();
    }

    /**
     * Get transaction details of given transaction id.
     * @param null|string $id
     * @return array
     */
    public function getTransaction(string $id = null): array|null
    {
        if (!$id)
            $id =  request()->id ?? null;
        return $this->setHttpResponse("/get_transaction", 'POST', ['id' => $id])->getResponse();
    }

    /**
     * Get transaction details of given transaction address.
     * @param null|string $address
     * @return array
     */
    public function getTransactionByAddress(string $address = null): array|null
    {
        if (!$address)
            $address =  request()->address ?? null;
        return $this->setHttpResponse("/get-transaction-by-address", 'POST', ['address' => $address])->getResponse();
    }

    /**
     * Get invoice details of given invoice id.
     * @param null|string $invoice_id
     * @return array
     */
    public function getInvoice(string $invoice_id = null): array
    {
        if (!$invoice_id)
            $invoice_id =  request()->invoice_id ?? null;
        return $this->setHttpResponse("/get-invoice", 'POST', ['invoice_id' => $invoice_id])->getResponse();
    }

    /**
     * Get all coins usd rate.
     * @return array|mixed
     */
    public function getRates(): mixed
    {
        return Http::withHeaders(['User-Agent' => 'PECR@' . $this->version . ',laravel plugin@' . '1.0.0'])
            ->post(Config::get('coinremitter.url', 'https://coinremitter.com/api/') . 'get-coin-rate',)->json();
    }

    /**
     * Create invoice for deposit balance.
     * @param null|array $data 
     * @return array
     */
    public function createInvoice($data = null): array
    {
        if (!$data)
            $data = [
                'amount' => request()->amount ?? 0.0001, //required
                'name' => request()->name ?? 'random name gh',
                'currency' => request()->currency ?? null,
                'expire_time' => request()->expire_time ?? null,
                'notify_url' => request()->notify_url ?? null,
                'suceess_url' => request()->success_url ?? null,
                'fail_url' => request()->fail_url ?? null,
                'description' => request()->description ?? null,
                'custom_data1' => request()->metadata ?? null,
                'custom_data2' => null,
            ];
        return $this->setHttpResponse("/create-invoice", 'POST', $data)->getResponse();
    }

    /**
     * Create invoice and redirect user directly to the payment gateway.
     * @param null|array $data 
     * @return array
     */
    public function redirectToGateway($data = null)
    {
        if (!$data)
            $data = [
                'amount' => request()->amount ?? 0.0001,
                'name' => request()->name ?? 'name yuyu',
                'currency' => request()->currency ?? null,
                'expire_time' => request()->expire_time ?? null,
                'notify_url' => request()->notify_url ?? null,
                'suceess_url' => request()->success_url ?? null,
                'fail_url' => request()->fail_url ?? null,
                'description' => request()->description ?? null,
                'custom_data1' => request()->metadata ?? null,
                'custom_data2' => null,
            ];
        $invoice = $this->setHttpResponse("/create-invoice", 'POST', $data)->getResponse();
        if ($invoice['flag'] == 1)
            return redirect()->to($invoice['data']['url']);
        else
            return $invoice;
    }

    /**
     * Get crypto rate of given fiat_symbol and fiat_amount.
     * @param null|array $data 
     * @return array
     */
    public function getRateFromFiat(array $data = null): array
    {
        if (!$data)
            $data = [
                'fiat_symbol' => request()->fiat_symbol ?? null,
                'fiat_amount' => request()->fiat_amount ?? null
            ];
        return $this->setHttpResponse("/get-fiat-to-crypto-rate", 'POST', $data)->getResponse();
    }
}
