<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 landscape; margin: 0; }
        body { margin: 0; padding: 0; font-family: 'DejaVu Serif', serif; }

        .frame { width: 297mm; height: 210mm; box-sizing: border-box; }
        table.layout { width: 100%; height: 210mm; border-collapse: collapse; }
        table.layout td { vertical-align: top; }

        .sidebar {
            width: 85mm;
            background-color: #1F2547;
            color: #FFFFFF;
            padding: 16mm 12mm;
            box-sizing: border-box;
            height: 210mm;
        }
        .sidebar .mark {
            width: 14mm; height: 14mm;
            border: 1.5pt solid #B8863B;
            border-radius: 50%;
            text-align: center;
            line-height: 14mm;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            color: #B8863B;
            margin-bottom: 10mm;
        }
        .sidebar .eyebrow {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            letter-spacing: 2.5pt;
            text-transform: uppercase;
            color: #B8863B;
            margin-bottom: 4mm;
        }
        .sidebar .business {
            font-size: 16pt;
            line-height: 1.35;
            margin-bottom: 8mm;
        }
        .sidebar .meta {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            color: #A9AFC4;
            line-height: 1.7;
        }
        .sidebar .qr { margin-top: 14mm; }
        .sidebar .qr img { width: 20mm; height: 20mm; }

        .main {
            padding: 22mm 20mm;
            box-sizing: border-box;
            text-align: left;
        }
        .main .certifies {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            letter-spacing: 1pt;
            text-transform: uppercase;
            color: #7C8598;
            margin-bottom: 6mm;
        }
        .main .recipient {
            font-size: 32pt;
            color: #1B1F2A;
            border-bottom: 1pt solid #B8863B;
            padding-bottom: 6mm;
            margin-bottom: 8mm;
        }
        .main .desc {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11.5pt;
            color: #1B1F2A;
            line-height: 1.7;
        }
        .main .desc b { color: #1F2547; }
        .main .dates {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5pt;
            color: #7C8598;
            margin-top: 6mm;
        }
    </style>
</head>
<body>
<div class="frame">
    <table class="layout">
        <tr>
            <td class="sidebar">
                <div class="mark">{{ strtoupper(substr($certificate->business?->business_name ?? 'H', 0, 1)) }}</div>
                <div class="eyebrow">Certificate</div>
                <div class="business">{{ $certificate->business?->business_name ?? 'HandSeal' }}</div>
                <div class="meta">
                    Certificate No.<br>
                    {{ $certificate->certificate_number ?? '—' }}<br><br>
                    Verify at<br>
                    {{ config('app.url') }}/{{ config('handseal.verify_path') }}
                </div>
                @if ($certificate->qrBase64())
                    <div class="qr"><img src="{{ $certificate->qrBase64() }}"></div>
                @endif
            </td>
            <td class="main">
                <div class="certifies">This certifies that</div>
                <div class="recipient">{{ $certificate->recipient_name ?? '—' }}</div>
                <div class="desc">
                    has successfully completed the<br>
                    <b>{{ $certificate->programme?->name ?? 'programme' }}</b> programme,
                    demonstrating consistent commitment and skill throughout the course of study.
                </div>
                <div class="dates">
                    {{ $certificate->start_date?->format('d M Y') ?? '—' }} &mdash; {{ $certificate->end_date?->format('d M Y') ?? '—' }}
                </div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
