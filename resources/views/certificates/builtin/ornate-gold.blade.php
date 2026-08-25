<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 landscape; margin: 0; }
        body { margin: 0; padding: 0; font-family: 'DejaVu Serif', serif; }

        .frame {
            width: 297mm;
            height: 210mm;
            box-sizing: border-box;
            padding: 10mm;
            border: 4pt double #1F2547;
        }
        .inner {
            border: 1pt solid #B8863B;
            padding: 14mm;
            height: 100%;
            box-sizing: border-box;
            text-align: center;
        }

        .top-ornament {
            font-size: 10pt;
            letter-spacing: 4pt;
            color: #B8863B;
            margin-bottom: 6mm;
        }
        .eyebrow {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            letter-spacing: 3pt;
            text-transform: uppercase;
            color: #1F2547;
            margin-bottom: 3mm;
        }
        .business {
            font-size: 19pt;
            color: #1F2547;
            margin-bottom: 10mm;
        }

        .certifies {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5pt;
            color: #7C8598;
            margin-bottom: 4mm;
        }
        .recipient {
            font-size: 29pt;
            color: #1B1F2A;
            margin-bottom: 4mm;
        }
        .divider { font-size: 9pt; color: #B8863B; margin-bottom: 8mm; }

        .desc {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10.5pt;
            color: #1B1F2A;
            line-height: 1.7;
            width: 190mm;
            margin: 0 auto 10mm auto;
        }
        .desc b { color: #1F2547; }

        table.footer-table { width: 100%; margin-top: 8mm; }
        .footer-table td { vertical-align: bottom; }
        .qr-cell { width: 25mm; text-align: left; }
        .qr-cell img { width: 20mm; height: 20mm; }
        .seal-cell { text-align: center; }
        .seal {
            width: 22mm; height: 22mm;
            border: 1.5pt solid #B8863B;
            border-radius: 50%;
            display: inline-block;
            line-height: 22mm;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 7pt;
            letter-spacing: 1pt;
            color: #B8863B;
            text-transform: uppercase;
        }
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
        <div class="top-ornament">&bull; &bull; &bull;</div>
        <div class="eyebrow">Certificate of Achievement</div>
        <div class="business">{{ $certificate->business?->business_name ?? 'HandSeal' }}</div>

        <div class="certifies">This is to certify that</div>
        <div class="recipient">{{ $certificate->recipient_name ?? '—' }}</div>
        <div class="divider">&bull; &bull; &bull;</div>

        <div class="desc">
            has diligently completed the requirements of the
            <b>{{ $certificate->programme?->name ?? 'programme' }}</b> programme, from
            {{ $certificate->start_date?->format('d M Y') ?? '—' }} to {{ $certificate->end_date?->format('d M Y') ?? '—' }},
            and is hereby awarded this certificate in recognition of that achievement.
        </div>

        <table class="footer-table">
            <tr>
                <td class="qr-cell">
                    @if ($certificate->qrBase64())
                        <img src="{{ $certificate->qrBase64() }}">
                    @endif
                </td>
                <td class="seal-cell">
                    <div class="seal">Certified</div>
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
