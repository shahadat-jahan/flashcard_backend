@extends('emails.layouts.email-template')
@section('title', 'Post Declined Notification')

@section('header')
    <div class="header">
        Post Declined Notification
    </div>
@endsection

@section('greeting')
    <div class="greeting">
        Dear <strong>{{ $notifiable->name }}</strong>,
    </div>
@endsection

@section('content')
    <div class="content">
        <p>Your post has been declined by <strong>{{ $declinedBy->name }}</strong>. Below are the details of
            the post:</p>
        <p><strong>Title:</strong> {{ $post['title'] }}</p>
        <p><strong>Topics:</strong> {{ $topics }}</p>
        <p><strong>Decline reason:</strong> {{ $declinedReason }}</p>
        <p>We appreciate your prompt attention to this post. Should you have any questions or need further
            clarification, please do not hesitate to reach out.</p>
    </div>
    <div class="button-container">
        <a href="{{ $url }}"
           class="button">
            View Declined Post
        </a>
    </div>
@endsection