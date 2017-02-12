<?php

namespace EVEMail;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $primaryKey = 'queue_id';
    protected $table = 'queue';

    protected $fillable = [
        'queue_id'
    ];
}
