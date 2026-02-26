<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Program\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        // List untuk page Our Program (bagian atas + pagination)
        $search = $request->input('search');
        $perPage = (int) ($request->input('per_page', 8));
        $perPage = $perPage > 50 ? 50 : $perPage;

        $q = Program::query()
            ->where('status', 'published')
            ->orderByRaw('COALESCE(published_at, created_at) DESC');

        if ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $data = $q->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $data
        ]);
    }

    public function detail(Request $request, $slug)
    {
        $program = Program::with(['images', 'video'])
            ->where('status', 'published')
            ->where('slug', $slug)
            ->first();

        if (!$program) {
            return response()->json([
                'status' => false,
                'message' => 'Program not found',
                'data' => null
            ], 404);
        }

        Program::where('id', $program->id)->increment('views_count');

        // related/latest (simple)
        $latest = Program::where('status', 'published')
            ->where('id', '!=', $program->id)
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->limit(3)
            ->get(['id', 'title', 'slug', 'excerpt', 'cover_image', 'published_at']);

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => [
                'detail' => [
                    'id' => $program->id,
                    'title' => $program->title,
                    'slug' => $program->slug,
                    'excerpt' => $program->excerpt,
                    'content' => $program->content,
                    'cover_image' => $program->cover_image,
                    'published_at' => optional($program->published_at)->toDateTimeString(),
                    'views_count' => $program->views_count + 1,
                    'gallery' => $program->images->map(fn($m) => [
                        'id' => $m->id,
                        'image' => $m->file_path,
                        'caption' => $m->caption,
                        'sort' => $m->sort,
                    ])->values(),
                    'video' => $program->video ? [
                        'video_url' => $program->video->video_url,
                        'file_path' => $program->video->file_path,
                    ] : null,
                ],
                'latest' => $latest,
            ],
        ]);
    }

    public function latest()
    {
        $latest = Program::where('status', 'published')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->limit(3)
            ->get(['id', 'title', 'slug', 'excerpt', 'cover_image', 'published_at']);

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $latest
        ]);
    }

    public function more(Request $request)
    {
        $limit = (int) $request->input('limit', 6);
        $lastId = $request->input('last_id');

        $limit = max(1, min($limit, 12)); // max 12 biar ga dump cepat

        $query = Program::where('status', 'published')
            ->orderByRaw('COALESCE(published_at, created_at) DESC');

        // Cursor logic
        if ($lastId) {
            $query->where('id', '<', $lastId);
        }

        $data = $query
            ->limit($limit + 1) // ambil 1 extra untuk cek has_more
            ->get(['id', 'title', 'slug', 'excerpt', 'cover_image', 'published_at']);

        $hasMore = $data->count() > $limit;

        if ($hasMore) {
            $data = $data->slice(0, $limit);
        }

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $data
        ]);
    }
}
