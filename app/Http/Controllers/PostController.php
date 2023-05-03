<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\Tag;
use App\Models\PostTag;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function create(Request $req){
        
        $req->validate([
            'title'       => 'required|string',
            'description' => 'required|string',
            'tags' => 'array'
        ]);
        
        try {
            
            
            $post = Post::create([
                "title"       => $req->title,
                "description" => $req->description,
                "author_id"   => $req->user()->id,
                "created_at"  => \Carbon\Carbon::now(),
                "updated_at"  => \Carbon\Carbon::now()
            ]);

            if($req->tags){
                $this->createTag($req->tags,$post);
            }
            
            return response([
                "msg"    => "Created new post successfully",
                "status" => 1,
                "post"   => $post
            ],201);

        } catch (\Exception $e) {
            dd($e);
            return response([
                "msg"    => "Failed to create new post.",
                "error"  => $e->getMessage(),
                "status" => 0
            ],500);
        }
    }
    public function getTagPosts(Request $req){
        try{

            $posts = Tag::where("value",$req->tag)->first()->posts;
            return response([
                "post" => $posts
            ]);

        } catch (\Exception $e) {
            dd($e);
            return response([
                "msg"    => "Failed to create new post.",
                "error"  => $e->getMessage(),
                "status" => 0
            ],500);
        }
    }
    public function createTag($tags,$post){

        foreach ($tags as $key => $tagName) {
            $tag = Tag::where(["value" => $tagName])->first();
            if($tag){
                PostTag::create([
                    "post_id" => $post->id,
                    "tag_id"  => $tag->id
                ]);        
            }else{

                $tag = Tag::create(["value" => $tagName]);
                PostTag::create([
                    "post_id" => $post->id,
                    "tag_id"  => $tag->id
                ]);
            }
        }
    }
    
    public function deletePost(Request $req){

        $req->validate([
            'post_id'     => 'required|int'
        ]);

        try {
            
            $post = Post::
            where("id",$req->post_id)
            ->delete();

            return response([
                "msg"    => "Deleted post successfully",
                "status" => 1,
                "post"   => $post
            ],200);

        } catch (\Exception $e) {

            return response([
                "msg"    => "Failed to delete post.",
                "error"  => $e->getMessage(),
                "status" => 0
            ],500);
        }
    }

    public function update(Request $req){
        
        $req->validate([
            'post_id'     => 'required|int',
            'title'       => 'required|string',
            'description' => 'required|string'
        ]);
        
        try {
            
            $post = Post::
            where("id",$req->post_id)
            ->update([
                "title"       => $req->title,
                "description" => $req->description,
                "updated_at"  => \Carbon\Carbon::now()
            ]);

            return response([
                "msg"    => "Updated new post successfully",
                "status" => 1,
                "post"   => $post
            ],200);

        } catch (\Exception $e) {

            return response([
                "msg"    => "Failed to update post.",
                "error"  => $e->getMessage(),
                "status" => 0
            ],500);
        }
    }
    public function list()
    {
        $posts = Post::get();

        $data = collect();
        foreach ($posts as $post) {
            $data->add([
                'id' => $post->id,
                'title' => $post->title,
                'description' => $post->description,
                'tags' => $post->tags,
                'like_counts' => $post->likes->count(),
                'created_at' => $post->created_at,
            ]);
        }

        return response()->json([
            'data' => $data,
        ]);
    }

    public function toggleReaction(Request $request)
    {
        $request->validate([
            'post_id' => 'required|int|exists:posts,id',
            'like' => 'required|boolean',
        ]);

        $post = Post::find($request->post_id);
        if (! $post) {
            return response()->json([
                'status' => 404,
                'message' => 'model not found',
            ]);
        }

        if ($post->user_id == auth()->id()) {
            return response()->json([
                'status' => 500,
                'message' => 'You cannot like your post',
            ]);
        }

        $like = Like::where('post_id', $request->post_id)->where('user_id', auth()->id())->first();
        if ($like && $like->post_id == $request->post_id && $request->like) {
            return response()->json([
                'status' => 500,
                'message' => 'You already liked this post',
            ]);
        } elseif ($like && $like->post_id == $request->post_id && ! $request->like) {
            $like->delete();

            return response()->json([
                'status' => 200,
                'message' => 'You unlike this post successfully',
            ]);
        }

        Like::create([
            'post_id' => $request->post_id,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'You like this post successfully',
        ]);
    }
}
