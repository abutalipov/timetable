<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

class
Timetable extends AbstractModel
{
    protected $fillable = [
      'time_start',
      'time_end',
      'event_date',
      'user_id',
      'group_id',
      'location_id',
      'occupation_id',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function group(){
        return $this->belongsTo(Group::class);
    }
    public function location(){
        return $this->belongsTo(Location::class);
    }
    public function occupation(){
        return $this->belongsTo(Occupation::class);
    }
    public static function check( $time_start, $time_end, $date,$user,$group,$location)
    {
        return Timetable
            ::where(function (Builder $query) use ($time_start,$date,$group,$user,$location) {
                $query->where('time_start', '<=', $time_start)
                    ->where('time_end', '>', $time_start)
                    ->where('event_date', $date)
                    ->where('group_id', $group)
                    ->where('user_id', $user)
                    ->where('location_id', $location);
            })
            ->orWhere(function (Builder $query) use ($time_end,$date,$group,$user,$location) {
                $query->where('time_start', '<', $time_end)
                    ->where('time_end', '>=', $time_end)
                    ->where('event_date', $date)
                    ->where('group_id', $group)//или у него
                    ->where('user_id', $user)//или у него не может быть в одно и тоже время в другом мете у другого препода и тд
                    ->where('location_id', $location);//или у него
            })
            ->orWhere(function (Builder $query) use ($time_start, $time_end,$date,$group,$user,$location) {
                $query->whereBetween('time_start', [$time_start, $time_end])
                    ->whereBetween('time_end', [$time_start, $time_end])
                    ->where('event_date', $date)
                    ->where('group_id', $group)
                    ->where('user_id', $user)
                    ->where('location_id', $location);
            });
    }

}
