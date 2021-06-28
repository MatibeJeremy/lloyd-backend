<?php

namespace App\Http\Controllers\V1\Blogs;

use App\Models\Blog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use JWTAuth;

class ApiController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('jwt.verify')->except(['index']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'All Blogs Fetched Successfully',
            'data' => Blog::all()
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validate data
        $data = $request->only('title', 'description');
        $validator = Validator::make($data, [
            'title' => 'required|string',
            'description' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        // create new user
        $blog = new Blog();
        $blog->title = $request['title'];
        $blog->description = $request['description'];
        $blog->user_id = JWTAuth::user()->id;
        $blog->save();

        //Product created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Blog created successfully',
            'data' => $blog
        ], Response::HTTP_OK);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function edit(Blog $blog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        Blog::where('id', $id)
            ->update(
                [
                    'title' => $request->input('title'),
                    'description' => $request->input('description')
                ]
            );
        $updated_blog = Blog::where('id', $id)->get();

        //Product updated, return success response
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data'    => $updated_blog
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Blog::where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blog deleted successfully'
        ], Response::HTTP_OK);
    }
}
