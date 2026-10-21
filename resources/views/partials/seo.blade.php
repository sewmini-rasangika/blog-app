@php
use Illuminate\Support\Facades\Storage;

$title = $seoTitle ?? ($post->title ?? config('app.name'));
$description = $seoDescription ?? ($post->excerpt ?? Str::limit(strip_tags($post->content ?? ''), 160));
$url = $seoUrl ?? url()->current();

if (!empty($seoImage)) {
    // DB එකේ /storage/posts/xxx.jpg වගේ තියෙන එක Full URL එකක් කරනවා
    $img = str_starts_with($seoImage, 'http') ? $seoImage : asset('storage/' . ltrim($seoImage, '/'));
} else {
    $img = asset('images/default-og.jpg'); // ඔයාට default image එකක් නැත්තම් මේ line එක අයින් කරන්න
}
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $url }}">


<meta property="og:type" content="article">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ $img }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $img }}">