<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property bool is_cancelled
 * @property Order order
 * @property string first_name
 * @property string last_name
 */
class Attendee extends MyBaseModel
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'event_id',
        'order_id',
        'ticket_id',
        'account_id',
        'reference',
        'has_arrived',
        'arrival_time',
    ];

    protected $casts = [
        'is_refunded' => 'boolean',
        'is_cancelled' => 'boolean',
    ];

    /**
     * Generate a private reference number for the attendee. Use for checking in the attendee.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($order) {

            do {
                //generate a random string using Laravel's Str::Random helper
                $token = Str::Random(15);
            } //check if the token already exists and if it does, try again

            while (Attendee::where('private_reference_number', $token)->first());
            $order->private_reference_number = $token;
        });

    }

    public static function findFromSelection(array $attendeeIds = []): Collection
    {
        return (new static)->whereIn('id', $attendeeIds)->get();
    }

    /**
     * The order associated with the attendee.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The ticket associated with the attendee.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * The event associated with the attendee.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class);
    }

    /**
     * Scope a query to return attendees that have not cancelled.
     *
     *
     * @return mixed
     */
    public function scopeWithoutCancelled($query)
    {
        return $query->where('attendees.is_cancelled', '=', 0);
    }

    /**
     * Reference index is a number representing the position of
     * an attendee on an order, for example if a given order has 3
     * attendees, each attendee would be assigned an auto-incrementing
     * integer to indicate if they were attendee 1, 2 or 3.
     *
     * The reference attribute is a string containing the order reference
     * and the attendee's reference index.
     */
    public function getReferenceAttribute(): string
    {
        return $this->order->order_reference.'-'.$this->reference_index;
    }

    /**
     * Get the full name of the attendee.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @return array $dates
     */
    public function getDates(): array
    {
        return ['created_at', 'updated_at', 'arrival_time'];
    }
}
