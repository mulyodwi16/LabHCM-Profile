<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    public function index()
    {
        return view('admin.projects.index', [
            'projects' => Project::with('user', 'images')->latest()->paginate(20),
        ]);
    }

    public function create() { return view('admin.projects.form', ['project' => new Project()]); }

    public function store(Request $r)
    {
        $this->abortIfPostTruncated($r);

        $files = $this->collectImageUploads($r);
        $data = $this->validated($r, requireImages: true);
        unset($data['images']);

        if ($files === []) {
            throw ValidationException::withMessages([
                'images' => 'Minimal satu gambar (JPG/PNG) wajib diunggah.',
            ]);
        }

        $project = $r->user()->projects()->create($data);
        $this->persistImages($files, $project);

        return redirect()->route('admin.projects.index')->with('status', 'Project created.');
    }

    public function edit(Project $project) { return view('admin.projects.form', compact('project')); }

    public function update(Request $r, Project $project)
    {
        $this->abortIfPostTruncated($r);

        $files = $this->collectImageUploads($r);
        $data = $this->validated($r, requireImages: false);
        unset($data['images']);

        $project->update($data);

        if ($files !== []) {
            $this->persistImages($files, $project);
        }

        return redirect()->route('admin.projects.index')->with('status', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        foreach ($project->images as $img) Storage::disk('public')->delete($img->path);
        $project->delete();
        return back()->with('status', 'Project deleted.');
    }

    private function validated(Request $r, bool $requireImages): array
    {
        $r->merge([
            'youtube_url' => $r->input('youtube_url') ?: null,
            'github_url'  => $r->input('github_url') ?: null,
        ]);

        $imageRules = $requireImages
            ? ['required', 'array', 'min:1']
            : ['nullable', 'array'];

        $data = $r->validate([
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'github_url'  => ['nullable', 'url', 'max:255'],
            'images'      => $imageRules,
            'images.*'    => ['image', 'mimes:jpeg,jpg,png', 'max:8192'],
        ], [
            'images.required' => 'Minimal satu gambar (JPG/PNG) wajib diunggah.',
            'images.min'      => 'Minimal satu gambar (JPG/PNG) wajib diunggah.',
            'images.*.image'  => 'Setiap file gambar harus berformat JPG atau PNG.',
            'images.*.mimes'  => 'Format gambar tidak didukung. Gunakan JPG atau PNG.',
            'images.*.max'    => 'Ukuran gambar maksimal 8 MB per file.',
        ]);

        $data['published'] = $r->boolean('published');

        return $data;
    }

    /** @return list<UploadedFile> */
    private function collectImageUploads(Request $r): array
    {
        $raw = $r->file('images');
        if ($raw === null) {
            return [];
        }

        $files = array_values(array_filter(
            Arr::wrap($raw),
            fn ($file) => $file instanceof UploadedFile
        ));

        $errors = [];
        $valid = [];

        foreach ($files as $i => $file) {
            if ($file->isValid()) {
                $valid[] = $file;
                continue;
            }

            $errors["images.{$i}"] = match ($file->getError()) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran gambar melebihi batas (maks. 8 MB per file).',
                UPLOAD_ERR_PARTIAL => 'Upload gambar terputus. Coba lagi.',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang terunggah.',
                default => 'Upload gambar gagal. Pastikan format JPG/PNG.',
            };
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $valid;
    }

    /** @param list<UploadedFile> $files */
    private function persistImages(array $files, Project $project): void
    {
        $nextOrder = (int) $project->images()->max('sort_order') + 1;

        foreach ($files as $i => $file) {
            $path = $file->store('projects', 'public');

            if (!$path) {
                throw ValidationException::withMessages([
                    'images' => 'Gagal menyimpan gambar ke storage. Coba lagi atau hubungi admin.',
                ]);
            }

            $project->images()->create([
                'path'       => $path,
                'sort_order' => $nextOrder + $i,
            ]);
        }
    }

    private function abortIfPostTruncated(Request $r): void
    {
        $length = (int) $r->server('CONTENT_LENGTH', 0);
        if ($length > 0 && !$r->filled('title') && !$r->filled('description')) {
            throw ValidationException::withMessages([
                'images' => 'Upload gagal: ukuran file melebihi batas server (maks. 20 MB per file).',
            ]);
        }
    }
}
