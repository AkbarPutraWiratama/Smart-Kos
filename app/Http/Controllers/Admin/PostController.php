<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\PostTarget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Show the trash (soft-deleted announcements — admin sees all authors).
     */
    public function index(): View
    {
        $trashedPosts = Post::onlyTrashed()
            ->with(['author', 'targets.location', 'targets.room'])
            ->orderByDesc('deleted_at')
            ->paginate(20);

        return view('admin.posts.index', [
            'trashedPosts' => $trashedPosts,
        ]);
    }

    /**
     * Store a newly created announcement post by admin.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $post = Post::create([
            'author_id'    => Auth::id(),
            'title'        => $validated['title'],
            'content'      => $validated['content'],
            'image_path'   => null,
            'status'       => 'published',
            'published_at' => now(),
            'expires_at'   => $validated['expires_at'] ?? null,
        ]);

        // Handle multiple images (new multi-upload)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('posts', 'public');
                PostImage::create([
                    'post_id' => $post->id,
                    'path'    => $path,
                    'order'   => $index,
                ]);
            }
        }

        // Legacy single-image backward compat
        if ($request->hasFile('image') && ! $request->hasFile('images')) {
            $post->update(['image_path' => $request->file('image')->store('posts', 'public')]);
        }

        $targetType = $validated['target_type'];
        $locationId = in_array($targetType, ['location', 'room']) ? $validated['location_id'] : null;
        $roomId     = ($targetType === 'room') ? $validated['room_id'] : null;

        PostTarget::create([
            'post_id'     => $post->id,
            'target_type' => $targetType,
            'location_id' => $locationId,
            'room_id'     => $roomId,
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    /**
     * Update an existing announcement post. Admin can update any post.
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'content'     => ['required', 'string'],
            'target_type' => ['required', 'in:all,location,room'],
            'location_id' => ['nullable', 'required_if:target_type,location,room', 'exists:locations,id'],
            'room_id'     => ['nullable', 'required_if:target_type,room', 'exists:rooms,id'],
            'expires_at'  => ['nullable', 'date'],
            'images'      => ['nullable', 'array', 'max:10'],
            'images.*'    => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $post->update([
            'title'      => $validated['title'],
            'content'    => $validated['content'],
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        // Append new images if provided
        if ($request->hasFile('images')) {
            $existingCount = $post->images()->count();
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('posts', 'public');
                PostImage::create([
                    'post_id' => $post->id,
                    'path'    => $path,
                    'order'   => $existingCount + $index,
                ]);
            }
        }

        $target     = $post->targets()->first();
        $targetType = $validated['target_type'];
        $locationId = in_array($targetType, ['location', 'room']) ? ($validated['location_id'] ?? null) : null;
        $roomId     = ($targetType === 'room') ? ($validated['room_id'] ?? null) : null;

        if ($target) {
            $target->update([
                'target_type' => $targetType,
                'location_id' => $locationId,
                'room_id'     => $roomId,
            ]);
        } else {
            PostTarget::create([
                'post_id'     => $post->id,
                'target_type' => $targetType,
                'location_id' => $locationId,
                'room_id'     => $roomId,
            ]);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Soft-delete an announcement (moves to trash).
     */
    public function destroy(Post $post): RedirectResponse
    {
        // Soft delete — images kept until permanently destroyed
        $post->delete();

        return redirect()->back()
            ->with('success', 'Pengumuman dipindahkan ke riwayat.');
    }

    /**
     * Permanently delete a single trashed post (admin can delete any).
     */
    public function forceDestroy(int $id): RedirectResponse
    {
        $post = Post::onlyTrashed()->with('images')->findOrFail($id);

        // Delete legacy single image
        if ($post->image_path && Storage::disk('public')->exists($post->image_path)) {
            Storage::disk('public')->delete($post->image_path);
        }

        // Delete all gallery images
        foreach ($post->images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
        }

        $post->forceDelete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Pengumuman berhasil dihapus permanen.');
    }

    /**
     * Permanently delete ALL trashed posts (admin scope: all authors).
     */
    public function destroyAll(): RedirectResponse
    {
        $trashedPosts = Post::onlyTrashed()->with('images')->get();

        foreach ($trashedPosts as $post) {
            if ($post->image_path && Storage::disk('public')->exists($post->image_path)) {
                Storage::disk('public')->delete($post->image_path);
            }
            foreach ($post->images as $image) {
                if (Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                }
            }
            $post->forceDelete();
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Semua riwayat pengumuman berhasil dihapus permanen.');
    }
}
