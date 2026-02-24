<?php
// app/Http/Controllers/Admin/ProgramController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program\Program;
use App\Models\Program\ProgramMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ProgramController extends Controller
{
    public function index()
    {
        $list = Program::orderBy('id', 'desc')->with(['images', 'video'])->get();
        return view('admin.program.index', compact('list'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'nullable|exists:programs,id',
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'cover_image' => 'nullable|image|max:5120',
        ]);

        $data = $request->only(['title', 'excerpt', 'content', 'status', 'published_at']);

        // slug
        if ($request->filled('id')) {
            $program = Program::findOrFail($request->id);
        } else {
            $program = new Program();
            $program->created_by = auth()->id();
        }

        $program->title = $data['title'];
        $program->excerpt = $data['excerpt'] ?? null;
        $program->content = $data['content'] ?? null;
        $program->status = $data['status'];
        $program->published_at = $data['published_at'] ?? null;

        // slug auto (update kalau title berubah dan slug kosong / atau tetap)
        if (!$program->slug) {
            $program->slug = $this->uniqueSlug($program->title, $program->id ?? null);
        }

        // cover upload
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $imageName = time() . '_' . $file->getClientOriginalName();

            $file->storeAs('public/programs/covers', $imageName);

            $compressed = Image::make($file);
            $compressed->resize(1600, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $compressed->save(storage_path('app/public/programs/covers/' . $imageName));

            $program->cover_image = '/storage/programs/covers/' . $imageName;
        }

        $program->save();

        return redirect()->back()->with('success', 'Program saved');
    }

    public function edit(Request $request)
    {
        $request->validate(['id' => 'required|exists:programs,id']);
        $program = Program::with(['images', 'video'])->findOrFail($request->id);

        return response()->json([
            'id' => $program->id,
            'title' => $program->title,
            'slug' => $program->slug,
            'excerpt' => $program->excerpt,
            'content' => $program->content,
            'status' => $program->status,
            'published_at' => optional($program->published_at)->format('Y-m-d\TH:i'),
            'cover_image' => $program->cover_image,
            'images' => $program->images->map(fn($m) => [
                'id' => $m->id,
                'file_path' => $m->file_path,
                'sort' => $m->sort,
                'caption' => $m->caption
            ])->values(),
            'video' => $program->video ? [
                'id' => $program->video->id,
                'video_url' => $program->video->video_url,
                'file_path' => $program->video->file_path
            ] : null,
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:programs,id']);
        Program::where('id', $request->id)->delete();

        return response()->json(['success' => true, 'id' => $request->id]);
    }

    // ===== MEDIA: upload gallery images =====
    public function uploadImages(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'images' => 'required',
            'images.*' => 'image|max:5120',
        ]);

        $programId = $request->program_id;
        $lastSort = ProgramMedia::where('program_id', $programId)->where('type', 'image')->max('sort');
        $nextSort = $lastSort ? $lastSort + 1 : 1;

        foreach ($request->file('images') as $image) {
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/programs/gallery', $imageName);

            $compressed = Image::make($image);
            $compressed->resize(1600, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $compressed->save(storage_path('app/public/programs/gallery/' . $imageName));

            ProgramMedia::create([
                'program_id' => $programId,
                'type' => 'image',
                'file_path' => '/storage/programs/gallery/' . $imageName,
                'sort' => $nextSort++,
            ]);
        }

        return redirect()->back()->with('success', 'Images uploaded');
    }

    public function updateImageSort(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:program_media,id',
            'sort' => 'required|integer|min:1',
        ]);

        ProgramMedia::where('id', $request->id)->update(['sort' => $request->sort]);

        return response()->json(['success' => true]);
    }

    public function deleteMedia(Request $request)
    {
        $request->validate(['id' => 'required|exists:program_media,id']);
        ProgramMedia::where('id', $request->id)->delete();

        return response()->json(['success' => true, 'id' => $request->id]);
    }

    // ===== VIDEO: set video url (only 1) =====
    public function upsertVideo(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'video_url' => 'nullable|string|max:255',
        ]);

        $programId = $request->program_id;

        // hapus jika kosong
        if (!$request->filled('video_url')) {
            ProgramMedia::where('program_id', $programId)->where('type', 'video')->delete();
            return redirect()->back()->with('success', 'Video removed');
        }

        ProgramMedia::updateOrCreate(
            ['program_id' => $programId, 'type' => 'video'],
            ['video_url' => $request->video_url, 'sort' => 1]
        );

        return redirect()->back()->with('success', 'Video saved');
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') $base = 'program';

        $slug = $base;
        $i = 1;

        while (Program::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
