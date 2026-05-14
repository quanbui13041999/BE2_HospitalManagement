@component('mail::message')
# {{ $article->title }}

{{ $article->excerpt }}

@component('mail::button', ['url' => route('news.show', $article->news_id)])
Xem chi tiết bản tin
@endcomponent

Trân trọng,<br>
Hệ thống {{ config('app.name') }}
@endcomponent
