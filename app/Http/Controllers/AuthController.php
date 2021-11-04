<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use DB;
use Mail;
use File;
class AuthController extends Controller
{

    protected $status = 200;
    protected $statusCode = false;
    public function __construct($status = null, $statusCode = null)
    {
        $this->statusCode = $statusCode;
        $this->status = $status;
    }
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
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users,email',
            'username' => 'required',
            'password' => 'required',
            'confirm_password'=> 'same:password'
        ],
        [
            'email.required' => 'The email field is required',
            'email.unique' => 'The email is already exist',
            'username.required' => 'The username field is required',
            'password.required' => 'The password field is required',
            'confirm_password.required'=> 'The confirm password field is required',
            'confirm_password.same'=> 'The password did not match'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->messages()
            ]);
        }

        DB::beginTransaction();
        try{
            $verificationPin = random_int(100000, 999999);
            $input = $request->all();
            $this->statusCode = true;
            $result = User::create([
                'name' => $request->input('username'),
                'user_name' => $request->input('username'),
                'user_role' => $request->input('user_role'), // based on registration process admin/user
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'verification_pin' => $verificationPin
            ]);
            if($result) {
                $data = array(
                    'name' => $request->input('username'),
                    'verificationCode' => $verificationPin,
                );
                $email = $request->input('email');
                Mail::send('email', $data, function ($message) use ($email) {
                    $message->from(env('MAIL_FROM_ADDRESS'), 'Laravel Task');
                    $message->to($email)->subject('Verification code for Laravel Task');
                });
            }
            DB::commit();
            return response()->json([
                'message' => "Registration successfully done..!",
                'status' => $this->statusCode
            ]);
        } catch(Exception $e)
        {
            DB::rollback();
            return back(response()->json([
                'msg' =>'Something went wrong please try after sometime',
                'status' => $this->statusCode
            ]));
        }
    }

    /**
     * Verification Pin
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyPin(Request $request)
    {
        $message = "";
        $this->statusCode = 200;
        $validator = Validator::make($request->all(), [
            'verification_pin' => 'required',
        ],
        [
            'verification_pin.required' => 'The Pin field is required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->messages()
            ]);
        }

        try{
            $checkUser = User::where(['email' => $request->input('email'), 'verification_pin' => $request->input('verification_pin')])->first();
            if($checkUser) {
                if(!$checkUser->registered_at) {
                    $checkUser->update([
                        'registered_at' => now()
                    ]);
                    $this->status = true;
                    $message = "Verification successfully done..!";
                } else {
                    $this->status = true;
                    $message = "Pin already verified..!";
                }
            } else {
                $this->statusCode = 400;
                $this->status = false;
                $message = "Incorrect verification pin please check in email again..!";
            }
            return response()->json([
                'message' => $message,
                'status' => $this->status,
                'code' => $this->statusCode
            ]);
        } catch(Exception $e)
        {
            return back(response()->json([
                'message' =>'Something went wrong please try after sometime',
                'status' => $this->status
            ]));
        }
    }

    /**
     * Check user for logged in
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $message = "";
        $this->status = false;
        $this->statusCode = 500;
        $dataReturn = array();
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ],
        [
            'username.required' => 'The username field is required',
            'password.required' => 'The password field is required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->messages()
            ]);
        }
        try {
            $checkUser = User::where('user_name',$request->input('username'))->orWhere('email',$request->input('username'))->first(['name','email','avatar']);
            if ($checkUser) {
                if (Auth::attempt(['email' => $request->input('username'), 'password' => $request->input('password')], true)) {
                    $dataReturn =[
                        'token' => Auth::user()->createToken(env('PERSONAL_ACCESS_CLIENT'))->plainTextToken,
                        'user' => $checkUser
                    ];
                    $this->status = true;
                    $this->statusCode = 200;
                    $message = "Logged in successfully..!";
                } else {
                    $this->status = false;
                    $this->statusCode = 500;
                    $message = "Unauthorized..!";
                }
            } else {
                $this->status = false;
                $this->statusCode = 500;
                $message = "User does not exist..!";
            }
            return response()->json([
                'status' => $this->status,
                'code' => $this->statusCode,
                'message' => $message,
                'data' => $dataReturn
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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

    /**
     * Send Invite to new Users
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function invite(Request $request)
    {
        try{
            $checkUser = User::where('email',$request->email)->first();
            if(!$checkUser) {
                Mail::send('invite_user', [], function ($message) use ($request) {
                    $message->from(env('MAIL_FROM_ADDRESS'), 'Invitation From Bhavesh');
                    $message->to($request->email)->subject('Bhavesh Keshrani invited you to join team');
                });
                $message = 'Invitation has been successfully sent..!!';
                $this->status = true;
                $this->statusCode = 200;
            } else {
                $message = 'User with same email is already a member of team..!!';
                $this->status = false;
                $this->statusCode = 400;
            }
            return response()->json([
                'message' => $message,
                'status' => true,
                'code' => $this->statusCode
            ]);
        } catch(Exception $e)
        {
            return back(response()->json([
                'message' =>'Something went wrong please try after sometime',
                'status' => $this->status
            ]));
        }
    }
    public function updateUserProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'avatar' => "dimensions:max_width=256,max_height=256",
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->messages()
                ]);
            }
            $userLoggedId = Auth::user();
            if (!File::isDirectory('avatar/'.$userLoggedId->id)){
                File::makeDirectory('avatar/'.$userLoggedId->id, 0775, true, true);
            }
            $fileNameToStore = 'avatar_'.time().'.'.$request->avatar->extension();
            $request->avatar->move(public_path('avatar/'.$userLoggedId->id), $fileNameToStore);
            $dataUpdated = $userLoggedId->update([
                'name' => $request->name,
                'avatar' => $request->getSchemeAndHttpHost().'/avatar/'.$userLoggedId->id.'/'.$fileNameToStore
            ]);
            if($dataUpdated) {
                $message = 'Profile has been successfully updated..!';
                $this->status = true;
                $this->statusCode = 200;
            } else {
                $message = 'Please check the data again..!';
                $this->status = false;
                $this->statusCode = 400;
            }
            return response()->json([
                'message' => $message,
                'status' => $this->status,
                'code' => $this->statusCode
            ]);
        }
        catch(Exception $e)
        {
            return back(response()->json([
                'message' =>'Something went wrong please try after sometime',
                'status' => $this->status
            ]));
        }
    }
}
