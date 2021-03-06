<?php


namespace Imdhemy\Purchases;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Imdhemy\AppStore\ClientFactory as AppStoreClientFactory;
use Imdhemy\AppStore\Receipts\ReceiptResponse;
use Imdhemy\AppStore\Receipts\Verifier;
use Imdhemy\GooglePlay\ClientFactory as GooglePlayClientFactory;
use Imdhemy\GooglePlay\Subscriptions\Subscription as GooglePlaySubscription;
use Imdhemy\GooglePlay\Subscriptions\SubscriptionPurchase;

class Subscription
{
    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $packageName;

    /**
     * @var string
     */
    protected $receiptData;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var bool
     */
    protected $renewalAble;

    /**
     * @return self
     */
    public function googlePlay(): self
    {
        $this->client = GooglePlayClientFactory::create([GooglePlayClientFactory::SCOPE_ANDROID_PUBLISHER]);
        $this->packageName = config('purchase.google_play_package_name');

        return $this;
    }

    /**
     * @return self
     */
    public function appStore(): self
    {
        $this->client = AppStoreClientFactory::create();
        $this->password = config('purchase.appstore_password');
        $this->renewalAble = false;

        return $this;
    }

    /**
     * @param string $itemId
     * @return self
     */
    public function id(string $itemId): self
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * @param string $token
     * @return self
     */
    public function token(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param string $packageName
     * @return self
     */
    public function packageName(string $packageName): self
    {
        $this->packageName = $packageName;

        return $this;
    }

    /**
     * @return ReceiptResponse
     * @throws GuzzleException
     */
    public function verifyReceipt(): ReceiptResponse
    {
        $verifier = new Verifier($this->client, $this->receiptData, $this->password);

        return $verifier->verify($this->renewalAble);
    }

    /**
     * @return ReceiptResponse
     * @throws GuzzleException
     */
    public function verifyRenewable(): ReceiptResponse
    {
        $verifier = new Verifier($this->client, $this->receiptData, $this->password);

        return $verifier->verifyRenewable();
    }

    /**
     * @return self
     */
    public function renewable(): self
    {
        $this->renewalAble = true;

        return $this;
    }

    /**
     * @return self
     */
    public function nonRenewable(): self
    {
        $this->renewalAble = false;

        return $this;
    }

    /**
     * @return SubscriptionPurchase
     * @throws GuzzleException
     */
    public function get(): SubscriptionPurchase
    {
        return $this->createSubscription()->get();
    }

    /**
     * @param string|null $developerPayload
     * @throws GuzzleException
     */
    public function acknowledge(string $developerPayload = null) 
    {
        $this->createSubscription()->acknowledge($developerPayload);
    }

    /**
     * @return GooglePlaySubscription
     */
    public function createSubscription(): GooglePlaySubscription
    {
        return new GooglePlaySubscription(
            $this->client,
            $this->packageName,
            $this->itemId,
            $this->token
        );
    }

    /**
     * @param string $receiptData
     * @return $this
     */
    public function receiptData(string $receiptData): self
    {
        $this->receiptData = $receiptData;

        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function password(string $password): self
    {
        $this->password = $password;

        return $this;
    }
}
