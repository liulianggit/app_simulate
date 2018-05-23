<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class content extends Model
{
    //不更新created_at  updated_at字段
    public $timestamps = false;
    public $table = 'xy_app_content';
}
