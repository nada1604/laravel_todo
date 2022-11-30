<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; //carbon = mengelola yg berhubungan dengan angka

class ChallengeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function login()
    {
        return view('dashboard.login');
    }

    public function register()
    {
        return view('dashboard.register');
    }

    public function inputRegister(Request$request)
    {
        $request->validate([
            'name'=> 'required|min:4|max:50',
            'email'=> 'required',
            'password'=>'required',
            'username'=> 'required|min:4|max:8',
        ]);

        User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'password'=>Hash::make($request->password),
            'username'=>$request->username,
        ]);

        return redirect('/')->with('succes', 'Anda berhasil membuat akun');
    }

    public function auth(Request$request)
    {
        $request->validate([
            'password'=>'required',
            'username'=> 'required',
        ]);

    $user = $request->only('username', 'password');
    if(Auth::attempt($user)){
        return redirect()->route('todo.index');
    }else{
        return redirect('/')->with('fail', "Gagal login, periksa dan coba lagi!");
    }
    }

    public function logout()
    {
       Auth::logout();
       return redirect('/');
    }

    public function index()
    {
        //menampilkan halaman awal, semua data
        //ambil semua data Challenge dari database
        //cari data todo yg punya user_idnya sama dengan id orang yang login. kalu ketemu datanya diambil
        $challenges = Challenge::where([
            ['user_id', '=', Auth::user()->id],
            ['status', '=', 0],
            ])->get();
        //tampilan file index difolder dashboard dan bawa data dari variable yang namanya Challenge ke file tersebut
        return view('dashboard.index', compact('challenges'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('dashboard.create');
    }

    public function complated()
    {
        $challenges = Challenge::where([
            ['user_id', '=', Auth::user()->id],
            ['status', '=', 1],
            ])->get();
        return view('dashboard.complated', compact('challenges'));
    }

    public function updateComplated($id)
    {
        //$id pada parameter mengambil data dari patch dinamis{id}
        //cari data yang memiliki value coloum id sama dengan data id yang dikirim ke route, maka update baris data tersebut
        Challenge::where('id', $id)->update([
            'status' => 1,
            'done_time' => Carbon::now(),
        ]);
        //kalau berhasil bakal diarahin
        return redirect()->route('todo.complated')->with('done', 'todo sudah dikerjakan!');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //mengirim data kedata base
        //validasi
        $request->validate([
            'title' => 'required|min:3',
            'date' => 'required',
            'description' => 'required|min:8',
        ]);
        //tambah data ke db
        Challenge::create([
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'status' => 0,
            'user_id' => Auth::user()->id,
        ]);
        return redirect()->route('todo.index')->with('successAdd', 'Berhasil menambahkan data challenge!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Challenge  $challenge
     * @return \Illuminate\Http\Response
     */
    public function show(Challenge $challenge)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Challenge  $challenge
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //memapilkan form edit data
        //ambil data dari db yang idnya sama dengan id yang dikirim dari route
        $challenge = Challenge::where('id', $id)->first();
        //lalu tampilkan halaman dari view edit dengan mengirim data yang ada di variable todo
        return view ('dashboard.edit', compact('challenge'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Challenge  $challenge
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|min:3',
            'date' => 'required',
            'description' => 'required|min:8',
        ]);
        //update data yang idnya sama dengan id dari route, updatenya ke db bangian table todos
        Challenge::where([
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'status' => 0,
            'user_id' => Auth::user()->id,
        ]);
        //kalau berhasil bakal diarahin ke halamann awal todo dengan pemberitahuan  berhasil
        return redirect('/todo/')->with('successUpdate', 'Data berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Challenge  $challenge
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //parameter $id akan menggambil data dari patch dinamis {id}
        //cari data yg isisan coloum id nya sama dengan $id yg dikirim ke patch dinamis
        //kalau ada, ambip terus hapus datanya
        Challenge::where('id', '=', $id)->delete();
        //klo berhasil, bakal dibalikin kehalaman list todo dengan pemberitahuan
        return redirect()->route('todo.index')->with('successDelete', 'Berhasil mengapus data ToDo!');
    }
}

