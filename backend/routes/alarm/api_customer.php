<?php

use App\Http\Controllers\AssignedDepartmentEmployeeController;
use App\Http\Controllers\Customers\CustomersController;
use App\Models\Customers\Customers;
use Illuminate\Support\Facades\Route;



Route::get("get_customer_maintenanace_info", [CustomersController::class, "GetCustomerMaintenanceInfo"]);
Route::get("get_customer_tickets_statistics", [CustomersController::class, "GetCustomerTicketsStatistics"]);
