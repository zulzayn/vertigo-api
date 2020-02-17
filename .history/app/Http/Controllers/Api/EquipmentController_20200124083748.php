<?php

namespace App\Http\Controllers\Api;

use App\Equipment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $equipments = Equipment::all();

        return response(['status' => 'OK' , 'equipments' => $equipments]);
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
            'img'                   => 'required|image|max:1999',   
            'tag_number'            => 'required', 
            'description'           => 'required', 
            'id_equip_category'     => 'required',
        ]);

        $equipment = New Equipment;
        $equipment->id = Uuid::uuid4()->getHex();
        $equipment->name = $request->name;
        $equipment->tag_number = $request->tag_number;
        $equipment->description = $request->description;
        $equipment->id_equip_category = $request->id_equip_category;
        $equipment->status = 'enable';
        $equipment->created_by = auth()->user()->id;
        $equipment->save();

        return response(['status' => 'OK' , 'message' => 'Successfully create equipment']);
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