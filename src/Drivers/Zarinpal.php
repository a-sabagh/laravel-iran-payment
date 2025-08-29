<?php

namespace IRPayment\Drivers;

use Illuminate\Http\Client\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use IRPayment\Contracts\PaymentDriver;
use IRPayment\Exceptions\PaymentDriverException;
use IRPayment\Models\Payment;
use IRPayment\ODT\VerificationValueObject;

class Zarinpal implements PaymentDriver
{
    public function __construct(
        protected Factory $request,
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
        return route('irpayment::payment.zarinpal.verify');
    }

    public function process(Payment $payment): void
    {
        [
            'authority' => $authorityKey,
        ] = $data = $this->request($payment);

        $payment->update($data);

        $this->startPay($authorityKey);
    }

    public function startPay(string $authorityKey)
    {
        $url = "https://payment.zarinpal.com/pg/StartPay/{$authorityKey}";

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

            throw new PaymentDriverException($message, $code);
        }

        return $response['data'];
    }

    public function verify(int $amount, string $authorityKey): VerificationValueObject
    {
        $url = 'https://api.zarinpal.com/pg/v4/payment/verify.json';

        $data = [
            'merchant_id' => $this->config->get('merchant_id'),
            'authority' => $authorityKey,
            'amount' => $amount,
        ];

        $response = $this->request->asJson()->acceptJson()->post($url, $data);

        $verificationVO = new VerificationValueObject(
            code: data_get($response, 'data.code'),
            message: data_get($response, 'data.message'),
            cardHash: data_get($response, 'data.card_hash'),
            cardMask: data_get($response, 'data.card_pan'),
            referenceId: data_get($response, 'data.ref_id'),
        );

        return $verificationVO;
    }
}
