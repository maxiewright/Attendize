<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Http\UploadedFile;
use Image;
use Str;

class Organiser extends MyBaseModel implements AuthenticatableContract
{
    use Authenticatable;
    use HasFactory;

    /**
     * The validation rules for the model.
     *
     * @var array
     */
    protected $rules = [
        'name' => ['required'],
        'email' => ['required', 'email'],
        'organiser_logo' => ['nullable', 'mimes:jpeg,jpg,png', 'max:10000'],
    ];

    protected $extra_rules = [
        'tax_name' => ['nullable', 'max:15'],
        'tax_value' => ['nullable', 'numeric'],
        'tax_id' => ['nullable', 'max:100'],
    ];

    /**
     * The validation rules for the model.
     *
     * @var array
     */
    protected $attributes = [
        'tax_name' => 'Tax Name',
        'tax_value' => 'Tax Rate',
        'tax_id' => 'Tax ID',
    ];

    /**
     * The validation error messages for the model.
     *
     * @var array
     */
    protected $messages = [
        'name.required' => 'You must at least give a name for the event organiser.',
        'organiser_logo.max' => 'Please upload an image smaller than 10Mb',
        'organiser_logo.size' => 'Please upload an image smaller than 10Mb',
        'organiser_logo.mimes' => 'Please select a valid image type (jpeg, jpg, png)',
    ];

    /**
     * The account associated with the organiser
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Account::class);
    }

    /**
     * The events associated with the organizer.
     */
    public function events(): HasMany
    {
        return $this->hasMany(\App\Models\Event::class);
    }

    /**
     * The attendees associated with the organizer.
     */
    public function attendees(): HasManyThrough
    {
        return $this->hasManyThrough(\App\Models\Attendee::class, \App\Models\Event::class);
    }

    /**
     * Get the orders related to an organiser
     */
    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(\App\Models\Order::class, \App\Models\Event::class);
    }

    /**
     * Get the full logo path of the organizer.
     *
     * @return mixed|string
     */
    public function getFullLogoPathAttribute()
    {
        if ($this->logo_path && (file_exists(public_path($this->logo_path)) || file_exists(config('attendize.cdn_url_user_assets').'/'.$this->logo_path))) {
            return config('attendize.cdn_url_user_assets').'/'.$this->logo_path;
        }

        return config('attendize.fallback_organiser_logo_url');
    }

    /**
     * Get the url of the organizer.
     */
    public function getOrganiserUrlAttribute(): string
    {
        return route('showOrganiserHome', [
            'organiser_id' => $this->id,
            'organiser_slug' => Str::slug($this->oraganiser_name),
        ]);
    }

    /**
     * Get the sales volume of the organizer.
     *
     * @return mixed|number
     */
    public function getOrganiserSalesVolumeAttribute()
    {
        return $this->events->sum('sales_volume');
    }

    public function getTicketsSold()
    {
        return $this->attendees()->where('is_cancelled', false)->count();
    }

    /**
     * TODO:implement DailyStats method
     */
    public function getDailyStats()
    {
    }

    /**
     * Set a new Logo for the Organiser
     */
    public function setLogo(UploadedFile $file)
    {
        $filename = Str::slug($this->name).'-logo-'.$this->id.'.'.strtolower($file->getClientOriginalExtension());

        // Image Directory
        $imageDirectory = public_path().'/'.config('attendize.organiser_images_path');

        // Paths
        $relativePath = config('attendize.organiser_images_path').'/'.$filename;
        $absolutePath = public_path($relativePath);

        $file->move($imageDirectory, $filename);

        $img = Image::make($absolutePath);

        $img->resize(250, 250, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $img->save($absolutePath);

        if (file_exists($absolutePath)) {
            $this->logo_path = $relativePath;
        }
    }

    /**
     * Adds extra validator rules to the organiser object depending on whether tax is required or not
     */
    public function addExtraValidationRules()
    {
        $this->rules = array_merge($this->rules, $this->extra_rules);
    }
}
