@extends('emails.layouts.email-template')
@section('title', 'Post Submitted Notification')

@section('header')
    <div class="header">
        Post Submitted Notification
    </div>
@endsection

@section('greeting')
    <div class="greeting">
        Dear <strong>{{ $notifiable->name }}</strong>,
    </div>
@endsection

@section('content')
    <div class="content">
        <p><strong>{{ $submittedBy->name }}</strong> has submitted a post. Below are the details of
            the post:</p>
        <p><strong>Title:</strong> {{ $post->title }}</p>
        <p><strong>Topics:</strong> {{ $topics }}</p>
        <p><strong>Due Date:</strong> {{ $dueDate }}
            @if (\Carbon\Carbon::parse($dueDate)->isPast())
                <span style="color: red;">
                     (Overdue {{ \Carbon\Carbon::parse($dueDate)->diffForHumans(now()) }})
                </span>
            @endif
        </p>
        <p>We appreciate your prompt attention to this post. Should you have any questions or need further
            clarification, please do not hesitate to reach out.</p>
    </div>
    <div class="button-container">
        <a href="{{ $url }}"
           class="button">
            View Submitted Post
        </a>
    </div>
@endsection