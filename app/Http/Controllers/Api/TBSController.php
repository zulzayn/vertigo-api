<?php

namespace App\Http\Controllers\Api;

use App\TBS;
use App\User;
use App\Scheduler;
use App\TBSDriver;
use App\Transport;
use App\DocumentLog;
use App\Notification;
use Ramsey\Uuid\Uuid;
use App\TBSTransportUse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TBSController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tbs = TBS::all();

        return response(['status' => 'OK' , 'message' =>  $tbs]);
    }

    public function dashDate(Request $request)
    {

        $dateFrom = date($request->date_from);
        $dateTo = date($request->date_to);

        $tbs = TBS::whereBetween('created_at', [$dateFrom, $dateTo])->get();

        return response(['status' => 'OK' , 'message' => $tbs]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_date'            => 'required',
            'start_time'            => 'required',
            'end_date'              => 'required', 
            'end_time'              => 'required', 
            'job_number'            => 'required',
            'job_title'             => 'required', 
            'drivers.*'             => 'required',
            'transport_uses.*'      => 'required',
        ]);

        $add = New TBS;
        $add->id = Uuid::uuid4()->getHex();
        $add->start_date = ''.date("Y-m-d", strtotime($request->start_date)).' '.date("H:i:s", strtotime($request->start_time)).'';
        $add->end_date = ''.date("Y-m-d", strtotime($request->end_date)).' '.date("H:i:s", strtotime($request->end_time)).'';
        $add->job_number = $request->job_number;
        $add->job_title = $request->job_title;
        $add->status = 'Booking Confirmed';
        $add->created_by = auth()->user()->id;
        $add->save();

        foreach ($request->transport_uses as $key => $transport_use) {
            $add3 = New TBSTransportUse;
            $add3->id = Uuid::uuid4()->getHex();
            $add3->id_transport = $transport_use;
            $add3->id_tbs = $add->id;
            $add3->created_by = auth()->user()->id;
            $add3->save(); 

            $transport = Transport::find($transport_use);
            $transport->availability = "unavailable";
            $transport->save();
        }

        foreach ($request->drivers as $key => $driver) {
            $add2 = New TBSDriver;
            $add2->id = Uuid::uuid4()->getHex();
            $add2->id_user = $driver;
            $add2->id_tbs = $add->id;
            $add2->created_by = auth()->user()->id;
            $add2->save(); 

            //NOTIFICATION FCM SCHEDULE
            $noti = new Notification;
            $noti->to_user =  $driver;
            $noti->tiny_img_url = '';
            $noti->title = 'Vertigo [Transport Booking System]';
            $noti->desc = 'Have you utilize the transport?';
            $noti->type = 'I';
            $noti->click_url = 'tbs-start';
            $noti->send_status = 'P';
            $noti->status = '';
            $noti->module = 'tbs';
            $noti->id_module = $add->id;
            $noti->created_by = auth()->user()->id;
            $json_noti = json_encode($noti);

            $scheduler = New Scheduler;
            $scheduler->id = Uuid::uuid4()->getHex();
            $scheduler->trigger_datetime = $add->start_date;
            $scheduler->url_to_call = 'triggeredNotification';
            $scheduler->secret_key = '';
            $scheduler->params = $json_noti;
            $scheduler->is_triggered = 0;
            $scheduler->created_by = auth()->user()->id;
            $scheduler->save();

        }

        $document = New DocumentLog;
        $document->id 				= Uuid::uuid4()->getHex();
        $document->user_type 		= auth()->user()->role->name;
        $document->id_user			= auth()->user()->id;
        $document->start_at 		= date('Y-m-d H:i:s');
        $document->end_at 			= null;
        $document->document_type 	= "TBS";
        $document->id_document 		=  $add->id;
        $document->remark 			= "Create New Booking for Transport Booking System";
        $document->status 			= "Booking Confirm";
        $document->id_notification 	= "";
        $document->created_by 		= auth()->user()->id;
        $document->updated_by 		= auth()->user()->id;
        $document->save();

        return response(['status' => 'OK' , 'message' => 'Successfully book transport']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tbs = TBS::find($id);

        return response(['status' => 'OK' , 'message' =>  $tbs]); 
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
        $request->validate([
            'start_date'            => 'required',
            'start_time'            => 'required',
            'end_date'              => 'required', 
            'end_time'              => 'required', 
            'job_number'            => 'required',
            'job_title'             => 'required', 
            'drivers.*'             => 'required',
            'transport_uses.*'      => 'required',
        ]);

        $add = TBS::find($id);
        $add->start_date = ''.date("Y-m-d", strtotime($request->start_date)).' '.date("H:i:s", strtotime($request->start_time)).'';
        $add->end_date = ''.date("Y-m-d", strtotime($request->end_date)).' '.date("H:i:s", strtotime($request->end_time)).'';
        $add->job_number = $request->job_number;
        $add->job_title = $request->job_title;
        // $add->status = 'Booking Confirmed';
        $add->updated_by = auth()->user()->id;
        $add->save();

        foreach ($add->tbstransportuse as $key => $data) {

            $transport = Transport::find($data->id_transport);
            $transport->availability = "available";
            $transport->save();

            $data->delete();
        }

        foreach ($add->tbsdriver as $key => $data2) {
            $data2->delete();
        }

        foreach ($request->transport_uses as $key => $transport_use) {
            $add3 = New TBSTransportUse;
            $add3->id = Uuid::uuid4()->getHex();
            $add3->id_transport = $transport_use;
            $add3->id_tbs = $add->id;
            $add3->created_by = auth()->user()->id;
            $add3->save(); 

            $transport = Transport::find($transport_use);
            $transport->availability = "unavailable";
            $transport->save();
        }

        foreach ($request->drivers as $key => $driver) {
            $add2 = New TBSDriver;
            $add2->id = Uuid::uuid4()->getHex();
            $add2->id_user = $driver;
            $add2->id_tbs = $add->id;
            $add2->created_by = auth()->user()->id;
            $add2->save(); 

            //NOTIFICATION FCM SCHEDULE
            $noti = new Notification;
            $noti->to_user =  $driver;
            $noti->tiny_img_url = '';
            $noti->title = 'Vertigo [Transport Booking System]';
            $noti->desc = 'Have you utilize the transport?';
            $noti->type = 'I';
            $noti->click_url = 'tbs-start';
            $noti->send_status = 'P';
            $noti->status = '';
            $noti->module = 'tbs';
            $noti->id_module = $add->id;
            $noti->created_by = auth()->user()->id;
            $json_noti = json_encode($noti);

            $scheduler = New Scheduler;
            $scheduler->id = Uuid::uuid4()->getHex();
            $scheduler->trigger_datetime = $add->start_date;
            $scheduler->url_to_call = 'triggeredNotification';
            $scheduler->secret_key = '';
            $scheduler->params = $json_noti;
            $scheduler->is_triggered = 0;
            $scheduler->created_by = auth()->user()->id;
            $scheduler->save();

        }

        $document = New DocumentLog;
        $document->id 				= Uuid::uuid4()->getHex();
        $document->user_type 		= auth()->user()->role->name;
        $document->id_user			= auth()->user()->id;
        $document->start_at 		= date('Y-m-d H:i:s');
        $document->end_at 			= null;
        $document->document_type 	= "TBS";
        $document->id_document 		=  $add->id;
        $document->remark 			= "Edit Booking for Transport Booking System";
        $document->status 			= "Booking Confirm";
        $document->id_notification 	= "";
        $document->created_by 		= auth()->user()->id;
        $document->updated_by 		= auth()->user()->id;
        $document->save();

        return response(['status' => 'OK' , 'message' => 'Successfully edit booked transport']);
    }

    public function startBooking(Request $request , $id_tbs)
    {
        $request->validate([
            'start_status'        => 'required',
        ]);

        $tbs = TBS::find($id_tbs);

        if ($request->start_status == 'Yes') 
        {
            $tbs->start_status = "Yes"; 
            $tbs->status = "Booking Start";
            $tbs->updated_by = auth()->user()->id;
            $tbs->save();

            foreach ($tbs->tbsdriver as $key => $tbsdriver) {

                //NOTIFICATION FCM SCHEDULE
                $noti = new Notification;
                $noti->to_user =  $tbsdriver->id_user;
                $noti->tiny_img_url = '';
                $noti->title = 'Vertigo [Transport Booking System]';
                $noti->desc = 'Have you completed the booking?';
                $noti->type = 'I';
                $noti->click_url = 'tbs-end';
                $noti->send_status = 'P';
                $noti->status = '';
                $noti->module = 'tbs';
                $noti->id_module = $tbs->id;
                $noti->created_by = auth()->user()->id;
                $json_noti = json_encode($noti);

                $scheduler = New Scheduler;
                $scheduler->id = Uuid::uuid4()->getHex();
                $scheduler->trigger_datetime = $tbs->end_date;
                $scheduler->url_to_call = 'triggeredNotification';
                $scheduler->secret_key = '';
                $scheduler->params = $json_noti;
                $scheduler->is_triggered = 0;
                $scheduler->created_by = auth()->user()->id;
                $scheduler->save();
     
            }

            $document = New DocumentLog;
            $document->id 				= Uuid::uuid4()->getHex();
            $document->user_type 		= auth()->user()->role->name;
            $document->id_user			= auth()->user()->id;
            $document->start_at 		= date('Y-m-d H:i:s');
            $document->end_at 			= null;
            $document->document_type 	= "TBS";
            $document->id_document 		=  $tbs->id;
            $document->remark 			= 'Transport Booking Start for Job Number : '.$tbs->job_number.'';
            $document->status 			= "Booking Start";
            $document->id_notification 	= "";
            $document->created_by 		= auth()->user()->id;
            $document->updated_by 		= auth()->user()->id;
            $document->save();

            return response(['status' => 'OK' , 'message' => 'Successfully acknowledge & start booking']);
        } 
        elseif ($request->start_status == 'No') 
        {
            $request->validate([
                'start_justification'        => 'required',
                'start_date'                 => 'required',
                'start_time'                 => 'required',
            ]);

            $tbs->start_status = "No"; 
            $tbs->start_date = ''.date("Y-m-d", strtotime($request->start_date)).' '.date("H:i:s", strtotime($request->start_time)).'';
            $tbs->start_justification = $request->start_justification;
            $tbs->updated_by = auth()->user()->id;
            $tbs->save();

            foreach ($tbs->tbsdriver as $key => $tbsdriver) {
    
                //NOTIFICATION FCM SCHEDULE
                $noti = new Notification;
                $noti->to_user =  $tbsdriver->id_user;
                $noti->tiny_img_url = '';
                $noti->title = 'Vertigo [Transport Booking System]';
                $noti->desc = 'Have you utilize the transport?';
                $noti->type = 'I';
                $noti->click_url = 'tbs-start';
                $noti->send_status = 'P';
                $noti->status = '';
                $noti->module = 'tbs';
                $noti->id_module = $tbs->id;
                $noti->created_by = auth()->user()->id;
                $json_noti = json_encode($noti);

                $scheduler = New Scheduler;
                $scheduler->id = Uuid::uuid4()->getHex();
                $scheduler->trigger_datetime = $tbs->start_date;
                $scheduler->url_to_call = 'triggeredNotification';
                $scheduler->secret_key = '';
                $scheduler->params = $json_noti;
                $scheduler->is_triggered = 0;
                $scheduler->created_by = auth()->user()->id;
                $scheduler->save();

                
            }
            
            $document = New DocumentLog;
            $document->id 				= Uuid::uuid4()->getHex();
            $document->user_type 		= auth()->user()->role->name;
            $document->id_user			= auth()->user()->id;
            $document->start_at 		= date('Y-m-d H:i:s');
            $document->end_at 			= null;
            $document->document_type 	= "TBS";
            $document->id_document 		=  $tbs->id;
            $document->remark 			= 'Transport Booking for Job Number : '.$tbs->job_number.' has place a new start date to '.date('j F Y, g:i a' , strtotime($tbs->start_date)).' ';
            $document->status 			= "Booking set a New Start Date";
            $document->id_notification 	= "";
            $document->created_by 		= auth()->user()->id;
            $document->updated_by 		= auth()->user()->id;
            $document->save();

            return response(['status' => 'OK' , 'message' => 'Successfully extend start booking']);
        }
    }

    public function updateProgress(Request $request , $id_tbs)
    {
        $request->validate([
            'booking_progress'             => 'required',
            'booking_justification'        => 'required',
        ]);

        $tbs = TBS::find($id_tbs);
        $tbs->booking_progress = $request->booking_progress;
        $tbs->booking_justification = $request->booking_justification;
        $tbs->status = "Booking Ended";
        $tbs->end_date = date("Y-m-d H:i:s");
        $tbs->save();

        foreach ($tbs->tbstransportuse as $key => $tbstransportuse) {
            $transport = Transport::find($tbstransportuse->id_transport);
            $transport->availability = "available";
            $transport->save();
        }
        
        
        $document = New DocumentLog;
        $document->id 				= Uuid::uuid4()->getHex();
        $document->user_type 		= auth()->user()->role->name;
        $document->id_user			= auth()->user()->id;
        $document->start_at 		= date('Y-m-d H:i:s');
        $document->end_at 			= null;
        $document->document_type 	= "TBS";
        $document->id_document 		=  $tbs->id;
        $document->remark 			= 'Transport Booking for Job Number : '.$tbs->job_number.' has updated the progress to '.$tbs->booking_progress.'';
        $document->status 			= "Booking Updated Progress";
        $document->id_notification 	= "";
        $document->created_by 		= auth()->user()->id;
        $document->updated_by 		= auth()->user()->id;
        $document->save();

        return response(['status' => 'OK' , 'message' => 'Successfully update booking progress']);
    }

    public function endBooking(Request $request , $id_tbs)
    {

        $request->validate([
            'finish_status'        => 'required',
            'img_update'           => 'image|max:1999', 
        ]);

        $tbs = TBS::find($id_tbs);

        if ($request->finish_status == 'Yes') 
        {
            $tbs->finish_status = "Yes"; 
            $tbs->status = "Booking Ended";
            $tbs->updated_by = auth()->user()->id;

            // Handle File Upload
            if($request->hasFile('img_update')){
                // Get filename with the extension
                $filenameWithExt = $request->file('img_update')->getClientOriginalName();
                // Get just filename
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                // Get just ext
                $extension = $request->file('img_update')->getClientOriginalExtension();
                // Filename to store
                $fileNameToStore= $tbs->id.'_'.time().'.'.$extension;
                // Upload Image
                $request->file('img_update')->storeAs('public'.DIRECTORY_SEPARATOR.'tbs', $fileNameToStore);
                
            } else {
                $fileNameToStore = 'noimage_'.$tbs->id.'_'.time().'.png';
                
                $img_path = public_path().''.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'tbs'.DIRECTORY_SEPARATOR.'noimage_'.$tbs->id.'_'.time().'.png';
                // $img_path = public_path().''.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'tbs'.DIRECTORY_SEPARATOR.'noimage_'.$tbs->id.'_'.time().'.png';
                copy(public_path().''.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'noimage.png' , $img_path);
            }

            //path
            
            $path = ''.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'tbs'.DIRECTORY_SEPARATOR.''.$fileNameToStore;
            // $path = ''.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'tbs'.DIRECTORY_SEPARATOR.''.$fileNameToStore;

            
            $tbs->img_update = $fileNameToStore;
            $tbs->img_path_update = $path;
            $tbs->save();

            foreach ($tbs->tbstransportuse as $key => $tbstransportuse) {
                $transport = Transport::find($tbstransportuse->id_transport);
                $transport->availability = "available";
                $transport->save();
            }

            $document = New DocumentLog;
            $document->id 				= Uuid::uuid4()->getHex();
            $document->user_type 		= auth()->user()->role->name;
            $document->id_user			= auth()->user()->id;
            $document->start_at 		= date('Y-m-d H:i:s');
            $document->end_at 			= null;
            $document->document_type 	= "TBS";
            $document->id_document 		=  $tbs->id;
            $document->remark 			= 'Transport Booking for Job Number : '.$tbs->job_number.' has successfully ended.';
            $document->status 			= "Booking Ended";
            $document->id_notification 	= "";
            $document->created_by 		= auth()->user()->id;
            $document->updated_by 		= auth()->user()->id;
            $document->save();
            
            return response(['status' => 'OK' , 'message' => 'Successfully end booking']);
        } 
        elseif ($request->finish_status == 'No') 
        {
            $request->validate([
                'finish_justification'     => 'required',
                'end_date'                 => 'required',
                'end_time'                 => 'required',
            ]);

            $tbs->finish_status = "No"; 
            $tbs->end_date = ''.date("Y-m-d", strtotime($request->end_date)).' '.date("H:i:s", strtotime($request->end_time)).'';
            $tbs->finish_justification = $request->finish_justification;
            $tbs->updated_by = auth()->user()->id;
            
            // Handle File Upload
            if($request->hasFile('img_update')){
                // Get filename with the extension
                $filenameWithExt = $request->file('img_update')->getClientOriginalName();
                // Get just filename
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                // Get just ext
                $extension = $request->file('img_update')->getClientOriginalExtension();
                // Filename to store
                $fileNameToStore= $tbs->id.'_'.time().'.'.$extension;
                // Upload Image
                $request->file('img_update')->storeAs('public'.DIRECTORY_SEPARATOR.'tbs', $fileNameToStore);
                
            } else {
                $fileNameToStore = 'noimage_'.$tbs->id.'_'.time().'.png';
                
                $img_path = public_path().''.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'tbs'.DIRECTORY_SEPARATOR.'noimage_'.$tbs->id.'_'.time().'.png';
                // $img_path = public_path().''.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'tbs'.DIRECTORY_SEPARATOR.'noimage_'.$tbs->id.'_'.time().'.png';
                copy(public_path().''.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'noimage.png' , $img_path);
            }

            //path
            $path = ''.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'tbs'.DIRECTORY_SEPARATOR.''.$fileNameToStore;
            // $path = ''.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'tbs'.DIRECTORY_SEPARATOR.''.$fileNameToStore;

            
            $tbs->img_update = $fileNameToStore;
            $tbs->img_path_update = $path;
            $tbs->save();


            foreach ($tbs->tbsdriver as $key => $tbsdriver) {
    
                //NOTIFICATION FCM SCHEDULE
                $noti = new Notification;
                $noti->to_user =  $tbsdriver->id_user;
                $noti->tiny_img_url = '';
                $noti->title = 'Vertigo [Transport Booking System]';
                $noti->desc = 'Have you completed the booking?';
                $noti->type = 'I';
                $noti->click_url = 'tbs-end';
                $noti->send_status = 'P';
                $noti->status = '';
                $noti->module = 'tbs';
                $noti->id_module = $tbs->id;
                $noti->created_by = auth()->user()->id;
                $json_noti = json_encode($noti);

                $scheduler = New Scheduler;
                $scheduler->id = Uuid::uuid4()->getHex();
                $scheduler->trigger_datetime = $tbs->end_date;
                $scheduler->url_to_call = 'triggeredNotification';
                $scheduler->secret_key = '';
                $scheduler->params = $json_noti;
                $scheduler->is_triggered = 0;
                $scheduler->created_by = auth()->user()->id;
                $scheduler->save();

            }

            $document = New DocumentLog;
            $document->id 				= Uuid::uuid4()->getHex();
            $document->user_type 		= auth()->user()->role->name;
            $document->id_user			= auth()->user()->id;
            $document->start_at 		= date('Y-m-d H:i:s');
            $document->end_at 			= null;
            $document->document_type 	= "TBS";
            $document->id_document 		=  $tbs->id;
            $document->remark 			= 'Transport Booking for Job Number : '.$tbs->job_number.' has place a new end date to '.date('j F Y , g:i a' , strtotime($tbs->end_date)).' ';
            $document->status 			= "Booking set a New End Date";
            $document->id_notification 	= "";
            $document->created_by 		= auth()->user()->id;
            $document->updated_by 		= auth()->user()->id;
            $document->save();

            return response(['status' => 'OK' , 'message' => 'Successfully extend end booking']);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
