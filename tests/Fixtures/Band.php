<?php

namespace Thettler\LaravelConsoleToolkit\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Band extends Model
{
    protected $guarded = [];

    public $preventsLazyLoading = true;

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }
}
