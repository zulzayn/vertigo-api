<?php

namespace App;

use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TMS extends Model
{
    use Notifiable;
    protected $table = 'tms';
    public $incrementing = FALSE;
    protected $with = ['pic.user', 'inquiry.user'];

    public function pic()
    {
        return $this->hasMany('App\TMSPic', 'id_tms', 'id');
    }

    public function inquiry()
    {
        return $this->hasOne('App\InquiryType', 'id', 'id_inquiry');
    }

    public function getClients($vtsb_num)
    {
        return $this->whereVtsbNum($vtsb_num)->get();
    }
}
