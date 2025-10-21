<?php

namespace IRPayment\Drivers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use IRPayment\Contracts\OnlineChannel;
use IRPayment\Contracts\PaymentDriver;
use IRPayment\DTO\ProcessResponseValueObject;
use IRPayment\DTO\VerificationValueObject;
use IRPayment\Enums\PaymentChannel;
use IRPayment\Exceptions\PaymentDriverException;
use IRPayment\Models\Payment;

class Payping implements OnlineChannel, PaymentDriver
{
    public function __construct(
        protected Collection $config
    ) {}

    public function CallbackUrl(): string
    {
        return route('irpayment.payment.payping.verify');
    }

    public function process(Payment $payment)
    {
        $paymentCode = $this->pay($payment);

        $redirectResponseUrl = "https://api.payping.ir/v3/pay/start/{$paymentCode}";

        $responseVO = new ProcessResponseValueObject(
            redirectResponseUrl: $redirectResponseUrl,
            authorityKey: $paymentCode
        );

        return $responseVO;
    }

    protected function pay(Payment $payment): int
    {
        $url = 'https://api.payping.ir/v3/pay';

        $token = $this->config->get('token');

        $args = [
            'amount' => $payment->amount,
            'payerIdentity' => $payment->phone,
            'description' => $payment->description,
            'returnUrl' => $this->CallbackUrl(),
            'clientRefId' => $payment->id,
        ];

        $httpResponse = Http::asJson()
            ->acceptJson()
            ->withToken($token)
            ->post($url, $args);

        $response = $httpResponse->json();

        if (empty($response['paymentCode'])) {
            $code = data_get($response, 'metaData.code', 500);
            $message = data_get($response, 'metaData.errors.0', __('Error on payping driver'));

            throw new PaymentDriverException($message, $code);
        }

        return $response['paymentCode'];
    }

    public function verify(int $amount, int $paymentRefId, int $paymentCode): VerificationValueObject
    {
        $url = 'https://api.zarinpal.com/pg/v4/payment/verify.json';

        $token = $this->config->get('token');

        $args = [
            'paymentRefId' => $paymentRefId,
            'paymentCode' => $paymentCode,
            'amount' => $amount,
        ];

        $httpResponse = Http::asJson()
            ->acceptJson()
            ->withToken($token)
            ->post($url, $args);

        $responseBody = $httpResponse->json();
        $responseCode = $httpResponse->status();
        $code = $httpResponse->assertOk() ? 100 : $responseCode;
        $message = trans("irpayment::messages.payping.{$responseCode}");

        return new VerificationValueObject(
            code: $code,
            message: $message,
            cardHash: data_get($responseBody, 'cardNumber'),
            cardMask: data_get($responseBody, 'cardHashPan'),
            referenceId: data_get($responseBody, 'paymentRefId'),
        );
    }

    public function title(): string
    {
        return $this->config->get('title', trans('irpayment::drivers.payping.title'));
    }

    public function description(): string
    {
        return $this->config->get('description', trans('irpayment::drivers.payping.description'));
    }

    public function channel(): PaymentChannel
    {
        return PaymentChannel::ONLINE;
    }
}
