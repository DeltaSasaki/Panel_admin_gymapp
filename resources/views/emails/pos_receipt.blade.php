<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Comprobante de Venta #{{ $sale->id }} - {{ $sale->gym->name ?? 'BigWorldFitness' }}</title>
    <style type="text/css">
        /* Client-specific resets */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }

        /* Responsive Mobile Styles */
        @media only screen and (max-width: 480px) {
            .email-container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 10px !important;
            }
            .receipt-box {
                padding: 14px 12px !important;
                border-radius: 12px !important;
            }
            .meta-text {
                font-size: 9.5px !important;
            }
            .item-title {
                font-size: 11px !important;
            }
            .item-price {
                font-size: 11px !important;
            }
            .total-title {
                font-size: 10px !important;
            }
            .total-amount-usd {
                font-size: 15px !important;
            }
            .total-amount-ves {
                font-size: 12px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #0f172a;">

    @php
        // Resolve real exchange rate (fallback if sale had default 1.00)
        $activeRate = ($sale->exchange_rate && (float)$sale->exchange_rate > 1.0001)
            ? (float)$sale->exchange_rate
            : (float)\App\Services\ExchangeRateService::getCurrentRate($sale->gym_id);

        if ($activeRate <= 1.0001) {
            $activeRate = (float)\App\Services\ExchangeRateService::getCurrentRate(null);
        }

        $totalVes = ($sale->total_amount_ves && (float)$sale->total_amount_ves > ((float)$sale->total_amount * 1.0001))
            ? (float)$sale->total_amount_ves
            : ((float)$sale->total_amount * $activeRate);

        $clientName = ($sale->user && $sale->user->profile) 
            ? trim(($sale->user->profile->first_name ?? '') . ' ' . ($sale->user->profile->last_name ?? '')) 
            : ($sale->user ? $sale->user->email : 'Cliente General');

        $clientDni = ($sale->user && $sale->user->profile && !empty($sale->user->profile->dni)) 
            ? $sale->user->profile->dni 
            : 'Sin DNI';

        $cashierName = ($sale->seller && $sale->seller->profile)
            ? trim(($sale->seller->profile->first_name ?? '') . ' ' . ($sale->seller->profile->last_name ?? ''))
            : ($sale->seller ? $sale->seller->email : 'Cajero');

        $paymentLabel = match($sale->payment_method) {
            'cash' => 'Efectivo',
            'card' => 'Tarjeta Débito / Crédito',
            'transfer' => 'Transferencia / Pago Móvil',
            default => ucfirst($sale->payment_method ?? 'Otro')
        };
    @endphp

    <!-- OUTER BACKGROUND WRAPPER -->
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 25px 8px;">
        <tr>
            <td align="center">
                
                <!-- MAIN TICKET CARD (Clean White Thermal Style matching Image 1) -->
                <table class="email-container" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 380px; margin: 0 auto;">
                    <tr>
                        <td class="receipt-box" style="background-color: #ffffff; border: 1.5px solid #0f172a; border-radius: 16px; padding: 20px 18px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);">
                            
                            <!-- 1. TICKET HEADER -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 12px;">
                                <tr>
                                    <td align="center">
                                        <h1 style="margin: 0; font-size: 16px; font-weight: 900; letter-spacing: 0.5px; text-transform: uppercase; color: #0f172a; line-height: 1.2;">
                                            {{ strtoupper($sale->gym->name ?? 'BIGWORLD FITNESS') }}
                                        </h1>
                                        <p style="margin: 3px 0 0 0; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #475569;">
                                            GIMNASIO & CENTRO DE ENTRENAMIENTO
                                        </p>
                                        <p style="margin: 2px 0 0 0; font-size: 8px; color: #64748b;">
                                            Comprobante No Fiscal | Sistema BigWorldFitness
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- 2. METADATA BOX -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; margin-bottom: 14px; font-size: 10px; line-height: 1.5;">
                                <tr>
                                    <td style="padding: 2px 0; color: #64748b; font-weight: 600;" class="meta-text">N° Ticket:</td>
                                    <td align="right" style="padding: 2px 0; font-weight: bold; color: #0f172a; font-family: monospace; font-size: 11px;" class="meta-text">#{{ $sale->id }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 2px 0; color: #64748b; font-weight: 600;" class="meta-text">Fecha y Hora:</td>
                                    <td align="right" style="padding: 2px 0; font-weight: bold; color: #0f172a;" class="meta-text">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 2px 0; color: #64748b; font-weight: 600;" class="meta-text">Cliente / Socio:</td>
                                    <td align="right" style="padding: 2px 0; font-weight: bold; color: #0f172a;" class="meta-text">{{ $clientName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 2px 0; color: #64748b; font-weight: 600;" class="meta-text">DNI / Cédula:</td>
                                    <td align="right" style="padding: 2px 0; font-weight: bold; color: #0f172a; font-family: monospace;" class="meta-text">{{ $clientDni }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 2px 0; color: #64748b; font-weight: 600;" class="meta-text">Atendido por:</td>
                                    <td align="right" style="padding: 2px 0; font-weight: bold; color: #0f172a;" class="meta-text">{{ $cashierName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 2px 0; color: #64748b; font-weight: 600;" class="meta-text">Forma de Pago:</td>
                                    <td align="right" style="padding: 2px 0; font-weight: bold; color: #0f172a;" class="meta-text">{{ $paymentLabel }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0 2px 0; color: #64748b; font-weight: 600; border-top: 1px dashed #cbd5e1;" class="meta-text">Factor Cambiario:</td>
                                    <td align="right" style="padding: 4px 0 2px 0; font-weight: bold; color: #16a34a; font-family: monospace; border-top: 1px dashed #cbd5e1;" class="meta-text">
                                        1 USD = Bs. {{ number_format($activeRate, 2, ',', '.') }}
                                    </td>
                                </tr>
                            </table>

                            <!-- 3. ITEMS TABLE -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 12px; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 1.5px solid #0f172a; text-transform: uppercase; font-size: 9px; color: #475569;">
                                        <th align="left" style="padding-bottom: 5px; font-weight: 800;">ÍTEM / CANT.</th>
                                        <th align="right" style="padding-bottom: 5px; font-weight: 800;">TOTAL ($ / BS.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->items as $item)
                                    @php
                                        $itemSubtotal = (float)$item->subtotal;
                                        $itemSubtotalVes = (float)($item->subtotal_ves && (float)$item->subtotal_ves > ($itemSubtotal * 1.0001) ? $item->subtotal_ves : ($itemSubtotal * $activeRate));
                                        $unitPrice = (float)$item->unit_price;
                                    @endphp
                                    <tr style="border-bottom: 1px dashed #e2e8f0;">
                                        <td style="padding: 7px 0; vertical-align: top;">
                                            <strong style="font-size: 11px; color: #0f172a; display: block; line-height: 1.25;" class="item-title">{{ $item->product->name ?? 'Producto' }}</strong>
                                            <span style="font-size: 9px; color: #64748b;">{{ $item->quantity }} unidad(es) x ${{ number_format($unitPrice, 2) }}</span>
                                        </td>
                                        <td align="right" style="padding: 7px 0; vertical-align: top;" class="item-price">
                                            <div style="font-weight: 800; font-size: 11px; color: #0f172a; font-family: monospace;">${{ number_format($itemSubtotal, 2) }}</div>
                                            <div style="font-size: 9px; color: #16a34a; font-weight: 700; font-family: monospace; margin-top: 1px;">Bs. {{ number_format($itemSubtotalVes, 2, ',', '.') }}</div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- 4. TOTAL BANNER (High-contrast Dark Box with Lime amount) -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #0f172a; border-radius: 8px; padding: 10px 12px; margin-top: 6px;">
                                <tr>
                                    <td style="vertical-align: middle;">
                                        <span style="font-size: 11px; text-transform: uppercase; font-weight: 900; letter-spacing: 0.5px; color: #ffffff; display: block;" class="total-title">
                                            TOTAL COBRADO
                                        </span>
                                        <span style="font-size: 8.5px; color: #94a3b8; font-weight: normal; margin-top: 1px; display: block;">
                                            Equivalente en Bolívares
                                        </span>
                                    </td>
                                    <td align="right" style="vertical-align: middle;">
                                        <div style="font-size: 16px; font-weight: 900; font-family: monospace; color: #a3e635;" class="total-amount-usd">
                                            ${{ number_format($sale->total_amount, 2) }}
                                        </div>
                                        <div style="font-size: 12px; font-weight: 800; font-family: monospace; color: #ffffff; margin-top: 1px;" class="total-amount-ves">
                                            Bs. {{ number_format($totalVes, 2, ',', '.') }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- 5. TICKET FOOTER -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="text-align: center; margin-top: 14px; padding-top: 10px; border-top: 1px dashed #cbd5e1;">
                                <tr>
                                    <td align="center">
                                        <p style="margin: 0; font-weight: 700; color: #334155; font-size: 9px;">
                                            ¡Gracias por entrenar en BigWorldFitness!
                                        </p>
                                        <p style="margin: 2px 0 0 0; color: #94a3b8; font-size: 8px;">
                                            Conserva este ticket para cualquier consulta o reclamo.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
