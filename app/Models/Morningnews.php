<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Morningnews extends Model
{
    protected $table= "morning_news";
    protected $fillable = ["title","news_text"];
}
