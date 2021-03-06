<?php

namespace App\Http\Controllers\Api;

use App\MSS;
use App\SAS;
use App\TMS;
use App\Role;
use App\User;
use App\Scheduler;
use App\SASComment;
use App\DocumentLog;
use App\Notification;
use Ramsey\Uuid\Uuid;
use App\SASStaffAssign;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Comment as CommentResource;

class SASController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sas = SAS::all();

        return response(['status' => 'OK', 'message' =>  $sas]);
    }

    public function dashDate(Request $request)
    {

        $dateFrom = date($request->date_from);
        $dateTo = date($request->date_to);

        $sas = SAS::whereBetween('created_at', [$dateFrom, $dateTo])->get();

        return response(['status' => 'OK', 'message' => $sas]);
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

    public function addNewTask(Request $request)
    {
        $managers = Role::where('level', 1)->first();

        if ($managers) {

            $managers = $managers->user;

            if (count($managers) != 0) {

                $managers_array = [];

                foreach ($managers as $key => $manager) {
                    array_push($managers_array, $manager->id);
                }

                $request->validate([
                    'start_date'        => 'required',
                    'start_time'        => 'required',
                    'end_date'          => 'required',
                    'end_time'          => 'required',
                    'job_number'        => 'required',
                    'job_title'         => 'required',
                    'job_description'   => 'required',
                    'assign_staff.*'    => 'required',
                ]);

                $add = new SAS;
                $add->id = Uuid::uuid4()->getHex();
                $add->job_number = $request->job_number;
                $add->job_title = $request->job_title;
                $add->job_description = $request->job_description;
                $add->status = 'In Progress';
                $add->id_approver = json_encode($managers_array);
                $add->approval_status = 'Submitted for Approval';
                $add->created_by = auth()->user()->id;
                $add->save();

                foreach ($request->assign_staff as $key => $assign_staff) {
                    $add2 = new SASStaffAssign;
                    $add2->id = Uuid::uuid4()->getHex();
                    $add2->id_user = $assign_staff;
                    $add2->id_sas = $add->id;
                    $add2->start_date = '' . date("Y-m-d", strtotime($request->start_date)) . ' ' . date("H:i:s", strtotime($request->start_time)) . '';
                    $add2->end_date = '' . date("Y-m-d", strtotime($request->end_date)) . ' ' . date("H:i:s", strtotime($request->end_time)) . '';
                    $add2->status = "Created";
                    $add2->created_by = auth()->user()->id;
                    $add2->save();

                    $assignee = User::find($add2->id_user);

                    $document = new DocumentLog;
                    $document->id                 = Uuid::uuid4()->getHex();
                    $document->user_type         = auth()->user()->role->name;
                    $document->id_user            = auth()->user()->id;
                    $document->start_at         = date('Y-m-d H:i:s');
                    $document->end_at             = null;
                    $document->document_type     = "SAS";
                    $document->id_document         =  $add2->id;
                    $document->remark             = 'Create New Task for ' . $assignee->name . ' in Staff Assignment System';
                    $document->status             = "Created";
                    $document->id_notification     = "";
                    $document->created_by         = auth()->user()->id;
                    $document->updated_by         = auth()->user()->id;
                    $document->save();
                }

                foreach ($managers as $key => $manager) {

                    //NOTIFICATION FCM OTS
                    $noti = new Notification;
                    $noti->id = Uuid::uuid4()->getHex();
                    $noti->to_user = $manager->id;
                    $noti->tiny_img_url = '';
                    $noti->title = 'Vertigo [Staff Assignment Management]';
                    $noti->desc = 'A new created task needs your approval';
                    $noti->type = 'A';
                    $noti->click_url = 'sas-approve';
                    $noti->send_status = 'P';
                    $noti->status = '';
                    $noti->module = 'sas';
                    $noti->id_module = $add->id;
                    $noti->created_by = auth()->user()->id;
                    $noti->save();

                    $noti->notificationFCM($manager->device_token, $noti->title, $noti->desc, null, null, $noti->id_module, $noti->module);
                }


                return response(['status' => 'OK', 'message' => 'Successfully add new task']);
            } else {
                return response(['status' => 'OK', 'message' => 'No managers found in system, please register at least a manager to get their approval.']);
            }
        } else {
            return response(['status' => 'OK', 'message' => 'No managers found in system, please register at least a manager to get their approval.']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $sas = SAS::find($id);

        return response(['status' => 'OK', 'message' =>  $sas]);
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
        $sas = SAS::find($id);

        $request->validate([
            'start_date'        => 'required',
            'start_time'        => 'required',
            'end_date'          => 'required',
            'end_time'          => 'required',
            'job_number'        => 'required',
            'job_title'         => 'required',
            'job_description'   => 'required',
            'assign_staff.*'    => 'required',
        ]);


        $sas->job_number = $request->job_number;
        $sas->job_title = $request->job_title;
        $sas->job_description = $request->job_description;
        $sas->updated_by = auth()->user()->id;
        $sas->save();

        $sasstaffassigns = $sas->sasstaffassign;

        foreach ($sasstaffassigns as $key => $sasstaffassign) {
            $sasstaffassign->delete();
        }

        foreach ($request->assign_staff as $key => $assign_staff) {
            $add2 = new SASStaffAssign;
            $add2->id = Uuid::uuid4()->getHex();
            $add2->id_user = $assign_staff;
            $add2->id_sas = $sas->id;
            $add2->start_date = '' . date("Y-m-d", strtotime($request->start_date)) . ' ' . date("H:i:s", strtotime($request->start_time)) . '';
            $add2->end_date = '' . date("Y-m-d", strtotime($request->end_date)) . ' ' . date("H:i:s", strtotime($request->end_time)) . '';
            $add2->created_by = auth()->user()->id;
            $add2->updated_by = auth()->user()->id;
            $add2->save();

            $assignee = User::find($add2->id_user);

            $document = new DocumentLog;
            $document->id                 = Uuid::uuid4()->getHex();
            $document->user_type         = auth()->user()->role->name;
            $document->id_user            = auth()->user()->id;
            $document->start_at         = date('Y-m-d H:i:s');
            $document->end_at             = null;
            $document->document_type     = "SAS";
            $document->id_document         =  $add2->id;
            $document->remark             = 'Update Task for ' . $assignee->name . ' in Staff Assignment System';
            $document->status             = "Update";
            $document->id_notification     = "";
            $document->created_by         = auth()->user()->id;
            $document->updated_by         = auth()->user()->id;
            $document->save();
        }

        return response(['status' => 'OK', 'message' => 'Successfully edit task']);
    }


    public function getAvailableStaff($datefrom, $dateto)
    {


        $unavailableStaffs = SASStaffAssign::where('start_date', '<=', date('Y-m-d H:i:s', strtotime($datefrom)))
        ->where('end_date', '>=', date('Y-m-d H:i:s', strtotime($dateto)))
        ->orWhere('start_date', '<=', date('Y-m-d H:i:s', strtotime($dateto)))
        ->where('end_date', '>=', date('Y-m-d H:i:s', strtotime($datefrom)))
        ->get();

        $unavailableStaffsMSS = MSS::where('start_date', '<=', date('Y-m-d H:i:s', strtotime($datefrom)))
        ->where('end_date', '>=', date('Y-m-d H:i:s', strtotime($dateto)))
        ->orWhere('start_date', '<=', date('Y-m-d H:i:s', strtotime($dateto)))
        ->where('end_date', '>=', date('Y-m-d H:i:s', strtotime($datefrom)))
        ->get();

        $unavailableStaffsTMS = TMS::where('sitevisit_start_date', '<=', date('Y-m-d H:i:s', strtotime($datefrom)))
        ->where('sitevisit_end_date', '>=', date('Y-m-d H:i:s', strtotime($dateto)))
        ->orWhere('sitevisit_start_date', '<=', date('Y-m-d H:i:s', strtotime($dateto)))
        ->where('sitevisit_end_date', '>=', date('Y-m-d H:i:s', strtotime($datefrom)))
        ->get();

        
        $availableStaffs = array();
        $staffs = User::all();

        if (count($unavailableStaffs) == 0) {
            $i = 1;
            foreach ($staffs as $key => $staff) {

                $availableStaffs[] = [

                    "id"                    => $staff->id,
                    "name"                  => $staff->name,
                    "email"                 => $staff->email,
                    "email_verified_at"     => $staff->email_verified_at,
                    "created_at"            => $staff->created_at,
                    "updated_at"            => $staff->updated_at,
                    "status"                => $staff->status,
                    "availability"          => $staff->availability,
                    "id_role"               => $staff->id_role,
                    "id_position"           => $staff->id_position,
                    "id_department"         => $staff->id_department,
                    "id_access_role"        => $staff->id_access_role,
                    "id_access_position"    => $staff->id_access_position,
                    "last_log_web"          => $staff->last_log_web,
                    "last_log_mobile"       => $staff->last_log_mobile,
                    "created_by"            => $staff->created_by,
                    "updated_by"            => $staff->updated_by,
                    "device_token"          => $staff->device_token,
                    "img_name"              => $staff->img_name,
                    "img_path"              => $staff->img_path,
                    "staff_id"              => $staff->staff_id,
                    "first_name"            => $staff->first_name,
                    "last_name"             => $staff->last_name,
                    "id_inquiry"            => $staff->id_inquiry,


                ];

            
                $i++;
            }
        } else {


            $i = 1;
            foreach ($staffs as $key => $staff) {

                $availableStaffs[] = [
                    "id"                    => $staff->id,
                    "name"                  => $staff->name,
                    "email"                 => $staff->email,
                    "email_verified_at"     => $staff->email_verified_at,
                    "created_at"            => $staff->created_at,
                    "updated_at"            => $staff->updated_at,
                    "status"                => $staff->status,
                    "availability"          => $staff->availability,
                    "id_role"               => $staff->id_role,
                    "id_position"           => $staff->id_position,
                    "id_department"         => $staff->id_department,
                    "id_access_role"        => $staff->id_access_role,
                    "id_access_position"    => $staff->id_access_position,
                    "last_log_web"          => $staff->last_log_web,
                    "last_log_mobile"       => $staff->last_log_mobile,
                    "created_by"            => $staff->created_by,
                    "updated_by"            => $staff->updated_by,
                    "device_token"          => $staff->device_token,
                    "img_name"              => $staff->img_name,
                    "img_path"              => $staff->img_path,
                    "staff_id"              => $staff->staff_id,
                    "first_name"            => $staff->first_name,
                    "last_name"             => $staff->last_name,
                    "id_inquiry"            => $staff->id_inquiry,
                ];

                $i++;
            }

            $i = 1;
        
            foreach ($availableStaffs as $x => $availableStaff) {
                foreach ($unavailableStaffs as $y => $unavailableStaff) {
                    
                        if ($unavailableStaff->id_user == $availableStaffs[$x]['id']) {
                            $availableStaffs[$x]['id'] = '';
                        } else {

                        }
                     
                }

                $i++;
            }

            $i = 1;

            foreach ($availableStaffs as $x => $availableStaff) {
                foreach ($unavailableStaffsMSS as $y => $unavailableSMSS) {
                    foreach ($unavailableSMSS->msspic as $y => $unavailableStaffMSS) {
                        if ($unavailableStaffMSS->id_user == $availableStaffs[$x]['id']) {
                            $availableStaffs[$x]['id'] = '';
                        } else {

                        }
                    }   
                }

                $i++;
            }

            $i = 1;
            
            foreach ($availableStaffs as $x => $availableStaff) {
                foreach ($unavailableStaffsTMS as $y => $unavailableSTMS) {
                    foreach ($unavailableSTMS->pic as $y => $unavailableStaffTMS) {
                        if ($unavailableStaffTMS->id_user == $availableStaffs[$x]['id']) {
                            $availableStaffs[$x]['id'] = '';
                        } else {

                        }
                    }   
                }

                $i++;
            }

            foreach ($availableStaffs as $key => $availableStaff) {
                if ($availableStaffs[$key]['id'] == '') {
                    unset($availableStaffs[$key]);
                }
            }

            
        }

        $availableStaffs = array_values($availableStaffs);
        
        

        return response(['status' => 'OK', 'staffs' => $availableStaffs]);

    }

    public function approve($id_sas)
    {
        $sas = SAS::find($id_sas);

        if($sas->approval_status == 'Approved')
        {
            return response(['status' => 'OK', 'message' => 'SAS already approved.']);
        }
        elseif($sas->approval_status == 'Rejected')
        {
            return response(['status' => 'OK', 'message' => 'SAS already rejected.']);
        }
        

        $sas->approval_status = 'Approved';
        $sas->approved_by = auth()->user()->id;
        $sas->updated_by = auth()->user()->id;
        $sas->save();

        foreach ($sas->sasstaffassign as $key => $sasstaffassign) {

            $sasstaffassign->status = "Approved";
            $sasstaffassign->save();

            //NOTIFICATION FCM OTS
            $noti = new Notification;
            $noti->id = Uuid::uuid4()->getHex();
            $noti->to_user = $sasstaffassign->id_user;
            $noti->tiny_img_url = '';
            $noti->title = 'Vertigo [Staff Assignment Management]';
            $noti->desc = 'You have been assigned to a new task';
            $noti->type = 'I';
            $noti->click_url = 'sas-acknowledge';
            $noti->send_status = 'P';
            $noti->status = '';
            $noti->module = 'sas';
            $noti->id_module = $sas->id;
            $noti->created_by = auth()->user()->id;
            $noti->save();

            $noti->notificationFCM($sasstaffassign->user->device_token, $noti->title, $noti->desc, null, null, $noti->id_module, $noti->module);

            //NOTIFICATION FCM SCHEDULE
            $noti = new Notification;
            $noti->to_user = $sasstaffassign->id_user;
            $noti->tiny_img_url = '';
            $noti->title = 'Vertigo [Staff Assignment Management]';
            $noti->desc =  'Have you started the assigned task?';
            $noti->type = 'I';
            $noti->click_url = 'sas-start';
            $noti->send_status = 'P';
            $noti->status = '';
            $noti->module = 'sas';
            $noti->id_module = $sas->id;
            $noti->created_by = auth()->user()->id;
            $json_noti = json_encode($noti);

            $scheduler = new Scheduler;
            $scheduler->id = Uuid::uuid4()->getHex();
            $scheduler->trigger_datetime = $sasstaffassign->start_date;
            $scheduler->url_to_call = 'triggeredNotification';
            $scheduler->secret_key = '';
            $scheduler->params = $json_noti;
            $scheduler->is_triggered = 0;
            $scheduler->created_by = auth()->user()->id;
            $scheduler->save();

            $assignee = User::find($sasstaffassign->id_user);

            $document = new DocumentLog;
            $document->id                 = Uuid::uuid4()->getHex();
            $document->user_type         = auth()->user()->role->name;
            $document->id_user            = auth()->user()->id;
            $document->start_at         = date('Y-m-d H:i:s');
            $document->end_at             = null;
            $document->document_type     = "SAS";
            $document->id_document         = $sasstaffassign->id;
            $document->remark             = 'Approve Task for ' . $assignee->name . ' with Job Number : ' . $sas->job_number . ' in Staff Assignment System';
            $document->status             = $sasstaffassign->status;
            $document->id_notification     = "";
            $document->created_by         = auth()->user()->id;
            $document->updated_by         = auth()->user()->id;
            $document->save();
        }



        return response(['status' => 'OK', 'message' => 'Successfully approve task']);
    }

    public function reject($id_sas)
    {
        $sas = SAS::find($id_sas);

        if($sas->approval_status == 'Rejected')
        {
            return response(['status' => 'OK', 'message' => 'SAS already rejected.']);
        }
        elseif($sas->approval_status == 'Approved')
        {
            return response(['status' => 'OK', 'message' => 'SAS already approved.']);
        }
       

        $sas->approval_status = 'Rejected';
        $sas->rejected_by = auth()->user()->id;
        $sas->updated_by = auth()->user()->id;
        $sas->save();


        foreach ($sas->sasstaffassign as $key => $sasstaffassign) {

            $sasstaffassign->status = "Rejected";
            $sasstaffassign->save();

            $assignee = User::find($sasstaffassign->id_user);

            $document = new DocumentLog;
            $document->id                 = Uuid::uuid4()->getHex();
            $document->user_type         = auth()->user()->role->name;
            $document->id_user            = auth()->user()->id;
            $document->start_at         = date('Y-m-d H:i:s');
            $document->end_at             = null;
            $document->document_type     = "SAS";
            $document->id_document         = $sasstaffassign->id;
            $document->remark             = 'Reject Task for ' . $assignee->name . ' with Job Number : ' . $sas->job_number . ' in Staff Assignment System';
            $document->status             = $sasstaffassign->status;
            $document->id_notification     = "";
            $document->created_by         = auth()->user()->id;
            $document->updated_by         = auth()->user()->id;
            $document->save();
        }

        return response(['status' => 'OK', 'message' => 'Successfully reject task']);
    }

    public function acknowledge($id_sas_assign_staff)
    {
        $sasassignstaff = SASStaffAssign::find($id_sas_assign_staff);

        // if($sasassignstaff->status == 'Acknowledge')
        // {
        //     return response(['status' => 'OK', 'message' => 'SAS Task already acknowledge.']);
        // }
        // elseif($sasassignstaff->status == 'Task Start')
        // {
        //     return response(['status' => 'OK', 'message' => 'SAS Task already acknowledge.']);
        // }
        // elseif($sasassignstaff->status == 'Task Finish')
        // {
        //     return response(['status' => 'OK', 'message' => 'SAS Task already acknowledge.']);
        // }

        $sasassignstaff->status = "Acknowledge";
        $sasassignstaff->acknowledge_status = '1';
        $sasassignstaff->updated_by = auth()->user()->id;
        $sasassignstaff->save();

        $document = new DocumentLog;
        $document->id                 = Uuid::uuid4()->getHex();
        $document->user_type         = auth()->user()->role->name;
        $document->id_user            = auth()->user()->id;
        $document->start_at         = date('Y-m-d H:i:s');
        $document->end_at             = null;
        $document->document_type     = "SAS";
        $document->id_document         = $sasassignstaff->id;
        $document->remark             = 'Acknowledge Task in Staff Assignment System';
        $document->status             = $sasassignstaff->status;
        $document->id_notification     = "";
        $document->created_by         = auth()->user()->id;
        $document->updated_by         = auth()->user()->id;
        $document->save();

        return response(['status' => 'OK', 'message' => 'Successfully acknowledge task']);
    }

    public function startTask(Request $request, $id_sas_assign_staff)
    {
        $sasassignstaff = SASStaffAssign::find($id_sas_assign_staff);

        $request->validate([
            'start_task'        => 'required',
        ]);

        if ($request->start_task == 'Yes') {

            // if($sasassignstaff->start_task == 'Yes')
            // {
            //     return response(['status' => 'OK', 'message' => 'SAS Task already start.']);
            // }
           

            $sasassignstaff->status = "Task Start";
            $sasassignstaff->start_task = $request->start_task;
            $sasassignstaff->updated_by = auth()->user()->id;
            $sasassignstaff->save();

            
            //NOTIFICATION FCM SCHEDULE
            $noti = new Notification;
            $noti->to_user = $sasassignstaff->id_user;
            $noti->tiny_img_url = '';
            $noti->title = 'Vertigo [Staff Assignment Management]';
            $noti->desc =  'Have you finished the assigned task?';
            $noti->type = 'I';
            $noti->click_url = 'sas-end';
            $noti->send_status = 'P';
            $noti->status = '';
            $noti->module = 'sas';
            $noti->id_module = $sasassignstaff->sas->id;
            $noti->created_by = auth()->user()->id;
            $json_noti = json_encode($noti);

            $scheduler = new Scheduler;
            $scheduler->id = Uuid::uuid4()->getHex();
            $scheduler->trigger_datetime = $sasassignstaff->end_date;
            $scheduler->url_to_call = 'triggeredNotification';
            $scheduler->secret_key = '';
            $scheduler->params = $json_noti;
            $scheduler->is_triggered = 0;
            $scheduler->created_by = auth()->user()->id;
            $scheduler->save();


            $document = new DocumentLog;
            $document->id                 = Uuid::uuid4()->getHex();
            $document->user_type         = auth()->user()->role->name;
            $document->id_user            = auth()->user()->id;
            $document->start_at         = date('Y-m-d H:i:s');
            $document->end_at             = null;
            $document->document_type     = "SAS";
            $document->id_document         = $sasassignstaff->id;
            $document->remark             = 'Start Task in Staff Assignment System';
            $document->status             = $sasassignstaff->status;
            $document->id_notification     = "";
            $document->created_by         = auth()->user()->id;
            $document->updated_by         = auth()->user()->id;
            $document->save();

            return response(['status' => 'OK', 'message' => 'Successfully start task']);
        } elseif ($request->start_task == 'No') {


            $request->validate([
                'justification_start'        => 'required',
                'start_date'                 => 'required',
                'start_time'                 => 'required',
            ]);

            $sasassignstaff->start_task = $request->start_task;
            $sasassignstaff->start_date = '' . date("Y-m-d", strtotime($request->start_date)) . ' ' . date("H:i:s", strtotime($request->start_time)) . '';
            $sasassignstaff->justification_start = $request->justification_start;
            $sasassignstaff->updated_by = auth()->user()->id;
            $sasassignstaff->save();


            //NOTIFICATION FCM SCHEDULE
            $noti = new Notification;
            $noti->to_user = $sasassignstaff->id_user;
            $noti->tiny_img_url = '';
            $noti->title = 'Vertigo [Staff Assignment Management]';
            $noti->desc =  'Have you started the assigned task?';
            $noti->type = 'I';
            $noti->click_url = 'sas-start';
            $noti->send_status = 'P';
            $noti->status = '';
            $noti->module = 'sas';
            $noti->id_module = $sasassignstaff->sas->id;
            $noti->created_by = auth()->user()->id;
            $json_noti = json_encode($noti);

            $scheduler = new Scheduler;
            $scheduler->id = Uuid::uuid4()->getHex();
            $scheduler->trigger_datetime = $sasassignstaff->start_date;
            $scheduler->url_to_call = 'triggeredNotification';
            $scheduler->secret_key = '';
            $scheduler->params = $json_noti;
            $scheduler->is_triggered = 0;
            $scheduler->created_by = auth()->user()->id;
            $scheduler->save();



            $document = new DocumentLog;
            $document->id                 = Uuid::uuid4()->getHex();
            $document->user_type         = auth()->user()->role->name;
            $document->id_user            = auth()->user()->id;
            $document->start_at         = date('Y-m-d H:i:s');
            $document->end_at             = null;
            $document->document_type     = "SAS";
            $document->id_document         = $sasassignstaff->id;
            $document->remark             = 'Set a new Start Date for Task in Staff Assignment System';
            $document->status             = $sasassignstaff->status;
            $document->id_notification     = "";
            $document->created_by         = auth()->user()->id;
            $document->updated_by         = auth()->user()->id;
            $document->save();

            return response(['status' => 'OK', 'message' => 'Successfully extend start task']);
        }
    }

    public function updateProgress(Request $request, $id_sas_assign_staff)
    {
        $request->validate([
            'task_progress'             => 'required',
            'justification_update'      => 'required',
            'img_update'                => 'image|max:1999',
        ]);

        $sasassignstaff = SASStaffAssign::find($id_sas_assign_staff);

        // if($sasassignstaff->task_progress != '' || $sasassignstaff->task_progress != null)
        // {
        //     return response(['status' => 'OK', 'message' => 'SAS Task Update have been choose']);
        // }
           

        $sasassignstaff->task_progress = $request->task_progress;
        $sasassignstaff->justification_update = $request->justification_update;
        $sasassignstaff->status = $request->task_progress;

        // Handle File Upload
        if ($request->hasFile('img_update')) {
            // Get filename with the extension
            $filenameWithExt = $request->file('img_update')->getClientOriginalName();
            // Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just ext
            $extension = $request->file('img_update')->getClientOriginalExtension();
            // Filename to store
            $fileNameToStore = $sasassignstaff->id . '_' . time() . '.' . $extension;
            // Upload Image
            $request->file('img_update')->storeAs('public' . DIRECTORY_SEPARATOR . 'sas', $fileNameToStore);
        } else {
            $fileNameToStore = 'noimage_' . $sasassignstaff->id . '_' . time() . '.png';

            $img_path = public_path() . '' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sas' . DIRECTORY_SEPARATOR . 'noimage_' . $sasassignstaff->id . '_' . time() . '.png';
            // $img_path = public_path().''.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'sas'.DIRECTORY_SEPARATOR.'noimage_'.$sasassignstaff->id.'_'.time().'.png';
            copy(public_path() . '' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'noimage.png', $img_path);
        }

        //path
        $path = '' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sas' . DIRECTORY_SEPARATOR . '' . $fileNameToStore;
        // $path = ''.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'sas'.DIRECTORY_SEPARATOR.''.$fileNameToStore;


        $sasassignstaff->img_update = $fileNameToStore;
        $sasassignstaff->img_path_update = $path;
        $sasassignstaff->save();

        $document = new DocumentLog;
        $document->id                 = Uuid::uuid4()->getHex();
        $document->user_type         = auth()->user()->role->name;
        $document->id_user            = auth()->user()->id;
        $document->start_at         = date('Y-m-d H:i:s');
        $document->end_at             = null;
        $document->document_type     = "SAS";
        $document->id_document         = $sasassignstaff->id;
        $document->remark             = 'Update Progress in Staff Assignment System';
        $document->status             = $sasassignstaff->status;
        $document->id_notification     = "";
        $document->created_by         = auth()->user()->id;
        $document->updated_by         = auth()->user()->id;
        $document->save();

        return response(['status' => 'OK', 'message' => 'Successfully update task progress']);
    }

    public function endTask(Request $request, $id_sas_assign_staff)
    {
        $sasassignstaff = SASStaffAssign::find($id_sas_assign_staff);

        // if($sasassignstaff->status == 'Task Finish')
        // {
        //     return response(['status' => 'OK', 'message' => 'Task already finish']);
        // }

        $request->validate([
            'finish_task'        => 'required',
        ]);

        if ($request->finish_task == 'Yes') {
            $sasassignstaff->status = "Task Finish";
            $sasassignstaff->finish_task = $request->finish_task;
            $sasassignstaff->updated_by = auth()->user()->id;
            $sasassignstaff->save();

            $sasassignstaff = SASStaffAssign::find($id_sas_assign_staff);
            $sas = $sasassignstaff->sas;
            $count = 0;

            foreach ($sas->sasstaffassign as $key => $sasstaffassign) {
                if ($sasstaffassign->status == 'Task Finish') {
                    $count++;
                }
            }

            if (count($sas->sasstaffassign) == $count) {
                $sas->status = 'Task Finish';
                $sas->save();
            }

            $document = new DocumentLog;
            $document->id                 = Uuid::uuid4()->getHex();
            $document->user_type         = auth()->user()->role->name;
            $document->id_user            = auth()->user()->id;
            $document->start_at         = date('Y-m-d H:i:s');
            $document->end_at             = null;
            $document->document_type     = "SAS";
            $document->id_document         = $sasassignstaff->id;
            $document->remark             = 'Finish Task in Staff Assignment System';
            $document->status             = $sasassignstaff->status;
            $document->id_notification     = "";
            $document->created_by         = auth()->user()->id;
            $document->updated_by         = auth()->user()->id;
            $document->save();

            return response(['status' => 'OK', 'message' => 'Successfully end task']);
        } elseif ($request->finish_task == 'No') {
            $request->validate([
                'justification_finish'        => 'required',
                'end_date'                    => 'required',
                'end_time'                    => 'required',
            ]);

            $sasassignstaff->finish_task = $request->finish_task;
            $sasassignstaff->justification_finish = $request->justification_finish;
            $sasassignstaff->end_date = '' . date("Y-m-d", strtotime($request->end_date)) . ' ' . date("H:i:s", strtotime($request->end_time)) . '';
            $sasassignstaff->updated_by = auth()->user()->id;
            $sasassignstaff->save();


            //NOTIFICATION FCM SCHEDULE
            $noti = new Notification;
            $noti->to_user = $sasassignstaff->id_user;
            $noti->tiny_img_url = '';
            $noti->title = 'Vertigo [Staff Assignment Management]';
            $noti->desc =  'Have you finished the assigned task?';
            $noti->type = 'I';
            $noti->click_url = 'sas-end';
            $noti->send_status = 'P';
            $noti->status = '';
            $noti->module = 'sas';
            $noti->id_module = $sasassignstaff->sas->id;
            $noti->created_by = auth()->user()->id;
            $json_noti = json_encode($noti);

            $scheduler = new Scheduler;
            $scheduler->id = Uuid::uuid4()->getHex();
            $scheduler->trigger_datetime = $sasassignstaff->end_date;
            $scheduler->url_to_call = 'triggeredNotification';
            $scheduler->secret_key = '';
            $scheduler->params = $json_noti;
            $scheduler->is_triggered = 0;
            $scheduler->created_by = auth()->user()->id;
            $scheduler->save();


            $document = new DocumentLog;
            $document->id                 = Uuid::uuid4()->getHex();
            $document->user_type         = auth()->user()->role->name;
            $document->id_user            = auth()->user()->id;
            $document->start_at         = date('Y-m-d H:i:s');
            $document->end_at             = null;
            $document->document_type     = "SAS";
            $document->id_document         = $sasassignstaff->id;
            $document->remark             = 'Set a new End Date for Task in Staff Assignment System';
            $document->status             = $sasassignstaff->status;
            $document->id_notification     = "";
            $document->created_by         = auth()->user()->id;
            $document->updated_by         = auth()->user()->id;
            $document->save();

            return response(['status' => 'OK', 'message' => 'Successfully extend end task']);
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

    public function commentSas(Request $request, $id)
    {
        $request->validate([
            'comment'        => 'required',
        ]);

        $sasstaffassign = SASStaffAssign::find($id);

        $cmt = new SASComment;
        $cmt->id = Uuid::uuid4()->getHex();
        $cmt->id_sas_staff_assign = $sasstaffassign->id;
        $cmt->id_user_comment = auth()->user()->id;
        $cmt->comment = $request->comment;
        $cmt->created_by = auth()->user()->id;
        $cmt->save();

        //Notification OTS
        $noti = new Notification;
        $noti->id = Uuid::uuid4()->getHex();
        $noti->to_user =  $sasstaffassign->id_user;
        $noti->tiny_img_url = '';
        $noti->title = 'Vertigo [Staff Assignment Management]';
        $noti->desc = '' . auth()->user()->name . ' comment on your task.';
        $noti->type = 'I';
        $noti->click_url = '';
        $noti->send_status = 'P';
        $noti->status = '';
        $noti->module = 'sas';
        $noti->id_module = $sasstaffassign->sas->id;
        $noti->created_by = auth()->user()->id;
        $noti->save();

        $noti->notificationFCM($sasstaffassign->user->device_token, $noti->title, $noti->desc, null, null, $noti->id_module, $noti->module);

        return response(['status' => 'OK', 'message' => 'Successfully comment on task']);
    }
    public function commentShowBySas($id_sas)
    {
        // $sas = SAS::find($id_sas);
        
        // $buttons = array();

        // foreach ($sas->sasstaffassign as $key => $sassa) {
        //     foreach ($variable as $key => $value) {
                
        //     }
        //     $buttons[] = [
        //         'id' => $sassa->id,
        //         'id_sas_staff_assign' => "file-medical",
        //         'id_user_comment' => 'title="Add Certificate"',
        //         'comment' => ,
        //         'status' => ,
        //         'created_by' => ,
        //         'updated_by' => ,
        //         'created_at' => ,
        //         'updated_at' => ,
        //     ];
        // }
        

        // return response(['status' => 'OK' , 'message' => $sas]); 
    }

    public function commentShowBySassa($id_sassa)
    {
        $sassa = SASStaffAssign::find($id_sassa);

        return response(['status' => 'OK', 'message' => CommentResource::collection($sassa->sascomment)]);
    }

    public function getIdStaffAssign(Request $request)
    {
        $sas = SASStaffAssign::whereIdUser($request->user_id)
            ->whereIdSas($request->sas_id)
            ->first();

        return response([
            'status' => 'OK',
            'message' => 'Successfully comment on task',
            'data' => $sas->id,
        ]);
    }
}
