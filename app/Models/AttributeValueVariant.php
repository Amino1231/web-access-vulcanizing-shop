<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeValueVariant extends Model
{
    protected $fillable = [
        'variant_id',
        'attribute_value_id',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }
}