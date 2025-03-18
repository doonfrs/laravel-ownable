<?php

namespace Trinavo\Ownable\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $model_class
 * @property string $model_short_name
 * @property int $record_id
 * @property int $owner_id
 * @property string $owner_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Ownership extends Model
{
    protected $table = 'ownable_ownerships';
    protected $guarded = [];
}
