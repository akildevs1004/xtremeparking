<?php

namespace App\Http\Controllers;

use App\Models\UserContacts;
use Illuminate\Http\Request;

class UserContactsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * @param  \App\Models\UserContacts  $userContacts
     * @return \Illuminate\Http\Response
     */
    public function show(UserContacts $userContacts)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserContacts  $userContacts
     * @return \Illuminate\Http\Response
     */
    public function edit(UserContacts $userContacts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserContacts  $userContacts
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserContacts $userContacts)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserContacts  $userContacts
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserContacts $userContacts)
    {
        //
    }


    public function contactStore(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|min:3|max:20",

            'number' => 'required',
            'company_id' => 'required',
            'user_id' => 'required',
            "description" => "required",
        ]);

        try {




            $validated["created_datetime"] = date("Y-m-d H:i:s");


            $document = UserContacts::create($validated);

            return $this->response("Contact has been added", null, true);
        } catch (\Exception $e) {
            return $this->response("Contact cannot add", $e->getMessage(), true);
        }
    }

    public function contactUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            "name" => "required|min:3|max:20",
            "description" => "required",
            'user_id' => 'required',
            'number' => 'required',
        ]);

        try {




            UserContacts::where("id", $id)->update($validated);
            return $this->response("Contact has been updated", null, true);
        } catch (\Exception $e) {
            return $this->response("Contact cannot update", $e->getMessage(), true);
        }
    }

    public function contactList(Request $request)
    {
        return UserContacts::where("company_id", $request->company_id)->where("user_id", $request->user_id)->paginate($request->per_page ?? 100);
    }

    public function sContactShow($id)
    {
        return UserContacts::where("id", $id)->first();
    }

    public function contactDestroy($id)
    {
        try {
            UserContacts::where("id", $id)->delete();
            return $this->response("Contact has been deleted", null, true);
        } catch (\Throwable $th) {
            return $this->response("Contact cannot delete", null, true);
        }
    }
}
