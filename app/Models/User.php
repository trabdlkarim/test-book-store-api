<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as Authenticatable;


class User extends Authenticatable{

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'active',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
    ];

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyAdminEmail);
    }

    protected function getAvatarUrlAttribute()
    {
        if ($this->avatar) return Voyager::image($this->avatar);
        else return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email))) . '?s=200&d=identicon';
    }

    public function role()
    {
        return $this->belongsTo(Voyager::modelClass('Role'));
    }

    /**
     * Check if User has a Role(s) associated.
     *
     * @param string|array $name The role(s) to check.
     *
     * @return bool
     */
    public function hasRole($slug)
    {
        $roles = $this->roles_all()->pluck('slug')->toArray();

        foreach ((is_array($slug) ? $slug : [$slug]) as $role) {
            if (in_array($role, $roles)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Set default User Role.
     *
     * @param string $name The role name to associate.
     */
    public function setRole($slug)
    {
        $role = Voyager::model('Role')->where('slug', '=', $slug)->first();

        if ($role) {
            $this->role()->associate($role);
            $this->save();
        }

        return $this;
    }

    public function isCentralAdmin()
    {
        return $this->role->slug === Role::ADMIN_ROLE_SLUG;
    }

    public function isCentralUser()
    {
        return $this->role->slug === Role::USER_ROLE_SLUG;
    }

    /**
     * The channels the user receives notification broadcasts on.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn()
    {
        return 'central.users.' . $this->id;
    }
}
