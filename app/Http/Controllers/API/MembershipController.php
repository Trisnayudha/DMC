<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use App\Models\Users\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MembershipController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * POST /api/check-membership
     * body: { "identifier": "email@domain.com" } atau { "identifier": "+62812xxxx" } atau { "identifier": "0812xxxx" }
     */
    public function check(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'identifier' => ['required', 'string', 'max:255'],
            ],
            [
                'identifier.required' => 'Email / Phone wajib diisi',
            ]
        );

        if ($validate->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => 'Invalid data',
                'payload' => [
                    'identifier' => $validate->errors()->first('identifier'),
                ],
            ]);
        }

        $identifier = trim((string) $request->identifier);

        // detect email
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        $user = null;
        $profile = null;

        if ($isEmail) {
            // cari user by email
            $user = User::where('email', $identifier)->first();

            if ($user) {
                // cari profile by users_id (kalau ada)
                $profile = ProfileModel::where('users_id', $user->id)->first();
            }
        } else {
            // treat as phone
            $rawPhone = $identifier;

            // normalisasi sederhana
            $phoneNoSpace = preg_replace('/\s+/', '', $rawPhone);
            $digitsOnly   = preg_replace('/\D+/', '', $rawPhone); // 0812xxx, 62812xxx

            // cari profile berdasarkan fullphone / phone (coba beberapa variasi)
            $profile = ProfileModel::where(function ($q) use ($rawPhone, $phoneNoSpace, $digitsOnly) {
                $q->where('fullphone', $rawPhone)
                    ->orWhere('fullphone', $phoneNoSpace)
                    ->orWhere('phone', $rawPhone)
                    ->orWhere('phone', $phoneNoSpace);

                // kalau data DB sering bentuk digits-only juga
                if (!empty($digitsOnly)) {
                    $q->orWhere('fullphone', $digitsOnly)
                        ->orWhere('phone', $digitsOnly);
                }
            })
                ->first();

            if ($profile) {
                $user = User::where('id', $profile->users_id)->first();
            }
        }

        if (!$user && !$profile) {
            return response()->json([
                'status'  => 404,
                'message' => 'Member not found',
                'payload' => [
                    'exists' => false,
                    'identifier' => $identifier,
                ],
            ]);
        }

        $roleName = 'guest';
        if ($user) {
            $role = $this->userService->checkrole($user->id);
            $roleName = $role[0]->name ?? 'guest';
        }

        // definisi "member" (silakan sesuaikan rule-nya)
        $isMember = false;
        if ($user) {
            $isMember = (
                ($user->verify_email === 'verified') ||
                ($user->verify_phone === 'verified') ||
                ($roleName !== 'guest')
            );
        }

        return response()->json([
            'status'  => 200,
            'message' => 'OK',
            'payload' => [
                'exists'       => true,
                'is_member'    => $isMember,
                'role'         => $roleName,

                'user' => $user ? [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'verify_email' => $user->verify_email,
                    'verify_phone' => $user->verify_phone,
                    'isStatus'     => $user->isStatus ?? null,
                ] : null,

                'profile' => $profile ? [
                    'users_id'   => $profile->users_id,
                    'fullphone'  => $profile->fullphone ?? null,
                    'phone'      => $profile->phone ?? null,
                    'job_title'  => $profile->job_title ?? null,
                    'company_id' => $profile->company_id ?? null,
                ] : null,
            ],
        ]);
    }
}
