<?php

namespace IRPayment\Drivers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Lang;
use IRPayment\Contracts\OnlineChannel;
use IRPayment\Contracts\PaymentDriver;
use IRPayment\DTO\ProcessResponseValueObject;
use IRPayment\DTO\VerificationValueObject;
use IRPayment\Enums\PaymentChannel;
use IRPayment\Exceptions\PaymentDriverException;
use IRPayment\Models\Payment;

class Zarinpal implements OnlineChannel, PaymentDriver
{
    public function __construct(
        protected Collection $config
    ) {}

    public function title(): string
    {
        return $this->config->get('title', Lang::get('irpayment::drivers.zarinpal'));
    }

    public function channel(): PaymentChannel
    {
        return PaymentChannel::ONLINE;
    }

    public function description(): string
    {
        return $this->config->get('description', Lang::get('irpayment::drivers.zarinpal'));
    }

    public function CallbackUrl(): string
    {
        return route('irpayment.payment.zarinpal.verify');
    }

    public function process(Payment $payment): ProcessResponseValueObject
    {
        [
            'authority' => $authorityKey,
        ] = $this->request($payment);

        $redirectResponseUrl = $this->startPay($authorityKey);

        $responseVO = new ProcessResponseValueObject(
            redirectResponseUrl: $redirectResponseUrl,
            authorityKey: $authorityKey
        );

        return $responseVO;
    }

    public function startPay(string $authorityKey)
    {
        $url = "https://payment.zarinpal.com/pg/StartPay/{$authorityKey}";

        return $url;
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

        $httpResponse = Http::asJson()->acceptJson()->post($url, $data);

        $response = $httpResponse->json();

        if (empty($response['data']) || $response['data']['code'] != 100) {
            $code = $response['errors']['code'];
            $message = Lang::get("irpayment::messages.zarinpal.{$code}");

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

        $httpResponse = Http::asJson()->acceptJson()->post($url, $data);

        $response = $httpResponse->json();

        if (! isset($response['data']['code'])) {
            $code = data_get($response, 'errors.code');
            $message = Lang::get("irpayment::messages.zarinpal.{$code}");

            return new VerificationValueObject(
                code: $code,
                message: $message,
                cardHash: null,
                cardMask: null,
                referenceId: null,
            );
        }

        $code = data_get($response, 'data.code');
        $message = Lang::get("irpayment::messages.zarinpal.{$code}");

        return new VerificationValueObject(
            code: $code,
            message: $message,
            cardHash: data_get($response, 'data.card_hash'),
            cardMask: data_get($response, 'data.card_pan'),
            referenceId: data_get($response, 'data.ref_id'),
        );
    }
}
