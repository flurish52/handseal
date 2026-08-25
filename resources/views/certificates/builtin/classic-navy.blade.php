<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* DomPDF-safe: table layout instead of flex/grid, inline-compatible fonts only */
        @page { size: A4 landscape; margin: 0; }
        body { margin: 0; padding: 0; font-family: 'DejaVu Serif', serif; }

        .frame {
            width: 297mm;
            height: 210mm;
            box-sizing: border-box;
            padding: 14mm;
            border: 3pt solid #1F2547;
        }
        .inner {
            border: 1pt solid #B8863B;
            padding: 12mm;
            height: 100%;
            box-sizing: border-box;
            text-align: center;
        }
        .eyebrow {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            letter-spacing: 3pt;
            text-transform: uppercase;
            color: #B8863B;
            margin-bottom: 6mm;
        }
        .business {
            font-size: 20pt;
            color: #1F2547;
            margin-bottom: 10mm;
        }
        .certifies {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #7C8598;
            margin-bottom: 4mm;
        }
        .recipient {
            font-size: 30pt;
            color: #1B1F2A;
            margin-bottom: 8mm;
        }
        .desc {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #1B1F2A;
            line-height: 1.6;
            margin-bottom: 12mm;
        }
        .desc b { color: #1F2547; }

        .footer-table { width: 100%; margin-top: 10mm; }
        .footer-table td { vertical-align: bottom; }
        .qr-cell { width: 25mm; text-align: left; }
        .qr-cell img { width: 22mm; height: 22mm; }
        .certno-cell {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 8pt;
            color: #7C8598;
        }
    </style>
</head>
<body>
<div class="frame">
    <div class="inner">
        <div class="eyebrow">Certificate of Completion</div>
        <div class="business">{{ $certificate->business?->business_name ?? 'HandSeal' }}</div>

        <div class="certifies">This certifies that</div>
        <div class="recipient">{{ $certificate->recipient_name ?? '—' }}</div>

        <div class="desc">
            has successfully completed the<br>
            <b>{{ $certificate->programme?->name ?? 'programme' }}</b><br>
            {{ $certificate->start_date?->format('d M Y') ?? '—' }} &mdash; {{ $certificate->end_date?->format('d M Y') ?? '—' }}
        </div>

        <table class="footer-table">
            <tr>
                <td class="qr-cell">
                    @if ($certificate->qrBase64())
                        <img src="{{ $certificate->qrBase64() }}">
                    @endif
                </td>
                <td class="certno-cell">
                    {{ $certificate->certificate_number ?? '—' }}<br>
                    verify at {{ config('app.url') }}/{{ config('handseal.verify_path') }}
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
