<?php

namespace App\Models;

use App\Attendize\Utils;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends MyBaseModel
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The validation rules
     *
     * @var array
     */
    protected $rules = [
        'first_name' => ['required'],
        'last_name' => ['required'],
        'email' => ['required', 'email'],
    ];

    /**
     * The validation error messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'timezone_id',
        'date_format_id',
        'datetime_format_id',
        'currency_id',
        'name',
        'last_ip',
        'last_login_date',
        'address1',
        'address2',
        'city',
        'state',
        'postal_code',
        'country_id',
        'email_footer',
        'is_active',
        'is_banned',
        'is_beta',
        'stripe_access_token',
        'stripe_refresh_token',
        'stripe_secret_key',
        'stripe_publishable_key',
        'stripe_data_raw',
    ];

    /**
     * The users associated with the account.
     */
    public function users(): HasMany
    {
        return $this->hasMany(\App\Models\User::class);
    }

    /**
     * The orders associated with the account.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    /**
     * The currency associated with the account.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Currency::class);
    }

    /**
     * Payment gateways associated with an account
     */
    public function account_payment_gateways(): HasMany
    {
        return $this->hasMany(\App\Models\AccountPaymentGateway::class);
    }

    /**
     * Alias for $this->account_payment_gateways()
     */
    public function gateways(): HasMany
    {
        return $this->account_payment_gateways();
    }

    /**
     * Get an accounts active payment gateway
     */
    public function active_payment_gateway(): HasOne
    {
        return $this->hasOne(\App\Models\AccountPaymentGateway::class, 'payment_gateway_id', 'payment_gateway_id')->where('account_id', $this->id);
    }

    /**
     * Get an accounts gateways
     *
     * @return mixed
     */
    public function getGateway($gateway_id)
    {
        return $this->gateways->where('payment_gateway_id', $gateway_id)->first();
    }

    /**
     * Get a config value for a gateway
     *
     * @return mixed
     */
    public function getGatewayConfigVal($gateway_id, $key)
    {
        $gateway = $this->getGateway($gateway_id);

        if ($gateway && is_array($gateway->config)) {
            return isset($gateway->config[$key]) ? $gateway->config[$key] : false;
        }

        return false;
    }

    /**
     * Get the stripe api key.
     *
     * @return \Illuminate\Support\Collection|mixed|static
     */
    public function getStripeApiKeyAttribute()
    {
        if (Utils::isAttendize()) {
            return $this->stripe_access_token;
        }

        return $this->stripe_secret_key;
    }
}
