<?php

namespace App;

use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TBS extends Model
{
    use Notifiable;
    protected $table = 'tbs';
    public $incrementing = FALSE;
    protected $with = ['tbstransportuse.transport', 'tbsdriver.driver'];

    public function getImgPathUpdateAttribute($value)
    {
        $url = URL::to($value);
        return $url;
    }

    public function tbstransportuse()
    {
        return $this->hasMany('App\TBSTransportUse', 'id_tbs', 'id');
    }

    public function tbsdriver()
    {
        return $this->hasMany('App\TBSDriver', 'id_tbs', 'id');
    }

    public function staffs()
    {
        return $this->belongsToMany('App\User', 'tbs_drivers', 'id_tbs', 'id_user');
    }
}
