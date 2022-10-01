<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userProfile = UserProfile::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($userProfile);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('userProfiles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'user_profile' => 'required',
            'description' => 'required'
        ]);

        // Create Post
        $userProfile = new UserProfile;
        $userProfile->user_profile = $request->input('user_profile');
        $userProfile->description = $request->input('description');
        $userProfile->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($userProfile);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @return \Illuminate\Http\Response
     */
    public function show(UserProfile $userProfile)
    {
        $userProfile = UserProfile::find($userProfile->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($userProfile);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @return \Illuminate\Http\Response
     */
    public function edit(UserProfile $userProfile)
    {
        $userProfile = UserProfile::find($userProfile->id);
        return response()->json($userProfile);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserProfile  $userProfile
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserProfile $userProfile)
    {
        $userProfile = UserProfile::find($userProfile->id);
        
        $userProfile->user_profile = $request->input('user_profile');
        $userProfile->description = $request->input('description');
        $userProfile->save();
      

        return response()->json($userProfile);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserProfile  $userProfile
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserProfile $userProfile)
    {
        $userProfile = UserProfile::find($userProfile->id);
        $userProfile->delete();
        return response()->json($userProfile);
    }
}
