<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SAS extends Model
{
    use Notifiable;
    protected $table = 'sas';
    public $incrementing = FALSE;
    protected $with = ['sasstaffassign.sascomment', 'sasstaffassign.user'];

    public function sasstaffassign()
    {
        return $this->hasMany('App\SASStaffAssign', 'id_sas', 'id');
    }

    public function personInUse()
    {
        return $this->belongsToMany('App\User', 'sas_staff_assigns', 'id_user', 'id_sas');
    }
}
