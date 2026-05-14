<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Highlight extends Model
{
    // Define quais campos podem ser preenchidos
    protected $fillable = ['user_id', 'title', 'cover_path'];

    // Relacionamento: Um destaque pertence a um usuário
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento: Um destaque tem vários stories
    public function stories()
    {
        return $this->belongsToMany(Story::class, 'highlight_story', 'highlight_id', 'story_id');
    }
}
