<?php

namespace App\Http\Controllers;

use App\Models\UserDocuments;
use Illuminate\Http\Request;

class UserDocumentsController extends Controller
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
     * @param  \App\Models\UserDocuments  $userDocuments
     * @return \Illuminate\Http\Response
     */
    public function show(UserDocuments $userDocuments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserDocuments  $userDocuments
     * @return \Illuminate\Http\Response
     */
    public function edit(UserDocuments $userDocuments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserDocuments  $userDocuments
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserDocuments $userDocuments)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserDocuments  $userDocuments
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserDocuments $userDocuments)
    {
        //
    }



    public function documentStore(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|min:3|max:20",
            "description" => "nullable|min:3|max:100",
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'company_id' => 'required',
            'user_id' => 'required',
        ]);

        try {



            unset($validated["document"]);
            $validated["created_datetime"] = date("Y-m-d H:i:s");


            $document = UserDocuments::create($validated);
            if (isset($request->document)) {
                $file = $request->file('document');
                $ext = $file->getClientOriginalExtension();
                $fileName = $document->id . '.' . $ext;
                $request->file('document')->move(public_path('/security_documents'), $fileName);
                $validated['path'] = $fileName;

                $document = UserDocuments::where("id", $document->id)->update(["path" => $fileName]);
            }
            return $this->response("Document has been added", null, true);
        } catch (\Exception $e) {
            return $this->response("Document cannot add", $e->getMessage(), true);
        }
    }

    public function documentUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            "name" => "required|min:3|max:20",
            "description" => "nullable|min:3|max:100",
            'user_id' => 'required',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            if (isset($request->document)) {
                $file = $request->file('document');
                $ext = $file->getClientOriginalExtension();
                $fileName = $id . '.' . $ext;
                $request->file('document')->move(public_path('/user_documents'), $fileName);
                $validated['path'] = $fileName;
                $validated["updated_datetime"] = date("Y-m-d H:i:s");
            }

            unset($validated["document"]);

            UserDocuments::where("id", $id)->update($validated);
            return $this->response("Document has been updated", null, true);
        } catch (\Exception $e) {
            return $this->response("Document cannot update", $e->getMessage(), true);
        }
    }

    public function documentList(Request $request)
    {
        return UserDocuments::where("company_id", $request->company_id)->where("user_id", $request->user_id)->paginate($request->per_page ?? 100);
    }

    public function documentShow($id)
    {
        return UserDocuments::where("id", $id)->first();
    }

    public function documentDestroy($id)
    {
        try {
            UserDocuments::where("id", $id)->delete();
            return $this->response("Document has been deleted", null, true);
        } catch (\Throwable $th) {
            return $this->response("Document cannot delete", null, true);
        }
    }
}
