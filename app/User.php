<?php

namespace App;

use App\Exceptions\LogicException;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * 修改登录密码
     * @param $password
     * @return bool
     */
    public function changePassword($password)
    {
        $this->password = bcrypt($password);
        return $this->save();
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->is_admin > 0 ? true: false;
    }

    /**
     * 新建用户
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public static function newUserInstance(array $data)
    {
        if(!empty($data['password'])){
            $data['password'] = bcrypt(trim($data['password']));
        }else{
            unset($data['password']);
        }
        return self::query()->create($data);
    }

    /**
     * 更新用户
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function updateUser(int $id, array $data): bool
    {
        $user =  self::query()->find($id);
        if(!$user){
            throw new LogicException("用户不存在",500);
        }
        if(!empty($data['password'])){
            $data['password'] = bcrypt(trim($data['password']));
        }else{
            unset($data['password']);
        }
        foreach ($data as $key => $value){
            $user->{$key} = $value;
        }
        return $user->save();
    }
}
