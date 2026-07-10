<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1F2937;
            margin: 0;
            padding: 0;
            font-size: 11px;
        }

        .header {
            background-color: #0F2D52;
            color: #ffffff;
            padding: 24px 36px;
        }

        .header table {
            width: 100%;
        }

        .header .brand {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .header .tagline {
            font-size: 10px;
            color: #A9C0DA;
            margin-top: 2px;
        }

        .header .reference-badge {
            background-color: #F28C28;
            color: #ffffff;
            padding: 4px 12px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }

        .content {
            padding: 24px 36px;
        }

        .product-title {
            font-size: 22px;
            font-weight: bold;
            color: #0F2D52;
            margin-bottom: 2px;
        }

        .product-meta {
            font-size: 11px;
            color: #6B7280;
            margin-bottom: 16px;
        }

        .product-meta .availability {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: bold;
            color: #ffffff;
        }

        .availability.en_stock { background-color: #00A8A8; }
        .availability.sur_commande { background-color: #F28C28; }
        .availability.rupture { background-color: #9CA3AF; }

        .main-table {
            width: 100%;
        }

        .main-table td {
            vertical-align: top;
        }

        .image-cell {
            width: 220px;
            padding-right: 20px;
        }

        .image-cell img {
            width: 200px;
            border: 1px solid #E5E7EB;
            border-radius: 4px;
        }

        .description {
            font-size: 11px;
            line-height: 1.6;
            color: #374151;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #0F2D52;
            border-bottom: 2px solid #00A8A8;
            padding-bottom: 4px;
            margin-top: 20px;
            margin-bottom: 8px;
        }

        .carac-group-title {
            font-size: 11px;
            font-weight: bold;
            color: #F28C28;
            margin-top: 10px;
            margin-bottom: 4px;
        }

        table.carac-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        table.carac-table td {
            padding: 4px 8px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 10.5px;
        }

        table.carac-table td.label {
            color: #6B7280;
            width: 60%;
        }

        table.carac-table td.value {
            color: #1F2937;
            font-weight: bold;
            text-align: right;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #F8F9FA;
            border-top: 3px solid #00A8A8;
            padding: 12px 36px;
            font-size: 9.5px;
            color: #374151;
        }

        .footer table {
            width: 100%;
        }

        .footer .footer-brand {
            font-weight: bold;
            color: #0F2D52;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="brand">AMEG INTERNATIONAL</div>
                    <div class="tagline">Équipements de cuisine professionnelle</div>
                </td>
                <td style="text-align: right;">
                    <span class="reference-badge">Réf. {{ $product->reference }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        <div class="product-title">{{ $product->name }}</div>
        <div class="product-meta">
            @if($product->brand)
                Marque : {{ $product->brand->name }} &nbsp;|&nbsp;
            @endif
            {{ $product->category->name ?? '' }}
            @if($product->subcategory) &rsaquo; {{ $product->subcategory->name }} @endif
            &nbsp;|&nbsp;
            <span class="availability {{ $product->availability }}">
                @switch($product->availability)
                    @case('en_stock') En stock @break
                    @case('rupture') Rupture de stock @break
                    @default Sur commande
                @endswitch
            </span>
        </div>

        <table class="main-table">
            <tr>
                @if($imageDataUri)
                <td class="image-cell">
                    <img src="{{ $imageDataUri }}" alt="{{ $product->name }}">
                </td>
                @endif
                <td>
                    @if($product->description)
                        <div class="description">{{ $product->description }}</div>
                    @endif
                </td>
            </tr>
        </table>

        @if($groupedCharacteristics->isNotEmpty())
            <div class="section-title">Caractéristiques techniques</div>

            @foreach($groupedCharacteristics as $groupe => $caracteristiques)
                <div class="carac-group-title">{{ $groupe }}</div>
                <table class="carac-table">
                    @foreach($caracteristiques as $carac)
                        <tr>
                            <td class="label">{{ $carac->caracteristique }}</td>
                            <td class="value">{{ $carac->valeur }} {{ $carac->unite }}</td>
                        </tr>
                    @endforeach
                </table>
            @endforeach
        @endif
    </div>

    <div class="footer">
        <table>
            <tr>
                <td class="footer-brand">AMEG International</td>
                <td style="text-align: right;">
                    {{ $ameg['phone'] }} &nbsp;|&nbsp; {{ $ameg['email'] }} &nbsp;|&nbsp; {{ $ameg['website'] }}
                </td>
            </tr>
            <tr>
                <td colspan="2">{{ $ameg['address'] }}</td>
            </tr>
        </table>
    </div>

</body>
</html>
