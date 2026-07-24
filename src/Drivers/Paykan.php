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
        public Collection $config
    ) {}

    public function CallbackUrl(): string
    {
        return route('irpayment.payment.paykan.verify');
    }

    /**
     * @see \IRPayment\Tests\PaykanDriverProcessHttpErrorTest
     * @see \IRPayment\Tests\PaykanDriverProcessTest
     */
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
        $url = 'https://pgw.paykan.app/api/v1/withdraw/';

        $data = [
            'merchant_id' => $this->config->get('merchant_id'),
            'amount' => $this->normalizeAmount($payment->amount),
            'order_id' => $this->getOrderId($payment),
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
        $url = "https://pgw.paykan.app/pgw/pay/{$authorityKey}";

        return $url;
    }

    /** @see \IRPayment\Tests\PaykanDriverVerifyTest */
    public function verify(int $amount, array $creadentials): VerificationValueObject
    {
        $url = 'https://pgw.paykan.app/api/v1/verify/';

        $data = [
            'merchant_id' => $this->config->get('merchant_id'),
            'order_id' => data_get($creadentials, 'order_id'),
            'amount' => $this->normalizeAmount($amount),
            'tracking_code' => data_get($creadentials, 'tracking_code'),
            'ref_num' => data_get($creadentials, 'ref_num'),
        ];

        $httpResponse = Http::asJson()->acceptJson()->post($url, $data);

        $response = $httpResponse->json();

        $status = data_get($response, 'status');

        if (! $httpResponse->ok()) {
            $code = $httpResponse->status();

            $message = Lang::get("irpayment::messages.paykan.http.{$code}");

            throw new PaymentDriverException($message, $code);
        }

        $code = $this->toCode($status);

        $message = Lang::get('irpayment::messages.paykan.message.'.$status);

        return new VerificationValueObject(
            code: $code,
            message: $message,
            cardHash: data_get($creadentials, 'card_no'),
            cardMask: data_get($creadentials, 'hashed_card_no'),
            referenceId: (int) data_get($creadentials, 'ref_num'),
        );
    }

    protected function normalizeAmount(int $amount): int
    {
        return config('irpayment.currency_symbol', 'IRT') === 'IRT'
            ? $amount * 10
            : $amount;
    }

    /** @see \IRPayment\Tests\PaykanVerifyStatusToCodeTest */
    protected function toCode($status): int
    {
        return match ($status) {
            'CONFIRMED' => 100,
            'FAILED' => 508,
            'INVALID_CARD' => 400,
            'CANCELLED' => 503,
            default => -1,
        };
    }

    protected function getOrderId(Payment $payment): int
    {
        return (int) ($payment->id.time());
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
