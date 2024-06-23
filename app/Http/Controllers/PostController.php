<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return response()->json([
            'data' => $posts,
            'message' => 'Successfully retrived!',
            'status' => Response::HTTP_OK,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required",
            "image" => "required|image|mimes:png,jpg,jpeg",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()
            ], 422);
        }

        $image = $request->file('image');
        $imageName = uniqid() . '_' . $image->getClientOriginalName();
        $image->storeAs('public/post-images', $imageName);

        $validatedData = $validator->validated();
        $validatedData['image'] = $imageName;

        $post = Post::create($validatedData);

        return response()->json([
            'data' => $post,
            'message' => 'Successfully created!',
            'status' => Response::HTTP_CREATED,
        ], 201);
    }


    public function show(string $id)
    {
        $post = Post::find($id);
        return response()->json([
            'data' => $post,
            'message' => 'Successfully retrived!',
            'status' => Response::HTTP_OK
        ], 200);

    }
    public function update(Request $request, $id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            "title" => "required", // Allow title to be optional when present
            "image" => "nullable|image|mimes:png,jpg,jpeg",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()
            ], 422);
        }

        // Retrieve the post or fail
        $post = Post::findOrFail($id);

        // Handle file upload if provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/post-images', $imageName);

            // Remove the old image if necessary
            if ($post->image) {
                // Storage::delete('public/post-images/' . $post->image);
                File::delete('storage/post-images/' . $post->image);
            }

            $post->image = $imageName;
        }

        // Update title if provided
        if ($request->has('title')) {
            $post->title = $request->input('title');
        }

        // Save the updated post
        $post->save();

        return response()->json([
            'data' => $post,
            'message' => 'Successfully updated!',
            'status' => Response::HTTP_OK,
        ], 200);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if ($post->image) {
            Storage::delete('public/post-images/' . $post->image);
        }

        $post->delete();

        return response()->json([
            'message' => 'Successfully deleted!',
            'status' => Response::HTTP_OK,
        ], 200);
    }

}
