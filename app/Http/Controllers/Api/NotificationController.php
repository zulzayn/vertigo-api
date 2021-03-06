<?php

namespace App\Http\Controllers\Api;

use App\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notifications = Notification::all();

        return response(['status' => 'OK', 'notifications' => $notifications]);
    }

    public function getPending()
    {
        $notifications = Notification::where('send_status', 'P')->where('to_user', auth()->user()->id)
        ->latest()
        ->get();

        return response(['status' => 'OK', 'notifications' => $notifications]);
    }

    public function getSend()
    {
        $notifications = Notification::where('send_status', 'S')->where('to_user', auth()->user()->id)
        ->latest()
        ->get();

        return response(['status' => 'OK', 'notifications' => $notifications]);
    }

    public function getReceived()
    {
        $notifications = Notification::where('send_status', 'R')->where('to_user', auth()->user()->id)
        ->latest()
        ->get();

        return response(['status' => 'OK', 'notifications' => $notifications]);
    }

    public function getRead()
    {
        $notifications = Notification::where('send_status', 'D')->where('to_user', auth()->user()->id)
        ->latest()
        ->get();

        return response(['status' => 'OK', 'notifications' => $notifications]);
    }

    public function getFailed()
    {
        $notifications = Notification::where('send_status', 'F')->where('to_user', auth()->user()->id)
        ->latest()
        ->get();

        return response(['status' => 'OK', 'notifications' => $notifications]);
    }

    public function getByUser()
    {
        $notifications = Notification::where('to_user', auth()->user()->id)
            ->latest()
            ->get();

        return response(['status' => 'OK', 'notifications' => $notifications]);
    }

    public function changeToRead($id)
    {
        $notification = Notification::find($id);

        $notification->send_status = "D";
        $notification->save();

        return response(['status' => 'OK', 'message' => "Success update notification send_status"]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $noti = Notification::find($id);
        $noti->delete();

        return response(['status' => 'OK']);
    }
}
