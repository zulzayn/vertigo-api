<?php

namespace App;

use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Transport extends Model
{
    use Notifiable;
    protected $table = 'transports';
    public $incrementing = FALSE;

    public function getImgPathAttribute($value)
    {
        $url = URL::to($value);
        return $url;
    }

    public function transportcategory()
    {
        return $this->hasOne('App\TransportCategory', 'id', 'id_trans_category');
    }

    public function bookings()
    {
        return $this->hasMany('App\TBSTransportUse', 'id_transport');
    }

    public function tbsDaily($date)
    {
        return $this->belongsToMany('App\TBS', 'tbs_transport_uses', 'id_transport', 'id_tbs')
            ->whereRaw('? between date(tbs.start_date) and tbs.end_date', [$date])
            ->get();
    }

    public function tbsMonthly($month)
    {
        return $this->belongsToMany('App\TBS', 'tbs_transport_uses', 'id_transport', 'id_tbs')
            ->whereMonth('tbs.start_date', $month)
            ->get();
    }
}
