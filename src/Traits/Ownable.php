<?php

namespace Trinavo\Ownable\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Trinavo\Ownable\Models\Ownership;
use Trinavo\Ownable\Exceptions\UserNotSetException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait Ownable
{

    public static $autoOwn = true;

    public function addOwner(Model|Authenticatable $owner): void
    {
        try {
            Ownership::create([
                'model_class' => $this->getMorphClass(),
                'model_short' => class_basename($this),
                'record_id'  => $this->getKey(),
                'owner_id'    => $owner->id,
                'owner_class' => $owner->getMorphClass()
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Silently ignore duplicate key errors (code 23000 or 1062)
            if (!($e->getCode() == 23000 ||
                (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062))) {
                throw $e;
            }
        }
    }

    public function isOwnedBy(Model|Authenticatable $owner): bool
    {
        return Ownership::where('model_class', $this->getMorphClass())
            ->where('model_short', class_basename($this))
            ->where('record_id', $this->getKey())
            ->where('owner_id', $owner->id)
            ->where('owner_class', $owner->getMorphClass())
            ->exists();
    }

    public function removeOwner(Model|Authenticatable $owner): int
    {
        return Ownership::where('model_class', $this->getMorphClass())
            ->where('model_short', class_basename($this))
            ->where('record_id', $this->getKey())
            ->where('owner_id', $owner->id)
            ->where('owner_class', $owner->getMorphClass())
            ->delete();
    }

    public function removeOwners(): int
    {
        return Ownership::where('model_class', $this->getMorphClass())
            ->where('model_short', class_basename($this))
            ->where('record_id', $this->getKey())
            ->delete();
    }

    public static function bootOwnable()
    {
        if (static::$autoOwn) {
            static::created(function (Model $model) {
                $user = Auth::user();
                if ($user) {
                    $model->addOwner($user);
                }
            });
        }


        static::deleted(function (Model $model) {
            $model->removeOwners();
        });
    }


    public function getOwnerType(Model|Authenticatable $owner): string
    {
        return $owner->getMorphClass();
    }

    public function getOwners(string|array|null $ownerType = null, int $limit = null): Collection
    {
        $query = Ownership::where('model_class', $this->getMorphClass())
            ->where('record_id', $this->getKey());

        if ($ownerType) {
            $query->where('owner_class', $ownerType);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getOwner(): Model|Authenticatable|null
    {
        return $this->getOwners(limit: 1)->first();
    }

    public function scopeOwnedBy($query, Model|Authenticatable $user): Builder
    {
        return $query->whereHas('owners', function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->where('owner_class', $user->getMorphClass());
        });
    }

    public function scopeMine($query): Builder
    {
        /**
         * @var Model|Authenticatable $user
         */
        $user = Auth::user();
        if (!$user) {
            throw new UserNotSetException();
        }
        return $query->whereHas('owners', function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->where('owner_class', $user->getMorphClass());
        });
    }

    public function owners(): HasMany
    {
        return $this->hasMany(Ownership::class, 'record_id')
            ->where('model_class', $this->getMorphClass());
    }
}
