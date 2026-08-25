<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 portrait; margin: 0; }
        body { margin: 0; padding: 0; font-family: 'DejaVu Serif', serif; }

        .frame {
            width: 210mm;
            height: 297mm;
            box-sizing: border-box;
            padding: 20mm 24mm;
        }

        .header-table { width: 100%; border-bottom: 2pt solid #1F2547; padding-bottom: 6mm; margin-bottom: 16mm; }
        .header-table td { vertical-align: bottom; }
        .header-business {
            font-size: 15pt;
            color: #1F2547;
        }
        .header-label {
            text-align: right;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            letter-spacing: 2pt;
            text-transform: uppercase;
            color: #B8863B;
        }

        .body { text-align: center; }
        .eyebrow {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5pt;
            letter-spacing: 1pt;
            text-transform: uppercase;
            color: #7C8598;
            margin-bottom: 6mm;
        }
        .recipient {
            font-size: 28pt;
            color: #1B1F2A;
            margin-bottom: 10mm;
        }
        .desc {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10.5pt;
            color: #1B1F2A;
            line-height: 1.8;
            width: 150mm;
            margin: 0 auto 40mm auto;
        }
        .desc b { color: #1F2547; }

        table.sign-table { width: 100%; margin-top: 4mm; }
        .sign-table td { width: 50%; text-align: center; vertical-align: top; }
        .sign-line {
            border-top: 0.75pt solid #7C8598;
            width: 60mm;
            margin: 0 auto 3mm auto;
        }
        .sign-label {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            color: #7C8598;
            text-transform: uppercase;
            letter-spacing: 1pt;
        }

        table.footer-table { width: 100%; margin-top: 16mm; border-top: 0.5pt solid #E4E7EE; padding-top: 4mm; }
        .footer-table td { vertical-align: middle; }
        .qr-cell { width: 20mm; }
        .qr-cell img { width: 16mm; height: 16mm; }
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
    <table class="header-table">
        <tr>
            <td class="header-business">{{ $certificate->business?->business_name ?? 'HandSeal' }}</td>
            <td class="header-label">Certificate of Completion</td>
        </tr>
    </table>

    <div class="body">
        <div class="eyebrow">This certifies that</div>
        <div class="recipient">{{ $certificate->recipient_name ?? '—' }}</div>

        <div class="desc">
            has successfully completed the <b>{{ $certificate->programme?->name ?? 'programme' }}</b> programme,
            covering the period {{ $certificate->start_date?->format('d M Y') ?? '—' }} to
            {{ $certificate->end_date?->format('d M Y') ?? '—' }}, and has met all requirements for completion
            as set out by {{ $certificate->business?->business_name ?? 'the issuing business' }}.
        </div>

        <table class="sign-table">
            <tr>
                <td>
                    <div class="sign-line"></div>
                    <div class="sign-label">Authorized Signature</div>
                </td>
                <td>
                    <div class="sign-line"></div>
                    <div class="sign-label">Date Issued</div>
                </td>
            </tr>
        </table>
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
