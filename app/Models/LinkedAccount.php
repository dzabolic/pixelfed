<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class LinkedAccount extends Model
{
    protected $fillable = ['owner_user_id', 'linked_user_id', 'switch_token'];
 
    public function owner()
    {
        return $this->belongsTo(\App\User::class, 'owner_user_id');
    }
 
    public function linked()
    {
        return $this->belongsTo(\App\User::class, 'linked_user_id');
    }
}
 
