<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Highlight extends Model
{
    protected $fillable = ['user_id', 'title', 'cover_path'];

    // Relacionamento: Um destaque pertence a um usuário
    public function user()
    {
        // Adicionamos \App\ para ele achar o arquivo que você encontrou
        return $this->belongsTo(\App\User::class);
    }

    // Relacionamento: Um destaque tem vários stories
    public function stories()
    {
        // Aqui também apontamos para a raiz onde está o Story.php que vimos na imagem
        return $this->belongsToMany(\App\Story::class, 'highlight_story', 'highlight_id', 'story_id');
    }
}
