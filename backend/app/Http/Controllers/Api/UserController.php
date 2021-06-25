<?php

namespace App\Http\Controllers\Api;
use App\Enums\UserRole;
use App\Enums\ApiErrorCodeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ModelCollection;
use App\Http\Resources\UserResource;

use App\Models\User;
use App\Models\UserInformation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
{
    //

    public function index(Request $request)
    {
        $users = User::query();
        $per_page = $request->per_page ?? 10;
        if ($request->has("is_customer")) {
            $users->where("is_customer", $request->is_customer);
        }
        $users = $users->paginate($per_page);
        return \ApiService::success(new ModelCollection($users));
    }

    public function show($id)
    {
        $user = User::find($id);
        if ($user === null) {
            return \ApiService::fail(ApiErrorCodeEnum::USER_NOT_EXIST, "User không tồn tại");
        }
        return \ApiService::success(new UserResource($user));
    }

    public function store(Request $request)
    {
        $rules = [
            "username" => "required|unique:users|min:4|max:100",
            "email" => "required|email",
            "password" => "required",
            "is_customer" => "required",
            "fullname" => "required", 
            "phone" => "required"
        ];
        
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return \ApiService::fail(ApiErrorCodeEnum::USER_FAIL_VALIDATION, $validator->errors()->toArray());
        }



        $user = new User();
        $user->fill($request->all());
        $user->password = \Hash::make($request->password);
        $user->save();
        
     
        
        if ($user->id === null) {
            return \ApiService::fail(ApiErrorCodeEnum::USER_ADD_FAIL);
        }
        $user_information = new UserInformation(); 
        $user_information->user_id = $user->id; 
        $user_information->fullname = $request->fullname;
        $user_information->phone = $request->phone;
        $user_information->save();

        if($user_information->wasRecentlCreated === false){
            return \ApiService::fail(ApiErrorCodeEnum::USER_ADD_FAIL);
        }
        return \ApiService::success(new UserResource($user));
    }

    public function update(Request $request, $id)
    {
        $rules = [
        ];
        
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return \ApiService::fail(ApiErrorCodeEnum::USER_FAIL_VALIDATION, $validator->errors()->toArray());
        }
        
        $user = User::find($id);
        
        if ($user === null) {
            return \ApiService::fail(ApiErrorCodeEnum::USER_NOT_EXIST);
        }

        $current_user = $request->user();
        // if(!($current_user->role === UserRole::ADMIN || $current_user->role <= $user->role && $current_user->tail_id  ===  $user->tail_id)) {
        //     return \ApiService::fail(ApiErrorCodeEnum::PERMISSION_DENIED);
        // }
        try {
            
            $user->fill($request->all());
            $result = $user->save();
            if ($result) {
                return \ApiService::success($user);
            }
            return \ApiService::fail(ApiErrorCodeEnum::USER_UPDATE_FAIL);
        } catch (\Exception $ex) {
            return \ApiService::fail(ApiErrorCodeEnum::USER_UPDATE_FAIL);
        }
    }

    public function delete(Request $request, $id)
    {
        $user = User::find($id);
        if ($user === null) {
            return \ApiService::fail(ApiErrorCodeEnum::USER_NOT_EXIST);
        }
        $user->delete();
        if($user->trashed()){
            return \ApiService::success("deleted");
        }
        return \ApiService::fail(ApiErrorCodeEnum::USER_DELETE_FAIL);
    }

    // Global region


    // Tạo mới tài khoản customer
    public function createCustomerUser(Request $request)
    {
        $request->request->add(
            [
                "is_customer" => true, 
                "role" => UserRole::CUSTOMER
            ]
        );   
        return $this->store($request);
        
        //return \ApiService::success($req_user);
    }


    // End Glbal region
    

    // Customer region

    // customer thay đổi thông tin tài khoản

    public function updateCustomerInfo(Request $request){
        $current_user = $request->user();
        //Userinformation 
        $user_information = UserInformation::find($current_user->id);
        $user_information->fill($request->all());
        $user_information->save();
        return \ApiService::success(new UserResource(User::find($current_user->id))); 
    }
    public function getCurrentCustomerInfo(Request $request){
        $current_user = $request->user();
        return \ApiService::success(new UserResource(User::find($current_user->id)));
    }
    public function changePassword(Request $request)
    {
        $rules = [
            "old_password" => "required",
            "password" => "required",
            "repassword"=> "required",
        ];
        
        $validator = \Validator::make($request->all(), $rules);
        if($validator->fails()){
            return \ApiService::fail(ApiErrorCodeEnum::USER_FAIL_VALIDATION, $validator->errors()->toArray());
        }

        
        if($request->password != $request->repassword){
            return \ApiService::fail(ApiErrorCodeEnum::USER_INCORRECT_REPASSWORD);
        }
        
        $current_user = $request->user();
        $user = User::find($current_user->id);
        if(\Hash::check($request->old_password, $user->password) !== true){
            return \ApiService::fail(ApiErrorCodeEnum::USER_OLD_PASSWORD_INCORRECT);
        }
        
        $user->password =  \Hash::make($request->password);
        $user->save();
        return \ApiService::success("success");
    }
    // End Customer region



    // Admin region
    public function createSubAdmin(Request $request)
    {
        $req_user = $request->user();
        $request->request->add(
            [
                "tail_id" => $req_user->tail_id,
                "is_customer" => false, 
                "role" => UserRole::SUB_ADMIN
            ]
        );
        if($req_user->role !== UserRole::ADMIN){
            return \ApiService::fail(ApiErrorCodeEnum::PERMISSION_DENIED);
        }
        return $this->store($request);
    }
    // End Admin

    // SubAdmin region

    public function createSaleStaff(Request $request)
    {
        $req_user = $request->user();
        $request->request->add(
            [
                "tail_id" => $req_user->tail_id,
                "is_customer" => false, 
                "role" => UserRole::SALE_STAFF
            ]
        );
        if($req_user->role !== UserRole::SUB_ADMIN){
            return \ApiService::fail(ApiErrorCodeEnum::PERMISSION_DENIED);
        }
        return $this->store($request);
    }
    public function createServiceStaff(Request $request)
    {
        $req_user = $request->user();
        $request->request->add(
            [
                "tail_id" => $req_user->tail_id,
                "is_customer" => false, 
                "role" => UserRole::SERVICE_STAFF
            ]
        );
        if($req_user->role !== UserRole::SUB_ADMIN){
            return \ApiService::fail(ApiErrorCodeEnum::PERMISSION_DENIED);
        }
        return $this->store($request);
    }


    public function changeStaffRole(Request $request, $user_id)
    {
        $req_user = $request->user();
        if($req_user->role !== UserRole::SUB_ADMIN){
            return \ApiService::fail(ApiErrorCodeEnum::PERMISSION_DENIED);
        }
        
        return $this->update($request);
    }

    // End SubAdmin region

}