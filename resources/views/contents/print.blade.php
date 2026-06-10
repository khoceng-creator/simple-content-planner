<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $contentPlan->headline }} · {{ $contentPlan->brand->name }}</title>
    <style>
        *{box-sizing:border-box}body{font:15px/1.55 Arial,sans-serif;color:#171717;margin:32px;max-width:960px}
        h1{font-size:28px;margin:0 0 4px}h2{font-size:15px;margin:24px 0 8px}.muted{color:#666}
        .meta{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0}.pill{border:1px solid #ccc;border-radius:999px;padding:4px 10px;font-size:12px}
        .box{border:1px solid #ddd;border-radius:10px;padding:14px}.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
        figure{margin:0}img{width:100%;height:auto;border-radius:8px}figcaption{font-size:12px;color:#666;margin-top:5px}
        a{color:#7a2424;word-break:break-all}.print-btn{position:fixed;right:24px;top:24px;padding:10px 16px;border:0;border-radius:8px;background:#171717;color:#fff;cursor:pointer}
        @media print{body{margin:16mm;max-width:none}.print-btn{display:none}.box{break-inside:avoid}.grid{grid-template-columns:repeat(2,1fr)}}
    </style>
</head>
<body>
    <button class="print-btn" type="button" onclick="window.print()">Print / Save as PDF</button>
    <h1>{{ $contentPlan->headline }}</h1>
    <div class="muted">{{ $contentPlan->brand->name }} · {{ $contentPlan->formatted_schedule }}</div>
    <div class="meta">
        <span class="pill">{{ $contentPlan->type_label }}</span>
        <span class="pill">{{ $contentPlan->is_made ? 'Sudah dibuat' : 'Belum dibuat' }}</span>
        @if (($contentPlan->platforms['instagram'] ?? false)) <span class="pill">Instagram</span> @endif
        @if (($contentPlan->platforms['tiktok'] ?? false)) <span class="pill">TikTok</span> @endif
    </div>
    <h2>Detail / script</h2><div class="box">{!! $contentPlan->detail_html ?: '-' !!}</div>
    <h2>Catatan</h2><div class="box">{!! $contentPlan->note_html ?: '-' !!}</div>
    <h2>Gambar</h2>
    @if ($contentPlan->images->isNotEmpty())
        <div class="grid">@foreach ($contentPlan->images as $image)<figure><img src="{{ $image->displayUrl() }}" alt="{{ $image->original_name }}"><figcaption>{{ $image->original_name }}</figcaption></figure>@endforeach</div>
    @else <div class="box">Belum ada gambar.</div> @endif
    <h2>Link dokumen</h2><div class="box">@if ($contentPlan->document_link)<a href="{{ $contentPlan->document_link }}">{{ $contentPlan->document_link }}</a>@else - @endif</div>
</body>
</html>
