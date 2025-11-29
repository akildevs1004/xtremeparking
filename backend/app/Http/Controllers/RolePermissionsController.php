<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RolePermissions;
use Illuminate\Http\Request;

class RolePermissionsController extends Controller
{
    public $menuItems = [
        ['label' => 'Intruder', 'name' => 'intruder'],
        ['label' => 'Dashboard', 'name' => 'dashboard'],
        ['label' => 'Map', 'name' => 'map'],
        ['label' => 'Alarms', 'name' => 'alarms'],
        ['label' => 'Customers', 'name' => 'customers'],
        ['label' => 'Devices', 'name' => 'devices'],
        ['label' => 'Reports', 'name' => 'reports'],
        ['label' => 'Tickets', 'name' => 'tickets'],
        ['label' => 'Settings', 'name' => 'settings'],
    ];
    public $subMenuItems = [
        [
            'topMenu' => 'intruder',
            'page_name' => 'intruder',
            'title' => 'Intruder Dashboard',
        ],
        [
            'topMenu' => 'dashboard',
            'page_name' => 'dashboard',
            'title' => 'Dashboard',
        ],
        [
            'topMenu' => 'map',
            'page_name' => 'map',
            'title' => 'MAP',
        ],
        [
            'topMenu' => 'alarms',
            'page_name' => 'alarms',
            'title' => 'Alarms',
        ],
        [
            'topMenu' => 'customers',
            'page_name' => 'customers',
            'title' => 'Customers',
        ],
        [
            'topMenu' => 'devices',
            'page_name' => 'devices',
            'title' => 'Devices',
        ],
        [
            'topMenu' => 'reports',
            'page_name' => 'armedreports',
            'title' => 'Armed Reports',
        ],
        [
            'topMenu' => 'reports',
            'page_name' => 'armedlogs',
            'title' => 'Armed Logs',
        ],
        [
            'topMenu' => 'tickets',
            'page_name' => 'tickets',
            'title' => 'Tickets',
        ],
        [
            'topMenu' => 'settings',
            'page_name' => 'company',
            'title' => 'Company',
        ],
        [
            'topMenu' => 'settings',
            'page_name' => 'serialnumbers',
            'title' => 'Serial Numbers',
        ],
        [
            'topMenu' => 'settings',
            'page_name' => 'operators',
            'title' => 'Operators',
        ],
        [
            'topMenu' => 'settings',
            'page_name' => 'technicians',
            'title' => 'Technicians',
        ],
        [
            'topMenu' => 'settings',
            'page_name' => 'weblogs',
            'title' => 'Weblogs',
        ],
        [
            'topMenu' => 'settings',
            'page_name' => 'users',
            'title' => 'Users',
        ],
        [
            'topMenu' => 'settings',
            'page_name' => 'roles',
            'title' => 'Roles',
        ],
    ];
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
     * @param  \App\Models\RolePermissions  $rolePermissions
     * @return \Illuminate\Http\Response
     */
    public function show(RolePermissions $rolePermissions)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RolePermissions  $rolePermissions
     * @return \Illuminate\Http\Response
     */
    public function edit(RolePermissions $rolePermissions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RolePermissions  $rolePermissions
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RolePermissions $rolePermissions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RolePermissions  $rolePermissions
     * @return \Illuminate\Http\Response
     */
    public function destroy(RolePermissions $rolePermissions)
    {
        //
    }

    public function roleUpdatePermissions(Request $request)
    {
        if ($request->role_id > 0 && $request->filled("permission_pages")) {
            RolePermissions::where('role_id', $request->role_id)->delete();


            $permissions = array_map(function ($page) use ($request) {
                return [
                    'role_id' => $request->role_id,
                    'page_name' => $page,
                ];
            }, $request->permission_pages);


            RolePermissions::insert($permissions);
        }

        return    $this->response('Role Permissions are successfully Updated', null, true);
    }
    public function createDefaultRoles(Request $request)
    {


        $companyId = $request->company_id;

        $this->createCompanyDefaultRoles($companyId);


        return $this->response("Roles created successfully", null, true);
    }
    public function createCompanyDefaultRoles($companyId)
    {


        $this->createRoleWithPermissions("Supervisor", $companyId, ["view", "create", "edit", "delete"]);

        $this->createRoleWithPermissions("Staff View", $companyId, ["view"]);

        $this->createRoleWithPermissions("Operator", $companyId, ["view", "edit"]);
    }
    public function createRoleWithPermissions($name, $companyId, $permissionsSet)
    {

        $role = Role::where("name", ($name))->where("company_id", $companyId);
        $id = 0;


        if ($role->count() == 1) {
            $id =  $role->first()->id;
        } else {

            $data = ["name" => $name, "company_id" => $companyId];
            $data["role_type"] = str_replace(' ', '_', strtolower($data["name"]));
            $data["description"] = implode(",", $permissionsSet);
            $record = Role::create($data);
            $id = $record->id;
        }
        $permissions = [];
        foreach ($this->subMenuItems as $menu) {
            foreach ($permissionsSet as $permission) {
                $permissions[] = ["role_id" => $id, "page_name" => $menu["page_name"] . "_$permission"];
            }
        }

        RolePermissions::insert($permissions);
    }
    public function getRolePermissions(Request $request)
    {

        return RolePermissions::where("role_id", $request->role_id)->pluck('page_name');
    }
    public function getPageRolesMenuList(Request $request)
    {



        return ["topmenu" => $this->menuItems, "submenu" =>  $this->subMenuItems];
    }
}
