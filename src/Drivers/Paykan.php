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

class Paykan implements OnlineChannel, PaymentDriver
{
    public function __construct(
        protected Collection $config
    ) {}

    public function CallbackUrl(): string
    {
        return route('irpayment.payment.paykan.verify');
    }

    public function process(Payment $payment)
    {
        $response = $this->request($payment);

        $paymentToken = data_get($response, 'token');

        // authority key same as reference number
        $authorityKey = data_get($response, 'ref_num');

        $redirectResponseUrl = $this->startPay($paymentToken);

        $responseVO = new ProcessResponseValueObject(
            redirectResponseUrl: $redirectResponseUrl,
            authorityKey: $authorityKey
        );

        return $responseVO;
    }

    public function request(Payment $payment)
    {
        $url = 'https://pgw.paykan.ir/api/v1/withdraw/';

        $data = [
            'merchant_id' => $this->config->get('merchant_id'),
            'amount' => $payment->amount,
            'order_id' => $payment->paymentable->id,
            'callback_url' => $this->callbackUrl(),
            'callback_method' => 'GET',
        ];

        $httpResponse = Http::asJson()->acceptJson()->post($url, $data);

        $response = $httpResponse->json();

        if (! $httpResponse->ok()) {
            $code = $httpResponse->status();

            $message = Lang::get("irpayment::messages.paykan.http.{$code}");

            throw new PaymentDriverException($message, $code);
        }

        return $response;
    }

    public function startPay(string $authorityKey)
    {
        $url = "https://pgw.paykan.ir/pgw/pay/{$authorityKey}";

        return $url;
    }

    public function verify(int $amount, array $creadentials): VerificationValueObject
    {
        $url = 'https://pgw.paykan.ir/api/v1/withdraw/verify/';

        $data = [
            'merchant_id' => $this->config->get('merchant_id'),
            'order_id' => data_get($creadentials, 'order_id'),
            'amount' => $amount,
            'tracking_code' => data_get($creadentials, 'tracking_code'),
            'ref_num' => data_get($creadentials, 'ref_num'),
        ];

        $httpResponse = Http::asJson()->acceptJson()->post($url, $data);

        $response = $httpResponse->json();

        $status = data_get($response, 'data.status', null);

        if (! $httpResponse->ok()) {
            $code = $httpResponse->status();

            $message = Lang::get("irpayment::messages.paykan.http.{$code}");

            throw new PaymentDriverException($message, $code);
        }

        $code = Lang::get("irpayment::messages.paykan.code.{$status}");
        $message = Lang::get("irpayment::messages.paykan.message.{$status}");

        return new VerificationValueObject(
            code: $code,
            message: $message,
            cardHash: data_get($response, 'data.card_no'),
            cardMask: data_get($response, 'data.hashed_card_no'),
            referenceId: data_get($response, 'data.ref_num'),
        );

        return $response;
    }

    public function title(): string
    {
        return $this->config->get('title', trans('irpayment::drivers.paykan.title'));
    }

    public function description(): string
    {
        return $this->config->get('description', trans('irpayment::drivers.paykan.description'));
    }

    public function channel(): PaymentChannel
    {
        return PaymentChannel::ONLINE;
    }
}
