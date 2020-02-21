<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SAS extends Model
{
    use Notifiable;
    protected $table = 'sas';
    public $incrementing = FALSE;

    public function sasstaffassign() {
        return $this->hasMany('App\SASS', 'id_equip_category', 'id');
    }

}