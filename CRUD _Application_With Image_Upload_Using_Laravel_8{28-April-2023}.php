CRUD Application With Image Upload Using Laravel 8
**************************************************
url:- https://dcodemania.com/post/crud-application-image-upload-laravel-8
-------------------------------------------------------------------------
composer create-project laravel/laravel crud-app

Step 2: Database Configuration

B_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=crud_app
DB_USERNAME=root
DB_PASSWORD=

Step 3: Creating Model, Migration and Controller

php artisan make:model Post -mc --resource

database/migrations/create_posts_table.php
------------------------------------------
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('posts', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->string('category');
      $table->longText('content');
      $table->string('image');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('posts');
  }
}

php artisan migrate
Route::resource('post', 'App\Http\Controllers\PostController');
php artisan route:list
app/Models/Post.php
--------------------
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
  use HasFactory;
  protected $fillable = ['title', 'category', 'content', 'image'];
}

app/Http/Controllers/PostController.php
---------------------------------------
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller {
  /**
   * Display a listing of the resource.
   *
   * @return IlluminateHttpResponse
   */
  public function index() {
    $posts = Post::orderBy('id', 'desc')->paginate(3);
    return view('post.index', ['posts' => $posts]);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return IlluminateHttpResponse
   */
  public function create() {
    return view('post.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  IlluminateHttpRequest  $request
   * @return IlluminateHttpResponse
   */
  public function store(Request $request) {
    $request->validate([
      'title' => 'required',
      'category' => 'required',
      'content' => 'required|min:50',
      'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    $imageName = time() . '.' . $request->file->extension();
    // $request->image->move(public_path('images'), $imageName);
    $request->file->storeAs('public/images', $imageName);

    $postData = ['title' => $request->title, 'category' => $request->category, 'content' => $request->content, 'image' => $imageName];

    Post::create($postData);
    return redirect('/post')->with(['message' => 'Post added successfully!', 'status' => 'success']);
  }

  /**
   * Display the specified resource.
   *
   * @param  AppModelsPost  $post
   * @return IlluminateHttpResponse
   */
  public function show(Post $post) {
    return view('post.show', ['post' => $post]);
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  AppModelsPost  $post
   * @return IlluminateHttpResponse
   */
  public function edit(Post $post) {
    return view('post.edit', ['post' => $post]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  IlluminateHttpRequest  $request
   * @param  AppModelsPost  $post
   * @return IlluminateHttpResponse
   */
  public function update(Request $request, Post $post) {
    $imageName = '';
    if ($request->hasFile('file')) {
      $imageName = time() . '.' . $request->file->extension();
      $request->file->storeAs('public/images', $imageName);
      if ($post->image) {
        Storage::delete('public/images/' . $post->image);
      }
    } else {
      $imageName = $post->image;
    }

    $postData = ['title' => $request->title, 'category' => $request->category, 'content' => $request->content, 'image' => $imageName];

    $post->update($postData);
    return redirect('/post')->with(['message' => 'Post updated successfully!', 'status' => 'success']);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  AppModelsPost  $post
   * @return IlluminateHttpResponse
   */
  public function destroy(Post $post) {
    Storage::delete('public/images/' . $post->image);
    $post->delete();
    return redirect('/post')->with(['message' => 'Post deleted successfully!', 'status' => 'info']);
  }
}

php artisan storage:link

resources/views/app.blade.php 
-----------------------------
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title')</title>

  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.2/css/bootstrap.min.css' />
</head>

<body>
  <div class="container">
    <div class="row my-2">
      <div class="col-lg-12 d-flex justify-content-between align-items-center mx-auto">
        <div>
          <h2 class="text-primary">@yield('heading')</h2>
        </div>
        <div>
          <a href="@yield('link')" class="btn btn-primary rounded-pill">@yield('link_text')</a>
        </div>

      </div>
    </div>
    <hr class="my-2">

    @yield('content')

  </div>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.2/js/bootstrap.bundle.min.js'></script>
</body>

</html>

resources/views/post/create.blade.php
-------------------------------------
@extends('layout.app')
@section('title', 'Add New Post')
@section('heading', 'Create a New Post')
@section('link_text', 'Goto All Posts')
@section('link', '/post')

@section('content')

<div class="row my-3">
  <div class="col-lg-8 mx-auto">
    <div class="card shadow">
      <div class="card-header bg-primary">
        <h3 class="text-light fw-bold">Add New Post</h3>
      </div>
      <div class="card-body p-4">
        <form action="/post" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="my-2">
            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" placeholder="Title" value="{{ old('title') }}">
            @error('title')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="my-2">
            <input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror" placeholder="Category" value="{{ old('category') }}">
            @error('category')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="my-2">
            <input type="file" name="file" id="file" accept="image/*" class="form-control @error('file') is-invalid @enderror">
            @error('file')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="my-2">
            <textarea name="content" id="content" rows="6" class="form-control @error('content') is-invalid @enderror" placeholder="Post Content">{{ old('content') }}</textarea>
            @error('content')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="my-2">
            <input type="submit" value="Add Post" class="btn btn-primary">
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

2. edit.blade.php
-------------------
@extends('layout.app')
@section('title', 'Edit Post')
@section('heading', 'Edit This Post')
@section('link_text', 'Goto All Posts')
@section('link', '/post')

@section('content')

<div class="row my-3">
  <div class="col-lg-8 mx-auto">
    <div class="card shadow">
      <div class="card-header bg-primary">
        <h3 class="text-light fw-bold">Edit Post</h3>
      </div>
      <div class="card-body p-4">
        <form action="/post/{{ $post->id }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <div class="my-2">
            <input type="text" name="title" id="title" class="form-control" placeholder="Title" value="{{ $post->title }}" required>
          </div>

          <div class="my-2">
            <input type="text" name="category" id="category" class="form-control" placeholder="Category" value="{{ $post->category }}" required>
          </div>

          <div class="my-2">
            <input type="file" name="file" id="file" accept="image/*" class="form-control">
          </div>

          <img src="{{ asset('storage/images/'.$post->image) }}" class="img-fluid img-thumbnail" width="150">

          <div class="my-2">
            <textarea name="content" id="content" rows="6" class="form-control" placeholder="Post Content" required>{{ $post->content }}</textarea>
          </div>

          <div class="my-2">
            <input type="submit" value="Update Post" class="btn btn-primary">
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

3. index.blade.php
-------------------
@extends('layout.app')
@section('title', 'Home Page')
@section('heading', 'All Posts')
@section('link_text', 'Add New Post')
@section('link', '/post/create')

@section('content')

@if(session('message'))
<div class="alert alert-{{ session('status') }} alert-dismissible fade show" role="alert">
  <strong>{{ session('message') }}</strong>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
<div class="row g-4 mt-1">
  @forelse($posts as $key => $row)
  <div class="col-lg-4">

      <div class="card shadow">
        <a href="post/{{ $row->id }}">
          <img src="{{ asset('storage/images/'.$row->image) }}" class="card-img-top img-fluid">
        </a>
        <div class="card-body">
          <p class="btn btn-success rounded-pill btn-sm">{{ $row->category }}</p>
          <div class="card-title fw-bold text-primary h4">{{ $row->title }}</div>
          <p class="text-secondary">{{ Str::limit($row->content, 100) }}</p>
        </div>
      </div>

  </div>
  @empty
    <h2 class="text-center text-secondary p-4">No post found in the database!</h2>
  @endforelse
  <div class="d-flex justify-content-center my-5">
    {{ $posts->onEachSide(1)->links() }}
  </div>

</div>

@endsection

4. show.blade.php
------------------
@extends('layout.app')
@section('title', 'Post Details')
@section('heading', 'Post Details')
@section('link_text', 'Goto All Posts')
@section('link', '/post')

@section('content')

<div class="row my-4">
  <div class="col-lg-8 mx-auto">
    <div class="card shadow">
      <img src="{{ asset('storage/images/'.$post->image) }}" class="img-fluid card-img-top">
      <div class="card-body p-5">
        <div class="d-flex justify-content-between align-items-center">
          <p class="btn btn-dark rounded-pill">{{ $post->category }}</p>
          <p class="lead">{{ \Carbon\Carbon::parse($post->created_at)->diffForHumans() }}</p>
        </div>

        <hr>
        <h3 class="fw-bold text-primary">{{ $post->title }}</h3>
        <p>{{ $post->content }}</p>
      </div>
      <div class="card-footer px-5 py-3 d-flex justify-content-end">
        <a href="/post/{{$post->id}}/edit" class="btn btn-success rounded-pill me-2">Edit</a>
        <form action="/post/{{$post->id}}" method="POST">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger rounded-pill">Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

Paginator::useBootstrap();
php artisan serve
