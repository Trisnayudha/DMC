<?php

namespace App\Http\Controllers\API_WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 12);
        $page = (int) $request->input('page', 1);

        $perPage = $perPage > 50 ? 50 : $perPage;

        $query = DB::table('users')
            ->join('profiles', 'profiles.users_id', '=', 'users.id')
            ->leftJoin('company', 'company.id', '=', 'profiles.company_id')
            ->select(
                'users.id',
                'users.name',
                'profiles.image',
                'profiles.job_title'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('profiles.job_title', 'like', "%{$search}%")
                    ->orWhere('company.name', 'like', "%{$search}%");
            });
        }

        $data = $query
            ->orderBy('users.name', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ],
        ]);
    }
}
