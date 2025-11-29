<?php

namespace App\Http\Controllers;

use App\Http\Requests\Branch\BranchContactRequest;
use App\Http\Requests\Branch\BranchRequest;
use App\Http\Requests\Branch\BranchUserRequest;
use App\Http\Requests\Branch\BranchUserUpdateRequest;
use App\Models\Branch;
use App\Models\BranchContact;
use App\Models\CompanyContact;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class BranchController extends Controller
{

    public function index(Request $request)
    {
        return Branch::with(['user', 'contact'])->orderByDesc('id')->paginate($request->perPage);
    }
    public function validateBranch(BranchRequest $request): JsonResponse
    {
        $data = $request->all();

        unset($data['login_user_id']);
        unset($data['login_user_type']);

        return Response::json([
            'record' => $data,
            'message' => 'Branch Successfully Validated.',
            'status' => true
        ], 200);
    }

    public function validateContact(BranchContactRequest $request): JsonResponse
    {
        $data['name'] = $request->contact_name;
        $data['number'] = $request->contact_no;
        $data['position'] = $request->contact_position;
        $data['whatsapp'] = $request->contact_whatsapp;
        return Response::json([
            'record' => $data,
            'message' => 'Contact successfully validated.',
            'status' => true
        ], 200);
    }

    public function validateBranchUser(BranchUserRequest $request): JsonResponse
    {
        $data = $request->except(['user_name', 'company_id', 'password_confirmation']);
        $data['name'] = $request->user_name;
        $data['password'] = Hash::make($request->password);
        return Response::json([
            'record' => $data,
            'message' => 'User successfully validated.',
            'status' => true
        ], 200);
    }

    public function validateBranchUserUpdate(BranchUserUpdateRequest $request): JsonResponse
    {
        $data = $request->except(['user_name',]);
        $data['name'] = $request->user_name;
        if (isset($request->password)) {
            $data['password'] = Hash::make($request->password);
        }
        return Response::json([
            'record' => $data,
            'message' => 'User successfully validated.',
            'status' => true
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->except(['contact_name', 'contact_no', 'contact_position', 'contact_whatsapp', 'user_name', 'email', 'password_confirmation', 'password']);

        if (isset($request->logo)) {
            $data['logo'] =  saveFileHelper($request, 'media/company/' . $request->company_id . '/branch/logo', 'logo', $request->name, 'logo');
        }

        DB::beginTransaction();

        try {
            $branch = Branch::create($data);
            //  Contact
            $contact['branch_id'] = $branch->id;
            $contact['name'] = $request->contact_name;
            $contact['number'] = $request->contact_no;
            $contact['position'] = $request->contact_position;
            $contact['whatsapp'] = $request->contact_whatsapp;
            $record = BranchContact::create($contact);
            // USER
            $user['name'] = $request->user_name;
            $user['password'] = Hash::make($request->password);
            $user['email'] = $request->email;
            $record = User::create($user);
            // $record->assignRole('company');
            Branch::find($branch->id)->update(['user_id' => $record->id]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        $branch->logo = asset('media/company' . $request->company_id . '/branch/logo' . $branch->logo);

        return Response::json([
            'record' => Branch::with(['user', 'contact'])->find($branch->id),
            'message' => 'Branch Successfully created.',
            'status' => true
        ], 200);
    }
    function saveFileHelper_OLD($request, $destination, $attribute_name = null, $prefix = "", $sufix = "", $imageObj = null, $return_ext = false)
    {
        if (isset($imageObj) && !empty($imageObj) && $attribute_name == null) {
            $temp = $imageObj;
            $file = $imageObj->getClientOriginalName();
            $file_ext = $imageObj->getClientOriginalExtension();
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            $image = ((!empty($prefix)) ? (str_ireplace(" ", "-", $prefix) . "-") : "") . str_ireplace(" ", "-", $fileName) . ((!empty($sufix)) ? "-" . str_ireplace(" ", "-", $sufix) : "") . "." . $file_ext;
            $temp->move($destination, $image);
        } else if (isset($attribute_name) && $request->hasFile($attribute_name) && $attribute_name != null) {
            $temp = $request->file($attribute_name);
            $file = $request->$attribute_name->getClientOriginalName();
            $file_ext = $request->$attribute_name->getClientOriginalExtension();
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            $image = ((!empty($prefix)) ? (str_ireplace(" ", "-", $prefix) . "-") : "") . str_ireplace(" ", "-", $fileName) . ((!empty($sufix)) ? "-" . str_ireplace(" ", "-", $sufix) : "") . "." . $file_ext;
            $temp->move($destination, $image);
        }

        if ($return_ext) {
            return ["name" => (isset($image)) ? $image : null, "ext" => (isset($file_ext)) ? $file_ext : null];
        }
        return (isset($image)) ? $image : null;
    }
    public function show($id)
    {
        $record = Branch::with(['user', 'contact'])->where('id', $id)->first();
        return Response::json([
            'record' => $record,
            'status' => true,
            'message' => null
        ], 200);
    }

    public function destroy($id)
    {
        $record = Branch::find($id);
        $user = User::find($record->user_id);
        $contact = CompanyContact::where('company_id', $id);
        if ($contact->delete()) {
            $record->delete();
            $user->delete();
            return Response::noContent(204);
        } else
            return Response::json(['message' => 'No such record found.'], 404);
    }

    public function search(Request $request, $key)
    {
        $model = Branch::query();

        $model
            ->where('id', 'LIKE', "%$key%")
            ->orWhere('name', 'LIKE', "%$key%")
            ->orWhere('location', 'LIKE', "%$key%")

            ->orWhereHas('contact', function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%');
                $query->where('number', 'like', '%' . $key . '%');
                $query->where('position', 'like', '%' . $key . '%');
                $query->where('whatsapp', 'like', '%' . $key . '%');
            })
            ->orWhereHas('user', function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%');
                $query->orWhere('email', 'like', '%' . $key . '%');
            });

        return $model->orderBy('id', 'desc')->paginate($request->perPage);
    }
    function saveFileHelper_old($request, $destination, $attribute_name = null, $prefix = "", $sufix = "", $imageObj = null, $return_ext = false)
    {
        if (isset($imageObj) && !empty($imageObj) && $attribute_name == null) {
            $temp = $imageObj;
            $file = $imageObj->getClientOriginalName();
            $file_ext = $imageObj->getClientOriginalExtension();
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            $image = ((!empty($prefix)) ? (str_ireplace(" ", "-", $prefix) . "-") : "") . str_ireplace(" ", "-", $fileName) . ((!empty($sufix)) ? "-" . str_ireplace(" ", "-", $sufix) : "") . "." . $file_ext;
            $temp->move($destination, $image);
        } else if (isset($attribute_name) && $request->hasFile($attribute_name) && $attribute_name != null) {
            $temp = $request->file($attribute_name);
            $file = $request->$attribute_name->getClientOriginalName();
            $file_ext = $request->$attribute_name->getClientOriginalExtension();
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            $image = ((!empty($prefix)) ? (str_ireplace(" ", "-", $prefix) . "-") : "") . str_ireplace(" ", "-", $fileName) . ((!empty($sufix)) ? "-" . str_ireplace(" ", "-", $sufix) : "") . "." . $file_ext;
            $temp->move($destination, $image);
        }

        if ($return_ext) {
            return ["name" => (isset($image)) ? $image : null, "ext" => (isset($file_ext)) ? $file_ext : null];
        }
        return (isset($image)) ? $image : null;
    }
    public function update(BranchRequest $request, $id): JsonResponse
    {
        $data = $request->except('company_id');

        $record = Branch::find($id);
        if (isset($request->logo)) {
            $data['logo'] =  saveFileHelper($request, 'media/company/' . $record->company_id . '/branch/logo', 'logo', $request->name, 'logo');
        }
        $record->update($data);
        return Response::json([
            'record' => $record,
            'message' => 'Branch Successfully Updated.',
            'status' => true
        ], 200);
    }

    public function updateContact(BranchContactRequest $request, $id): JsonResponse
    {
        $data['name'] = $request->contact_name;
        $data['number'] = $request->contact_no;
        $data['position'] = $request->contact_position;
        $data['whatsapp'] = $request->contact_whatsapp;
        $record = BranchContact::where('branch_id', $id)->first();
        $record->update($data);
        return Response::json([
            'record' => $record,
            'message' => 'Contact successfully updated.',
            'status' => true
        ], 200);
    }

    public function updateBranchUserUpdate(BranchUserUpdateRequest $request, $id): JsonResponse
    {
        $data = $request->except(['user_name']);
        $data['name'] = $request->user_name;
        if (isset($request->password)) {
            $data['password'] = Hash::make($request->password);
        }
        $record = User::find(Branch::find($id)->user_id);
        $record->update($data);
        return Response::json([
            'record' => $data,
            'message' => 'User successfully updated.',
            'status' => true
        ], 200);
    }
}
