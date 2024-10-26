<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $email = Email::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($email);
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
        $this->validate($request, [
            'email' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $email = new Email;
        $email->email = $request->input('email');
        $email->status = 1;
        $email->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($email);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Email  $email
     * @return \Illuminate\Http\Response
     */
    public function show(Email $email)
    {
        $email = Email::find($email->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($email);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Email  $email
     * @return \Illuminate\Http\Response
     */
    public function edit(Email $email)
    {
        $email = Email::find($email->id);
        return response()->json($email);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Email  $email
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Email $email)
    {
        $email = Email::find($brand->id);
        $email->email = $request->input('email');
        $email->status = $request->input('status');
        $email->save();

        return response()->json($email);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Email  $email
     * @return \Illuminate\Http\Response
     */
    public function destroy(Email $email)
    {
        $email = Email::find($email->id);
        $email->delete();
        return response()->json($email);
    }
}
