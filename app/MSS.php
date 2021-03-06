<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class MSS extends Model
{
    use Notifiable;
    protected $table = 'mss';
    public $incrementing = FALSE;
    protected $with = ['mssequipment.equipment', 'msstransport.transport', 'msspic.user', 'msstask.maintenanceTask'];

    public function mssequipment()
    {
        return $this->hasMany('App\MSSEquipment', 'id_mss', 'id');
    }

    public function msstransport()
    {
        return $this->hasMany('App\MSSTransport', 'id_mss', 'id');
    }

    public function msspic()
    {
        return $this->hasMany('App\MSSPic', 'id_mss', 'id');
    }

    public function msstask()
    {
        return $this->hasMany('App\MSSTask', 'id_mss', 'id');
    }

    public function createdby()
    {
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function personInCharge()
    {
        return $this->belongsToMany('App\User', 'App\MSSPic', 'id_mss', 'id_user');
    }
}
