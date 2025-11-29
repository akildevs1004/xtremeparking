<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function create_user($request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:4',
                'company_id' => "required",
                "user_type" => "required"
            ]);

            $validatedData["name"] = $request->name ?? 'Hello';
            $validatedData["password"] = Hash::make($validatedData["password"]);
            return User::create($validatedData) ? true : false;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function index(User $model, Request $request)
    {
        $model = $this->FilterCompanyList($model, $request);

        $model->where("role_id", "!=", 1);
        $model->where("user_type", "company");
        // $model->whereHas("role", function ($q) {
        //     return $q->where('name', '!=', "company");
        // });

        $model->with("role");

        return $model->paginate($request->per_page);
    }
    public function store(User $model, StoreRequest $request)
    {
        try {
            $data = $request->validated();
            $data["password"] = Hash::make($data["password"]);

            if ($request->company_id) {
                $data["company_id"] = $request->company_id;
            }

            $record = $model->create($data);

            if ($record) {
                return $this->response('User successfully added.', $record, true);
            } else {
                return $this->response('User cannot add.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(User $user, UpdateRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->password) {
                $data["password"] = Hash::make($data["password"]);
            }

            $record = $user->update($data);

            if ($record) {
                return $this->response('User successfully updated.', $record, true);
            } else {
                return $this->response('User cannot update.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show(User $User)
    {
        return $User;
    }

    public function destroy(User $user)
    {
        try {
            $record = $user->delete();

            if ($record) {
                return $this->response('User successfully deleted.', null, true);
            } else {
                return $this->response('User cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function deleteSelected(Request  $request, $id)
    {
        if ($id > 0) {

            $model = User::where("id", $id)->first();;
            if ($model->picture) {
                $filepath = public_path('/users') . "/" . $model->picture;
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
            $return = User::where("id", $id)->delete();
            return $this->response('User account is deleted Successfully', null, true);
        }
    }
    // public function deleteSelected(User $model, Request $request, $id)
    // {
    //     $id = $request->id;
    //     if ($id > 0) {

    //         $model = User::where("id", $id)->first();;
    //         $filepath = public_path('/users') . "/" . $model->picture;
    //         if (file_exists($filepath)) {
    //             unlink($filepath);
    //         }
    //         $return = User::where("id", $id)->delete();
    //         return $this->response('User account is deleted Successfully', null, true);
    //     }
    //     try {
    //         $record = $model->whereIn('id', $request->ids);

    //         if ($record) {
    //             return $this->response('Select record successfully deleted.', null, true);
    //         } else {
    //             return $this->response('Select record cannot delete.', null, false);
    //         }
    //     } catch (\Throwable $th) {
    //         throw $th;
    //     }
    // }

    public function search(User $model, Request $request, $key)
    {
        $model = $this->FilterCompanyList($model, $request);

        $fields = [
            'name',
            'role' => ['name'],
        ];

        $model = $this->process_search($model, $key, $fields);

        $model->whereHas("role", function ($q) {
            return $q->where('name', '!=', "company");
        });

        $model->with("role");

        return $model->paginate($request->per_page);
    }

    public function createUserData(Request $request)
    {
        $validationErrors = $this->validateRequest($request);
        if ($validationErrors) return $validationErrors;



        $data = $request->except(['editId', 'confirm_password', 'password', 'attachment', "user_id"]);
        if ($request->hasFile('attachment')) {
            $data['picture'] = $this->uploadFile($request->file('attachment'));
        }

        $isExist = User::where('email', $request->email)->first();
        if ($request->filled('editId')) {


            if ($isExist && $isExist->id !=  $request->editId) {
                return ["status" => false, "errors" => ["email" => ["User Email already exists111111"]]];
            }
            $this->updateUser($request, $data);
            return $this->response('User account details are updated', null, true);
        } else {
            if ($isExist) return ["status" => false, "errors" => ["email" => ["User Email already exists22222"]]];
            $record = $this->createUser($request, $data);
            return $this->response('User account is created.', $record, true);
        }
    }

    // Modular Validation
    private function validateRequest($request)
    {
        $rules = [
            'company_id' => 'required|integer',
            'branch_id' => 'nullable',
            'editId' => 'nullable',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'contact_number' => 'required',
            'password' => $request->editId ? 'nullable' : 'required',
            'confirm_password' => $request->editId ? 'nullable' : 'required',
        ];

        $request->validate($rules);

        if ($request->password && $request->password !== $request->confirm_password) {
            return ["status" => false, "errors" => ["confirm_password" => ["Password and Confirm Password do not match"]]];
        }
        return null;
    }

    // File Upload Helper
    private function uploadFile($file)
    {
        $fileName = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('/users'), $fileName);
        return $fileName;
    }

    // Update User Helper
    private function updateUser($request, $data)
    {
        $userData = [
            "user_type" => "company",
            "role_id" => $request->role_id,
            'name' => "{$request->first_name} {$request->last_name}",
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email_id,
            'company_id' => $request->company_id,
            'web_login_access' => $request->web_login_access ?? 1,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        User::where("id", $request->editId)->update(array_merge($userData, $data));
    }

    // Create User Helper
    private function createUser($request, $data)
    {

        return User::create([
            "user_type" => "company",
            "role_id" => $request->role_id,
            'name' => "{$request->first_name} {$request->last_name}",
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => $request->company_id,
            'web_login_access' => 1,
        ] + $data);
    }
}
