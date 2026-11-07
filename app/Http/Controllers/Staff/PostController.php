<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StorePostRequest;
use App\Models\Location;
use App\Models\Post;
use App\Models\PostTarget;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a list of announcements created by staff.
     */
    public function index(): View
    {
        $posts = Post::with(['author', 'targets.location', 'targets.room'])
            ->where('author_id', Auth::id())
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('staff.posts.index', [
            'posts' => $posts,
        ]);
    }

    /**
     * Show the dedicated form for creating a new announcement.
     */
    public function create(): View
    {
        $locations = Location::where('status', 'active')->orderBy('name')->get();
        $allRooms = Room::with('location')
            ->where('status', 'active')
            ->orderBy('room_number')
            ->get();

        return view('staff.posts.create', [
            'locations' => $locations,
            'allRooms'  => $allRooms,
        ]);
    }

    /**
     * Store a newly created announcement post by staff.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create([
            'author_id'    => Auth::id(),
            'title'        => $validated['title'],
            'content'      => $validated['content'],
            'image_path'   => $imagePath,
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $targetType = $validated['target_type'];
        $locationId = in_array($targetType, ['location', 'room']) ? $validated['location_id'] : null;
        $roomId = ($targetType === 'room') ? $validated['room_id'] : null;

        PostTarget::create([
            'post_id'     => $post->id,
            'target_type' => $targetType,
            'location_id' => $locationId,
            'room_id'     => $roomId,
        ]);

        return redirect()->route('staff.dashboard')
            ->with('success', 'Pengumuman berhasil dipublikasikan untuk target yang ditentukan.');
    }

    /**
     * Delete an announcement post authored by staff.
     */
    public function destroy(Post $post): RedirectResponse
    {
        // Ensure only author or authorized staff/admin can delete
        if ($post->author_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus pengumuman ini.');
        }

        if ($post->image_path && Storage::disk('public')->exists($post->image_path)) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        return redirect()->back()
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
