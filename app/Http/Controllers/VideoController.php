<?php
namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{

    public function index()
    {
        $videos = Video::latest()->get();

        // Transform the collection to include the full web accessible URL for the videos
        $videos->transform(function ($video) {
            $video->file_path = asset('storage/' . $video->file_path);
            return $video;
        });

        return response()->json([
            'success' => true,
            'data' => $videos,
        ], 200);
    }

    public function upload(Request $request)
    {
        // 1. Validate inputs
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska|max:51200',
        ]);

        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $filename = time() . '_' . $file->getClientOriginalName();

            // 2. Store file locally inside the 'public/videos' path
            $path = $file->storeAs('videos', $filename, 'public');

            // 3. Save relative path to SQLite database
            $video = new Video();
            $video->title = $request->input('title');
            $video->description = $request->input('description');
            $video->file_path = $path; // Saves as 'videos/filename.mp4'
            $video->save();

            return response()->json([
                'message' => 'Video details stored successfully.',
                'data' => $video,
            ], 200);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }
}