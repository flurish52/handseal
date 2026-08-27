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
            padding: 10mm;
        }
        .outer-border {
            border: 2pt solid #1F2547;
            height: 100%;
            box-sizing: border-box;
            padding: 3mm;
        }
        .inner {
            border: 1pt solid #B8863B;
            height: 100%;
            box-sizing: border-box;
            padding: 10mm 16mm;
        }

        /* Full-height layout table keeps everything vertically balanced
           and stops content drifting or overflowing the frame */
        .layout { width: 100%; height: 100%; border-collapse: collapse; }
        .layout td { padding: 0; }
        .row-top    { height: 22mm; text-align: center; vertical-align: top; }
        .row-mid    { text-align: center; vertical-align: middle; }
        .row-footer { height: 20mm; vertical-align: bottom; }

        .eyebrow {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5pt;
            letter-spacing: 3pt;
            text-transform: uppercase;
            color: #B8863B;
            margin-bottom: 5mm;
        }
        .business {
            font-size: 17pt;
            font-weight: bold;
            color: #1F2547;
        }
        .divider {
            width: 16mm;
            border-top: 1pt solid #B8863B;
            margin: 5mm auto;
        }
        .certifies {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5pt;
            letter-spacing: 1pt;
            text-transform: uppercase;
            color: #7C8598;
            margin-bottom: 4mm;
        }
        .recipient {
            font-size: 27pt;
            font-weight: bold;
            color: #1B1F2A;
            line-height: 1.2;
            margin-bottom: 6mm;
            word-wrap: break-word;
        }
        .desc {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10.5pt;
            color: #1B1F2A;
            line-height: 1.7;
        }
        .desc b { color: #1F2547; }
        .dates {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5pt;
            color: #7C8598;
            margin-top: 2mm;
        }

        /* Footer: verification block reads as a designed credential strip,
           not a raw debug line */
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-table td { vertical-align: bottom; }
        .footer-rule {
            border-top: 0.5pt solid #D8D2C4;
            padding-top: 4mm;
        }
        .qr-cell { width: 22mm; text-align: left; }
        .qr-cell img { width: 20mm; height: 20mm; }

        .verify-cell {
            text-align: center;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            color: #7C8598;
            letter-spacing: 0.5pt;
        }
        .verify-cell .url {
            color: #1F2547;
            font-weight: bold;
        }

        .certno-cell {
            width: 55mm;
            text-align: right;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            color: #7C8598;
        }
        .certno-cell .no {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9pt;
            color: #1F2547;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="frame">
    <div class="outer-border">
        <div class="inner">
            <table class="layout">
                <tr class="row-top">
                    <td>
                        <div class="eyebrow">Certificate of Completion</div>
                        <div class="business">{{ $certificate->business?->business_name ?? 'HandSeal' }}</div>
                    </td>
                </tr>
                <tr class="row-mid">
                    <td>
                        <div class="divider"></div>
                        <div class="certifies">This certifies that</div>
                        <div class="recipient">{{ $certificate->recipient_name ?? '—' }}</div>
                        <div class="desc">
                            has successfully completed the<br>
                            <b>{{ $certificate->programme?->name ?? 'programme' }}</b>
                            <div class="dates">
                                {{ $certificate->start_date?->format('d M Y') ?? '—' }} &nbsp;&mdash;&nbsp; {{ $certificate->end_date?->format('d M Y') ?? '—' }}
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="row-footer">
                    <td>
                        <table class="footer-table">
                            <tr>
                                <td class="qr-cell footer-rule">
                                    @if ($certificate->qrBase64())
                                        <img src="{{ $certificate->qrBase64() }}">
                                    @endif
                                </td>
                                <td class="verify-cell footer-rule">
                                    Scan the code or visit<br>
                                    <span class="url">{{ preg_replace('#^https?://#', '', config('app.url')) }}/{{ config('handseal.verify_path') }}</span> to verify
                                </td>
                                <td class="certno-cell footer-rule">
                                    CERTIFICATE NO.<br>
                                    <span class="no">{{ $certificate->certificate_number ?? '—' }}</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
</body>
</html>
