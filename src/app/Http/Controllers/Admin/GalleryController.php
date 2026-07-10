<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        return view('admin.gallery.index', ['items' => GalleryItem::latest()->paginate(24)]);
    }

    public function create() { return view('admin.gallery.form', ['item' => new GalleryItem()]); }

    public function store(Request $r)
    {
        $data = $r->validate([
            'title'    => ['required', 'string', 'max:200'],
            'caption'  => ['nullable', 'string', 'max:1000'],
            'taken_at' => ['nullable', 'date'],
            'image'    => ['required', 'image', 'max:8192'],
        ]);
        $data['user_id']    = $r->user()->id;
        $data['image_path'] = $r->file('image')->store('gallery', 'public');
        GalleryItem::create($data);
        return redirect()->route('admin.gallery.index')->with('status', 'Gallery item added.');
    }

    public function edit(GalleryItem $galleryItem)
    {
        return view('admin.gallery.form', ['item' => $galleryItem]);
    }

    public function update(Request $r, GalleryItem $galleryItem)
    {
        $data = $r->validate([
            'title'    => ['required', 'string', 'max:200'],
            'caption'  => ['nullable', 'string', 'max:1000'],
            'taken_at' => ['nullable', 'date'],
            'image'    => ['nullable', 'image', 'max:8192'],
        ]);
        if ($r->hasFile('image')) {
            Storage::disk('public')->delete($galleryItem->image_path);
            $data['image_path'] = $r->file('image')->store('gallery', 'public');
        }
        $galleryItem->update($data);
        return redirect()->route('admin.gallery.index')->with('status', 'Gallery item updated.');
    }

    public function destroy(GalleryItem $galleryItem)
    {
        Storage::disk('public')->delete($galleryItem->image_path);
        $galleryItem->delete();
        return back()->with('status', 'Gallery item deleted.');
    }
}
