<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        /*
         * The PDF renderer supports a small, older subset of CSS: no flexbox,
         * no grid, and nothing fetched over the network. Layout is done with
         * tables, and DejaVu Sans is named explicitly because it is the bundled
         * face that carries the rupee sign.
         */
        @page {
            margin: 22mm 16mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #222;
            margin: 0;
        }

        .masthead {
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
            margin-bottom: 18px;
        }

        .masthead h1 {
            font-size: 15pt;
            margin: 0 0 2px;
        }

        .masthead .tagline {
            font-size: 8.5pt;
            color: #666;
        }

        .masthead .doc {
            font-size: 12pt;
            font-weight: bold;
            text-align: right;
        }

        .masthead .issued {
            font-size: 8.5pt;
            color: #666;
            text-align: right;
        }

        h2 {
            font-size: 11pt;
            margin: 18px 0 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th,
        table.data td {
            border: 1px solid #ccc;
            padding: 5px 7px;
            text-align: left;
        }

        table.data th {
            background: #f2f2f2;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        table.data tfoot th,
        table.data tfoot td {
            background: #f8f8f8;
            font-weight: bold;
        }

        table.plain td {
            padding: 2px 0;
            vertical-align: top;
        }

        .num {
            text-align: right;
        }

        .muted {
            color: #666;
        }

        .footnote {
            margin-top: 22px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #777;
        }
    </style>
</head>

<body>
    <table class="masthead">
        <tr>
            <td>
                <h1>Meals on Wheels</h1>
                <div class="tagline">Pet food, delivered from our warehouse</div>
            </td>
            <td>
                <div class="doc">@yield('title')</div>
                <div class="issued">Issued {{ now()->format('j F Y, H:i') }}</div>
            </td>
        </tr>
    </table>

    @yield('content')

    <div class="footnote">
        @yield('footnote', 'Figures are taken from the price and commission recorded at the time of each sale.')
    </div>
</body>

</html>
