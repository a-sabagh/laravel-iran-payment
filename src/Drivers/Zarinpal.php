<?php

namespace IRPayment\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use IRPayment\Contracts\PaymentDriver;
use IRPayment\Exceptions\PaymentDriverException;
use IRPayment\Models\Payment;

class Zarinpal implements PaymentDriver
{
    public function __construct(
        protected Request $request,
        protected Collection $config
    ) {}

    public function title(): string
    {
        return $this->config->get('title', Lang::get('irpayment::drivers.zarinpal'));
    }

    public function description(): string
    {
        return $this->config->get('description', Lang::get('irpayment::drivers.zarinpal'));
    }

    protected function CallbackUrl()
    {
        return route('irpayment::payment.verify');
    }

    public function process(Payment $payment): void
    {
        [
            'authority' => $authority,
        ] = $data = $this->request($payment);

        $payment->update($data);

        $this->redirect($authority);
    }

    public function redirect(string $authority)
    {
        $url = "https://payment.zarinpal.com/pg/StartPay/{$authority}";
         
        return redirect($url);
    }

    protected function request(Payment $payment): array
    {
        $url = 'https://api.zarinpal.com/pg/v4/payment/request.json';

        $data = [
            'merchant_id' => $this->config->get('merchant_id'),
            'currency' => $this->config->get('currency', 'IRT'),
            'amount' => $payment->amount,
            'description' => $payment->description,
            'callback_url' => $this->callbackUrl(),
        ];

        $response = $this->request->asJson()->acceptJson()->post($url, $data);

        if ($response['data']['code'] != 100) {
            $code = $response['data']['code'];
            $message = Lang::get("irpayment::drivers.{$code}");

            throw new PaymentDriverException($code, $message);
        }

        return $response['data'];
    }

    protected function startPay() {}

    public function verify(Payment $payment) {}
}
