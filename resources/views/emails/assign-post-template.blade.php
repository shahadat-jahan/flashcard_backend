@extends('emails.layouts.email-template')
@section('title', 'Post Assignment Notification')

@section('header')
    <div class="header">
        Post Assignment Notification
    </div>
@endsection

@section('greeting')
    <div class="greeting">
        Dear <strong>{{ $notifiable->name }}</strong>,
    </div>
@endsection

@section('content')
    <div class="content">
        <p>You have been assigned a new post by <strong>{{ $assignBy->name }}</strong>. Below are the details of the
            post:</p>
        <p><strong>Title:</strong> {{ $post->title }}</p>
        <p><strong>Topics:</strong> {{ $topics }}</p>
        <p><strong>Due Date:</strong> {{ $dueDate }}</p>
        <p>We appreciate your prompt attention to this post. Should you have any questions or need further
            clarification, please do not hesitate to reach out.</p>
    </div>
    <div class="button-container">
        <a href="{{ $url }}"
           class="button">
            View Assigned Post
        </a>
    </div>
@endsection