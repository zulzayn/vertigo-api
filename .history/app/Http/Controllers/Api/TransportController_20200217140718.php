<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transports = Transport::all;

        return response()->json(['status' => 'OK' , 'transport' => $transports]);
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
            'name'                  => 'required',
            'img'                   => 'image|max:1999',   
            'plate_number'            => 'required', 
            'description'           => 'required', 
            'id_trans_category'     => 'required',
        ]);

        $transport = New Transport;
        $transport->id = Uuid::uuid4()->getHex();
        $transport->name = $request->name;

        // Handle File Upload
        if($request->hasFile('img')){
            // Get filename with the extension
            $filenameWithExt = $request->file('img')->getClientOriginalName();
            // Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just ext
            $extension = $request->file('img')->getClientOriginalExtension();
            // Filename to store
            $fileNameToStore= $transport->id.'_'.time().'.'.$extension;
            // Upload Image
            $request->file('img')->storeAs('public'.DIRECTORY_SEPARATOR.'transports', $fileNameToStore);
            
        } else {
            $fileNameToStore = 'noimage_'.$transport->id.'_'.time().'.png';
            $img_path = public_path().''.DIRECTORY_SEPARATOR.'/storage/transports/noimage_'.$equipment->id.'_'.time().'.png';
            copy(public_path().''.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'noimage.png' , $img_path);
        }

        //path
        $path = '/storage/transports/'.$fileNameToStore;
        
        $transport->img = $fileNameToStore;
        $transport->img_path = $path;
        $transport->plate_number = $request->plate_number;
        $transport->description = $request->description;
        $transport->id_trans_category = $request->id_trans_category;
        $transport->status = 'enable';
        $transport->created_by = auth()->user()->id;
        $transport->save();

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
        //
    }
}