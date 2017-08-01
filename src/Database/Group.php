<?php

namespace Dubroquin\Bouncer\Database;

use Illuminate\Database\Eloquent\Model;
use Pegasus\NotificationType;

class Group extends Model
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
        $this->table = Models::table('groups');

        parent::__construct($attributes);
    }

    public function roles(){
        return $this->belongsTo(Role::class);
    }
}
