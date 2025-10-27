<?php

namespace App\implementation\repositories;

use App\Enums\ApiResponse;
use App\Interfaces\repositories\iuserInterface;
use App\Models\User;
use App\Notifications\TemporaryPasswordNotification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class _userRepository implements iuserInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;
    protected $role;
    public function __construct(User $model, Role $role)
    {
        $this->model = $model;
        $this->role = $role;
    }
    public function getusers($search = null)
    {
        try {
            if ($search) {
                return $this->model::where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->paginate(10);
            }
            return $this->model::paginate(10);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function getall(){
        return $this->model::all();
    }
    public function getuser($id)
    {
        try {
            $user = $this->model::find($id);
            return $user ? $user : ApiResponse::NOT_FOUND;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getuserbyemail($email)
    {
        return $this->model::where('email', $email)->first();
    }
    public function createuser(array $user, array $roles)
    {
        try {
            $exists = $this->model::where('email', $user['email'])->exists();
            if ($exists) {
                return ["status" => ApiResponse::ALREADY_EXISTS->value, "message" => "User already exists."];
            }

            // Generate temporary password and verification token
            $temporaryPassword = "Password12345";
            $user["id"]=Str::uuid();

            // Add verification token and password to user data
            $user['password'] = $temporaryPassword;
            $user['status'] = $user['status'] ?? 'active';



            // Create the user
            $newUser = $this->model::create($user);
            $newUser->syncRoles($roles);
            // $newUser->roles()->sync($roles);
            $accounttpes =  $this->role->whereIn('id', $roles)->get()->pluck('accounttype_id')->unique()->toArray();
           
            $newUser->accounttypes()->sync($accounttpes);

            // Send notifications
            $newUser->notify(new TemporaryPasswordNotification($temporaryPassword));

            return ["status" => "success", "message" => "User created successfully."];
        } catch (\Exception $e) {
          
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function updateuser($id, array $data, array $roles)
    {
        try {
            $user = $this->model::where('id', $id)->first();
            if (!$user) {
                return ApiResponse::NOT_FOUND;
            }


            // Check if email is being changed and if it already exists
            if (isset($user['email']) && $user['email'] !== $user->email) {
                $exists = $this->model::where('email', $user['email'])
                    ->where('id', '!=', $id)
                    ->exists();
                if ($exists) {
                    return ["status" => "error", "message" => "User already exists."];
                }
            }

            $user->update($data);
            $user->syncRoles($roles);
            $accounttpes =  $this->role->whereIn('id', $roles)->get()->pluck('accounttype_id')->unique()->toArray();
            $user->accounttypes()->sync($accounttpes);
            return ["status" => "success", "message" => "User updated successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function deleteuser($id)
    {
        try {
            $user = $this->model::where('id', $id)->first();
            if (!$user) {
                return ["status" => "error", "message" => "User not found"];
            }
            $user->delete();
            return ["status" => "success", "message" => "User deleted successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function assignroles($id, array $roles)
    {
        try {
            $user = $this->model::where('id', $id)->first();
            if (!$user) {
                return ["status" => "error", "message" => "User not found"];
            }
            $user->assignRoles($roles);
            return ["status" => "success", "message" => "Roles assigned successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function removeroles($id, array $roles)
    {
        try {
            $user = $this->model::where('id', $id)->first();
            if (!$user) {
                return ["status" => "error", "message" => "User not found"];
            }
            $user->syncRoles($roles);
            return ["status" => "success", "message" => "Roles removed successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function assignaccounttype($id, array $accounttype)
    {
        try {
            $user = $this->model::find($id);
            if (!$user) {
                return ["status" => "error", "message" => "User not found"];
            }
            $user->accounttypes()->sync($accounttype, false);
            return ["status" => "success", "message" => "Account type assigned successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function removeaccounttype($id, array $accounttype)
    {
        try {
            $user = $this->model::find($id);
            if (!$user) {
                return ["status" => "error", "message" => "User not found"];
            }
            $user->accounttypes()->detach();
            $user->accounttypes()->sync($accounttype);
            return ["status" => "success", "message" => "Account type removed successfully."];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function resetpassword($id)
    {
        try {
            $user = $this->model::find($id);
            if (!$user) {
                return ["status" => "error", "message" => "User not found"];
            }
            // Generate temporary password
            $temporaryPassword = Str::random(12);
            $user->password = $temporaryPassword;
            $user->save();

            // Send notification with temporary password
            $user->notify(new TemporaryPasswordNotification($temporaryPassword));

            return ["status" => "success", "message" => "Password reset successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    public function updatepassword($id, array $user)
    {
        try {
            $user = $this->model::find($id);
            if (!$user) {
                return ["status" => "error", "message" => "User not found"];
            }
            $user->password = $user['password'];
            $user->save();
            return ["status" => "success", "message" => "Password updated successfully."];
        } catch (\Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
}
