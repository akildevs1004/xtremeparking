<?php

namespace App\Http\Controllers;

use App\Models\CompanyDocuments;
use Illuminate\Http\Request;

class CompanyDocumentsController extends Controller
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
     * @param  \App\Models\CompanyDocuments  $companyDocuments
     * @return \Illuminate\Http\Response
     */
    public function show(CompanyDocuments $companyDocuments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CompanyDocuments  $companyDocuments
     * @return \Illuminate\Http\Response
     */
    public function edit(CompanyDocuments $companyDocuments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CompanyDocuments  $companyDocuments
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CompanyDocuments $companyDocuments)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CompanyDocuments  $companyDocuments
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompanyDocuments $companyDocuments)
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
        ]);

        try {



            unset($validated["document"]);
            $validated["created_datetime"] = date("Y-m-d H:i:s");


            $document = CompanyDocuments::create($validated);

            (new ActivityController())->recordNewActivity($request, "create", "Company Document Created", $document->id,  "company_documents", null, null);
            if (isset($request->document)) {
                $file = $request->file('document');
                $ext = $file->getClientOriginalExtension();
                $fileName = $document->id . '.' . $ext;
                $request->file('document')->move(public_path('/company_documents'), $fileName);
                $validated['path'] = $fileName;

                $document = CompanyDocuments::where("id", $document->id)->update(["path" => $fileName]);
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
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            if (isset($request->document)) {
                $file = $request->file('document');
                $ext = $file->getClientOriginalExtension();
                $fileName = $id . '.' . $ext;
                $request->file('document')->move(public_path('/company_documents'), $fileName);
                $validated['path'] = $fileName;
                $validated["updated_datetime"] = date("Y-m-d H:i:s");
            }

            unset($validated["document"]);

            CompanyDocuments::where("id", $id)->update($validated);
            (new ActivityController())->recordNewActivity($request, "update", "Company Document Updated", $id,  "company_documents", null, null);
            return $this->response("Document has been updated", null, true);
        } catch (\Exception $e) {
            return $this->response("Document cannot update", $e->getMessage(), true);
        }
    }

    public function documentList(Request $request)
    {
        return CompanyDocuments::where("company_id", $request->company_id)->paginate($request->per_page ?? 100);
    }

    public function documentShow($id)
    {
        return CompanyDocuments::where("id", $id)->first();
    }

    public function documentDestroy(Request $reqeust, $id)
    {
        try {
            CompanyDocuments::where("id", $id)->delete();
            (new ActivityController())->recordNewActivity($reqeust, "delete", "Company Document Deleted",    $id, "company_documents", null, null);

            return $this->response("Document has been deleted", null, true);
        } catch (\Throwable $th) {
            return $this->response("Document cannot delete", null, true);
        }
    }

    public function getEncodedLogo()
    {
        $url = request("url", 'https://hms-backend.test/upload/1743250338.jpeg');

        $context = stream_context_create([
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ]);

        $imageData = file_get_contents($url, false, $context);

        $md5string = base64_encode($imageData);

        return "data:image/png;base64,$md5string";
    }
}
