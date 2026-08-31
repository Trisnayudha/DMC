<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MemberSource;
use Illuminate\Http\Request;

class MemberSourceController extends Controller
{
    public function check(Request $request)
    {
        $category = $request->query('category');
        $code = $request->query('code');

        if (!$category || !$code) {
            return response()->json([
                'status'  => 400,
                'message' => 'Category and Code are required.',
                'payload' => null,
            ]);
        }

        $source = MemberSource::where('category', $category)
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$source) {
            return response()->json([
                'status'  => 404,
                'message' => 'Source pendaftaran tidak valid atau tidak aktif.',
                'payload' => null,
            ]);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Success',
            'payload' => [
                'name'      => $source->name,
                'form_type' => $source->form_type,
                'category'  => $source->category,
                'code'      => $source->code,
            ],
        ]);
    }
}
