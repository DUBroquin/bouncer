<?php

namespace Dubroquin\Bouncer\Database;

use Illuminate\Database\Eloquent\Model;
use Pegasus\NotificationType;

class Role extends Model
{
    use Concerns\IsRole;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'title', 'level'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['level' => 'int'];

    /**
     * Constructor.
     *
     * @param array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Models::table('roles');

        parent::__construct($attributes);
    }

     public function notification_types(){
        return $this->belongsToMany(NotificationType::class)->withTimestamps();
    }

    public function groups(){
        return $this->hasMany(Group::class);
    }
}
