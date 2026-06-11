<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $contentPlan->headline }} · {{ $contentPlan->brand->name }}</title>
    <style>
        @page { margin: 18mm 16mm 20mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #24211f;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10.5px;
            line-height: 1.55;
        }
        .accent-bar { height: 5px; margin-bottom: 18px; border-radius: 5px; background: #8d3030; }
        .header, .summary-table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: middle; }
        .brand-mark { width: 54px; }
        .brand-mark img { display: block; width: 42px; height: 42px; object-fit: contain; border-radius: 9px; }
        .brand-name { margin: 0; font-size: 13px; font-weight: bold; }
        .document-label { color: #8d3030; font-size: 8px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; }
        .header-meta { color: #77706a; font-size: 8.5px; text-align: right; }
        .hero {
            margin-top: 22px;
            padding: 22px;
            border: 1px solid #ded7d0;
            border-radius: 14px;
            background: #f7f3ef;
        }
        .hero-table { width: 100%; border-collapse: collapse; }
        .hero-table td { vertical-align: top; }
        .hero-logo { width: 76px; padding-left: 16px; text-align: right; }
        .hero-logo img { width: 62px; height: 62px; object-fit: contain; border-radius: 12px; background: #fff; }
        .kicker { margin-bottom: 7px; color: #8d3030; font-size: 8px; font-weight: bold; letter-spacing: 1.1px; text-transform: uppercase; }
        h1 { margin: 0 0 9px; color: #171514; font-size: 24px; line-height: 1.2; }
        .schedule { color: #625c57; font-size: 11px; }
        .pills { margin-top: 15px; }
        .pill {
            display: inline-block;
            margin: 0 5px 5px 0;
            padding: 4px 9px;
            border: 1px solid #d8d0c9;
            border-radius: 10px;
            background: #fff;
            color: #49433e;
            font-size: 8.5px;
        }
        .pill-status { border-color: #8d3030; background: #8d3030; color: #fff; }
        .summary-table { margin-top: 12px; table-layout: fixed; }
        .summary-table td { width: 33.33%; padding-right: 8px; vertical-align: top; }
        .summary-table td:last-child { padding-right: 0; }
        .summary-card {
            min-height: 58px;
            padding: 11px;
            border: 1px solid #e2dcd6;
            border-radius: 10px;
        }
        .summary-label { color: #8b837c; font-size: 7.5px; font-weight: bold; letter-spacing: .8px; text-transform: uppercase; }
        .summary-value { margin-top: 4px; color: #262321; font-size: 10px; font-weight: bold; }
        .section { margin-top: 20px; page-break-inside: avoid; }
        .section-heading {
            margin: 0 0 8px;
            padding-bottom: 7px;
            border-bottom: 1px solid #e1dbd5;
            color: #332f2c;
            font-size: 11px;
        }
        .section-number {
            display: inline-block;
            width: 21px;
            margin-right: 7px;
            padding: 3px 0;
            border-radius: 10px;
            background: #8d3030;
            color: #fff;
            font-size: 7.5px;
            text-align: center;
        }
        .content-box {
            padding: 13px 15px;
            border: 1px solid #e2dcd6;
            border-radius: 10px;
            background: #fcfaf8;
            overflow-wrap: break-word;
        }
        .content-box p, .content-box div { margin: 0 0 7px; }
        .content-box p:last-child, .content-box div:last-child { margin-bottom: 0; }
        .content-box ul, .content-box ol { margin: 6px 0 6px 18px; padding: 0; }
        .empty { color: #918982; font-style: italic; }
        .image-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .image-grid td { width: 50%; padding: 0 7px 14px 0; vertical-align: top; }
        .image-grid td:nth-child(even) { padding-right: 0; padding-left: 7px; }
        .image-card { padding: 7px; border: 1px solid #e2dcd6; border-radius: 10px; page-break-inside: avoid; }
        .image-card img { display: block; width: 100%; max-height: 230px; object-fit: contain; border-radius: 7px; background: #f3efeb; }
        .image-name { margin-top: 6px; color: #77706a; font-size: 7.5px; overflow-wrap: break-word; }
        a { color: #7d2929; text-decoration: none; overflow-wrap: break-word; }
        .footer {
            position: fixed;
            right: 0;
            bottom: -10mm;
            left: 0;
            padding-top: 6px;
            border-top: 1px solid #e1dbd5;
            color: #918982;
            font-size: 7.5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="footer">
        IMM Content Planner · Dokumen dibuat {{ now()->locale('id')->translatedFormat('d F Y, H.i') }}
    </div>

    <div class="accent-bar"></div>
    <table class="header">
        <tr>
            <td class="brand-mark">
                @if ($logoDataUri)
                    <img src="{{ $logoDataUri }}" alt="IMM">
                @endif
            </td>
            <td>
                <div class="document-label">Content plan document</div>
                <p class="brand-name">{{ $contentPlan->brand->name }}</p>
            </td>
            <td class="header-meta">
                IMM Content Planner<br>
                {{ $contentPlan->posting_date->format('Y-m-d') }}
            </td>
        </tr>
    </table>

    <section class="hero">
        <table class="hero-table">
            <tr>
                <td>
                    <div class="kicker">Jadwal konten</div>
                    <h1>{{ $contentPlan->headline }}</h1>
                    <div class="schedule">{{ $contentPlan->formatted_schedule }}</div>
                    <div class="pills">
                        <span class="pill">{{ $contentPlan->type_label }}</span>
                        <span class="pill pill-status">{{ $contentPlan->is_made ? 'Sudah dibuat' : 'Belum dibuat' }}</span>
                        @if (($contentPlan->platforms['instagram'] ?? false))
                            <span class="pill">Instagram</span>
                        @endif
                        @if (($contentPlan->platforms['tiktok'] ?? false))
                            <span class="pill">TikTok</span>
                        @endif
                    </div>
                </td>
                @if ($brandLogoDataUri)
                    <td class="hero-logo"><img src="{{ $brandLogoDataUri }}" alt="{{ $contentPlan->brand->name }}"></td>
                @endif
            </tr>
        </table>
    </section>

    <table class="summary-table">
        <tr>
            <td><div class="summary-card"><div class="summary-label">Tanggal tayang</div><div class="summary-value">{{ $contentPlan->posting_date->locale('id')->translatedFormat('d F Y') }}</div></div></td>
            <td><div class="summary-card"><div class="summary-label">Waktu</div><div class="summary-value">{{ $contentPlan->posting_time ? substr((string) $contentPlan->posting_time, 0, 5) : 'Belum ditentukan' }}</div></div></td>
            <td><div class="summary-card"><div class="summary-label">Media</div><div class="summary-value">{{ $pdfImages->count() }} gambar</div></div></td>
        </tr>
    </table>

    <section class="section">
        <h2 class="section-heading"><span class="section-number">01</span>Detail / script</h2>
        <div class="content-box">
            {!! $contentPlan->detail_html ?: '<span class="empty">Belum ada detail atau script.</span>' !!}
        </div>
    </section>

    <section class="section">
        <h2 class="section-heading"><span class="section-number">02</span>Catatan produksi</h2>
        <div class="content-box">
            {!! $contentPlan->note_html ?: '<span class="empty">Belum ada catatan produksi.</span>' !!}
        </div>
    </section>

    <section class="section">
        <h2 class="section-heading"><span class="section-number">03</span>Media pendukung</h2>
        @if ($pdfImages->isNotEmpty())
            <table class="image-grid">
                @foreach ($pdfImages->chunk(2) as $row)
                    <tr>
                        @foreach ($row as $image)
                            <td>
                                <div class="image-card">
                                    <img src="{{ $image['src'] }}" alt="{{ $image['name'] }}">
                                    <div class="image-name">{{ $image['name'] }}</div>
                                </div>
                            </td>
                        @endforeach
                        @if ($row->count() === 1)
                            <td></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @else
            <div class="content-box empty">Belum ada gambar yang dilampirkan.</div>
        @endif
    </section>

    <section class="section">
        <h2 class="section-heading"><span class="section-number">04</span>Link dokumen</h2>
        <div class="content-box">
            @if ($contentPlan->document_link)
                <a href="{{ $contentPlan->document_link }}">{{ $contentPlan->document_link }}</a>
            @else
                <span class="empty">Belum ada link dokumen.</span>
            @endif
        </div>
    </section>
</body>
</html>
