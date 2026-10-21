@extends('emails.layout')

@section('title', 'Posts waiting for approval')
@section('tagline', 'Pending posts reminder')

@section('content')
  <h2 style="margin:0 0 12px;font-size:22px;color:#1f2340;">Hello {{ $recipientName }} 👋</h2>

  <p style="margin:0 0 20px;">
    <strong style="color:#4f46e5;">{{ $posts->count() }}</strong>
    {{ \Illuminate\Support\Str::plural('post', $posts->count()) }}
    {{ $posts->count() === 1 ? 'has' : 'have' }} been waiting for approval
    @if($days > 0) for {{ $days }}+ {{ \Illuminate\Support\Str::plural('day', $days) }} @endif.
  </p>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
         style="background:#f4f5ff;border-radius:12px;margin:0 0 24px;">
    <tr>
      <td style="padding:10px 16px;font-size:12px;color:#6b6f93;">POST</td>
      <td style="padding:10px 16px;font-size:12px;color:#6b6f93;">AUTHOR</td>
      <td style="padding:10px 16px;font-size:12px;color:#6b6f93;">WAITING</td>
    </tr>
    @foreach($posts->take(10) as $post)
      <tr>
        <td style="padding:10px 16px;font-size:13px;font-weight:700;color:#1f2340;">{{ $post->title }}</td>
        <td style="padding:10px 16px;font-size:13px;color:#1f2340;">{{ $post->user?->name ?? 'Unknown' }}</td>
        <td style="padding:10px 16px;font-size:13px;color:#6b6f93;">{{ $post->updated_at->diffForHumans() }}</td>
      </tr>
    @endforeach
  </table>

  @if($posts->count() > 10)
    <p style="margin:0 0 20px;font-size:13px;color:#6b6f93;">...and {{ $posts->count() - 10 }} more.</p>
  @endif

  <table role="presentation" cellpadding="0" cellspacing="0" align="center">
    <tr>
      <td align="center" bgcolor="#4f46e5" style="border-radius:10px;">
        <a href="{{ $reviewUrl }}"
           style="display:inline-block;padding:14px 30px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:10px;background:#4f46e5;">
          Review pending posts
        </a>
      </td>
    </tr>
  </table>
@endsection
