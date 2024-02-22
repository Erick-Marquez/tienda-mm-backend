@php
    $path_style = resource_path('views'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte</title>
    <link href="{{ $path_style }}" rel="stylesheet" />
    <style>
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @foreach ($sales as $sale)
        <header class="header">
            <div class="logo">
                <img src="images/logo.jpg" alt="este es el logo del negocio">
            </div>
            <div class="titular">
                <h1>MamaMia</h1>
                <p class="titular__distrito">Jr. Capitán Quiñonez 300</p>
                <p class="titular__ciudad">Lima - Lima - San Martin de Porres</p>
                <div class="titular__elemento">
                    {{-- <img src="img/phone-call.svg" alt="telefono"> --}}
                    <p class="telefono__numero"> Tel: 906 443 937</p>
                </div>
                <a href="#" class="correo titular__elemento">
                    {{-- <img src="img/email.svg" alt="correo"> --}}
                    <p class="correo__dir"></p>
                </a>
            </div>
            <div class="ruc">
                <p class="ruc__primero">R.U.C. 10157516286</p>
                <p class="ruc__segundo">{{ $sale->serie->voucherType->description }}</p>
                <p class="ruc__numero">{{ $sale->serie->serie }} - {{ str_pad($sale->document_number, 4, '0', STR_PAD_LEFT) }}</p>
            </div>
        </header>
        <main>
            <div class="cliente">
                <p>Cliente<span class="cliente__puntos">:</span>{{ $sale->customer->name }}</p>
                <p>Num. Doc.<span class="cliente__puntos">:</span>{{ $sale->customer->document }}</p>
                {{--<p>Direccion<span class="cliente__puntos">:</span>{{ $sale->customer->address }}</p>--}}
            </div>
            <table class="descripcion">
                <thead>
                    <tr>
                        <td>FECHA DE EMISIÓN</td>
                        <td>CONDICIÓN DE PAGO</td>
                        <td>TIPO DE MONEDA</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $sale->created_at->toDateString() }}</td>
                        <td>EFECTIVO</td>
                        <td>Soles</td>
                    </tr>
                </tbody>
            </table>
            <table class="pagar" >
                <thead>
                    <tr>
                        <td style="width: 35px">CANT.</td>
                        <td >DESCRIPCIÓN</td>
                        <td style="width: 90px">AFECT.IGV</td>
                        <td style="width: 90px">PRECIO</td>
                        <td style="width: 90px">IMPORTE</td>
                    </tr>
                </thead>
                <tbody class="detalle"> 
                    @foreach ($sale->saleDetails as $detail)
                        <tr>
                            <td>{{ $detail->quantity }}</td>
                            <td>{{ $detail->product->name  }}</td>
                            <td>Gravado</td>
                            <td>S/. {{ number_format(round($detail->price, 2), 2, '.', '') }}</td>
                            <td>S/. {{ number_format(round($detail->total, 2), 2, '.', '') }}</td>
                        </tr>
                    @endforeach
                    <tr class="tfood">
                        <td colspan="5">SON {{ \App\Services\Facturacion\Helpers\Number\NumberLetter::convertToLetter(round($sale->total, 2), 'SOLES') }}</td>
                    </tr>
                </tbody>
            </table>
            <table class="transaccion">
                <tr>
                    <td>
                        <div class="escaner">
                            <div class="escaner__img">
                                {{-- <img src="data:image/png;base64, {{ $qr }}"/> --}}
                            </div>
                        </div>  
                    </td>
                    <td>
                        <div class="compra__texto">
                            <p class="compra__vendedor"></p>
                        </div>
                        <div class="compra__texto">
                            <p class="compra__vendedor"></p>
                        </div>
                    </td>
                    <td>
                        <div class="resumen">
                            <p>RESUMEN:</p>
                            @if ($sale->subtotal > 0)
                                <div class="resumen__elemento">
                                    <p>Subtotal:</p>
                                    <p class="descuento__precio">S/. {{ number_format(round($sale->subtotal, 2), 2, '.', '') }}</p>
                                </div>
                            @endif
                            @if ($sale->total_taxed > 0)
                                {{--<div class="resumen__elemento">
                                    <p>Gravado:</p>
                                    <p class="descuento__precio">S/. {{ number_format(round($sale->total_taxed, 2), 2, '.', '') }}</p>
                                </div>--}}
                            @endif
                            @if ($sale->total_exonerated > 0)
                                <div class="resumen__elemento">
                                    <p>Exonerado:</p>
                                    <p class="descuento__precio">S/. {{ round($sale->total_exonerated, 2) }}</p>
                                </div>
                            @endif
                            @if ($sale->total_unaffected > 0)
                                <div class="resumen__elemento">
                                    <p>Inafecto:</p>
                                    <p class="descuento__precio">S/. {{ round($sale->total_unaffected, 2) }}</p>
                                </div>
                            @endif
                            @if ($sale->total_free > 0)
                                <div class="resumen__elemento">
                                    <p>Gratuita:</p>
                                    <p class="descuento__precio">S/. {{ round($sale->total_free, 2) }}</p>
                                </div>
                            @endif
                            @if ($sale->total_igv > 0)
                                <div class="resumen__elemento">
                                    <p>Igv (18%):</p>
                                    <p class="descuento__precio">S/. {{ number_format(round($sale->total_igv, 2), 2, '.', '') }}</p>
                                </div>
                            @endif
                            <div class="resumen__elemento">
                                <p>Total:</p>
                                <p class="total__precio">S/ {{ number_format(round($sale->total, 2), 2, '.', '') }}</p>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="observacion">
                    <td colspan="3">
                        {{-- <p><span>Hash: </span>{{ $head->hash_cpe }}</p> --}}
                    </td>
                </tr>
                @if ($sale->observation)
                    <tr class="observacion">
                        <td colspan="3">
                            <p><span>Observación: </span>{{ $sale->observation }}</p>
                        </td>
                    </tr>
                @endif
            </table>
        </main>

        @if ($loop->last) @else
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>