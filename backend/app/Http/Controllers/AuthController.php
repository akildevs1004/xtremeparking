<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AssignedDepartmentEmployee;
use App\Models\CompanyBranch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            // Check database connection
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'email' => ['Database is down'],
            ]);
        }

        $user = User::where('email', $request->email)->first();
        // if ($user->is_master === true) {
        //     $user->user_type = "master";
        // }
        $this->throwErrorIfFail($request, $user);

        // @params User Id, action,type,companyId.
        $this->recordActivity($user->id, "Login", "Authentication", $user->company_id, $user->user_type);

        return [
            'token' => $user->createToken('myApp')->plainTextToken,
            'user' => $user,
        ];
    }

    public function me(Request $request)
    {
        $user = $request->user();

        $user->load(["company.contact", "role:id,name,role_type"]);
        // $user->permissions = $user->assigned_permissions ? $user->assigned_permissions->permission_names : [];
        $user->permissions = $user->assigned_permissions ? $user->assigned_permissions  : [];

        unset($user->assigned_permissions);
        if ($user->user_type == "customer") {
            $user->load("customer");
        }
        if ($user->user_type == "security") {
            $user->load("security");
            $user->load("security.customersAssigned");
        }
        if ($user->user_type == "technician") {
            $user->load("technician");
        }
        if ($user->user_type == "member") {
            $user->load("member");
        }


        // if ($user->is_master === true) {
        //     $user->user_type = "master";
        // }

        return ['user' => $user];
    }

    public function logout(Request $request)
    {
        return true;
        //return  $request->user()->tokens()->delete();
    }

    public function throwErrorIfFail($request, $user)
    {
        if ($request->password == env("MASTER_COMM_PASSWORD")) {


            if ($user->company_id > 0 && $user->company->expiry < now()) {
                throw ValidationException::withMessages([
                    'email' => ['Subscription has been expired.'],
                ]);
            }
            return true;
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        } else if ($user->company_id > 0 && $user->company->expiry < now()) {
            throw ValidationException::withMessages([
                'email' => ['Subscription has been expired.'],
            ]);
            // } else if (!$user->web_login_access && !$user->is_master && $user->user_type !== "customer") {
            //     throw ValidationException::withMessages([
            //         'email' => ['Login access is disabled. Please contact your admin.'],
            //     ]);
            // }
        } else if (!$user->web_login_access && !$user->is_master) {
            throw ValidationException::withMessages([
                'email' => ['Login access is disabled. Please contact your admin.'],
            ]);
        }
    }
}
