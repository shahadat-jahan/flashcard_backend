@extends('emails.layouts.email-template')
@section('title', 'Set Password Notification')

@section('header')
    <div class="header">
        Welcome! Please Join the FlashCard System
    </div>
@endsection

@section('greeting')
    <div class="greeting">
        Dear <strong>{{ $notifiable->name }}</strong>,
    </div>
@endsection

@section('content')
    <div class="content">
        <p>You’re being added to our FlashCard system. Get ready to dive in and start contributing to our exciting
            blog
            posts!
        </p>
        <p>Please join using the link below:</p>
        <div class="button-container">
            <a class="button" href="{{$setPasswordUrl}}">Set password.</a>
        </div>
    </div>
@endsection