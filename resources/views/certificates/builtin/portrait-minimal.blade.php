<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 portrait; margin: 0; }
        body { margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; }

        .frame {
            width: 210mm;
            height: 297mm;
            box-sizing: border-box;
            padding: 24mm 22mm;
            text-align: center;
        }

        .badge {
            width: 16mm; height: 16mm;
            border: 1pt solid #1F2547;
            border-radius: 50%;
            margin: 0 auto 10mm auto;
            line-height: 16mm;
            font-family: 'DejaVu Serif', serif;
            font-size: 13pt;
            color: #1F2547;
        }

        .eyebrow {
            font-size: 9pt;
            letter-spacing: 3.5pt;
            text-transform: uppercase;
            color: #B8863B;
            margin-bottom: 4mm;
        }
        .business {
            font-family: 'DejaVu Serif', serif;
            font-size: 15pt;
            color: #1F2547;
            margin-bottom: 20mm;
        }

        .certifies {
            font-size: 9.5pt;
            letter-spacing: 1pt;
            text-transform: uppercase;
            color: #7C8598;
            margin-bottom: 6mm;
        }
        .recipient {
            font-family: 'DejaVu Serif', serif;
            font-size: 27pt;
            color: #1B1F2A;
            margin-bottom: 3mm;
        }
        .rule {
            width: 40mm;
            height: 0;
            border-top: 1pt solid #B8863B;
            margin: 0 auto 12mm auto;
        }

        .desc {
            font-size: 10.5pt;
            color: #1B1F2A;
            line-height: 1.8;
            width: 130mm;
            margin: 0 auto 12mm auto;
        }
        .desc b { color: #1F2547; }

        .dates {
            font-size: 9pt;
            color: #7C8598;
            margin-bottom: 30mm;
        }

        table.footer-table { width: 130mm; margin: 0 auto; }
        .footer-table td { vertical-align: bottom; }
        .qr-cell { width: 20mm; text-align: left; }
        .qr-cell img { width: 18mm; height: 18mm; }
        .certno-cell {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 7.5pt;
            color: #7C8598;
        }
    </style>
</head>
<body>
<div class="frame">
    <div class="badge">{{ strtoupper(substr($certificate->business?->business_name ?? 'H', 0, 1)) }}</div>
    <div class="eyebrow">Certificate of Completion</div>
    <div class="business">{{ $certificate->business?->business_name ?? 'HandSeal' }}</div>

    <div class="certifies">This certifies that</div>
    <div class="recipient">{{ $certificate->recipient_name ?? '—' }}</div>
    <div class="rule"></div>

    <div class="desc">
        has successfully completed the <b>{{ $certificate->programme?->name ?? 'programme' }}</b> programme,
        fulfilling all requirements set out by {{ $certificate->business?->business_name ?? 'the issuing business' }}.
    </div>

    <div class="dates">
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
</body>
</html>
