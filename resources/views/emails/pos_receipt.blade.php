<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Venta POS #{{ $sale->id }} - BigWorldFitness</title>
</head>
<body style="margin: 0; padding: 0; background-color: #090d16; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #f8fafc;">

    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #090d16; padding: 30px 10px;">
        <tr>
            <td align="center">
                
                <!-- CONTAINER CARD -->
                <table width="100%" max-width="500" border="0" cellspacing="0" cellpadding="0" style="max-width: 500px; background-color: #0f172a; border: 1px solid #1e293b; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
                    
                    <!-- HEADER BANNER -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 30px 20px 20px 20px; border-bottom: 2px solid #84cc16;">
                            <h1 style="margin: 0; font-size: 22px; font-weight: 900; color: #f8fafc; letter-spacing: 1px; text-transform: uppercase;">{{ strtoupper($sale->gym->name ?? 'BIGWORLD FITNESS') }}</h1>
                            <p style="margin: 4px 0 0 0; font-size: 10px; font-weight: 700; color: #84cc16; text-transform: uppercase; letter-spacing: 1px;">Gimnasio & Centro de Entrenamiento | Sistema BigWorldFitness</p>
                        </td>
                    </tr>

                    <!-- CONTENT AREA -->
                    <tr>
                        <td style="padding: 25px 24px;">
                            
                            <h2 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 800; color: #f8fafc; text-align: center;">¡Gracias por tu compra!</h2>
                            <p style="margin: 0 0 20px 0; font-size: 12px; color: #94a3b8; text-align: center; line-height: 1.5;">Adjunto encontrarás el resumen detallado de tu transacción realizada en el Punto de Venta POS.</p>

                            <!-- METADATA BOX -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #1e293b; border-radius: 12px; padding: 14px; margin-bottom: 20px; font-size: 12px;">
                                <tr>
                                    <td style="padding: 3px 0; color: #94a3b8;">Gimnasio / Sucursal:</td>
                                    <td align="right" style="padding: 3px 0; font-weight: bold; color: #f8fafc;">{{ $sale->gym->name ?? 'BigWorldFitness' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 0; color: #94a3b8;">N° Comprobante:</td>
                                    <td align="right" style="padding: 3px 0; font-weight: bold; color: #84cc16; font-family: monospace;">#{{ $sale->id }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 0; color: #94a3b8;">Fecha y Hora:</td>
                                    <td align="right" style="padding: 3px 0; font-weight: bold; color: #f8fafc;">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 0; color: #94a3b8;">Cliente / Socio:</td>
                                    <td align="right" style="padding: 3px 0; font-weight: bold; color: #f8fafc;">
                                        {{ $sale->user && $sale->user->profile ? trim($sale->user->profile->first_name . ' ' . $sale->user->profile->last_name) : ($sale->user ? $sale->user->email : 'Cliente General') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 0; color: #94a3b8;">DNI / Cédula:</td>
                                    <td align="right" style="padding: 3px 0; font-weight: bold; color: #84cc16; font-family: monospace;">
                                        {{ $sale->user && $sale->user->profile && !empty($sale->user->profile->dni) ? $sale->user->profile->dni : 'Sin DNI' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 0; color: #94a3b8;">Atendido Por (Cajero):</td>
                                    <td align="right" style="padding: 3px 0; font-weight: bold; color: #f8fafc;">
                                        {{ $sale->seller && $sale->seller->profile ? trim($sale->seller->profile->first_name . ' ' . $sale->seller->profile->last_name) : ($sale->seller ? $sale->seller->email : 'Cajero') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 0; color: #94a3b8;">Método de Pago:</td>
                                    <td align="right" style="padding: 3px 0; font-weight: bold; color: #f8fafc;">
                                        @switch($sale->payment_method)
                                            @case('cash') Efectivo @break
                                            @case('card') Tarjeta Débito / Crédito @break
                                            @case('transfer') Transferencia / Pago Móvil @break
                                            @default Otro Método
                                        @endswitch
                                    </td>
                                </tr>
                            </table>

                            <!-- ITEMS TABLE -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px; font-size: 12px; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 2px solid #334155;">
                                        <th align="left" style="padding: 8px 0; color: #94a3b8; font-size: 10px; text-transform: uppercase;">Producto</th>
                                        <th align="center" style="padding: 8px 0; color: #94a3b8; font-size: 10px; text-transform: uppercase;">Cant.</th>
                                        <th align="right" style="padding: 8px 0; color: #94a3b8; font-size: 10px; text-transform: uppercase;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->items as $item)
                                    <tr style="border-bottom: 1px solid #1e293b;">
                                        <td style="padding: 10px 0; color: #f8fafc; font-weight: 600;">
                                            {{ $item->product->name ?? 'Producto' }}
                                            <div style="font-size: 10px; color: #64748b;">${{ number_format($item->unit_price, 2) }} c/u</div>
                                        </td>
                                        <td align="center" style="padding: 10px 0; color: #cbd5e1; font-weight: bold;">{{ $item->quantity }}</td>
                                        <td align="right" style="padding: 10px 0; color: #f8fafc; font-weight: bold;">${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- TOTAL BANNER -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #1e293b; border: 1px solid #84cc16; border-radius: 12px; padding: 14px; margin-bottom: 20px;">
                                <tr>
                                    <td style="font-size: 12px; font-weight: 800; color: #f8fafc; text-transform: uppercase;">TOTAL PAGADO</td>
                                    <td align="right" style="font-size: 20px; font-weight: 900; color: #84cc16; font-family: monospace;">${{ number_format($sale->total_amount, 2) }}</td>
                                </tr>
                            </table>

                            <!-- FOOTER NOTE -->
                            <p style="margin: 0; font-size: 11px; color: #64748b; text-align: center; line-height: 1.4;">
                                Conserva este correo como comprobante digital de tu compra.<br>
                                Si tienes alguna inquietud, acércate a la recepción de tu sucursal.
                            </p>
                        </td>
                    </tr>

                    <!-- FOOTER COPYRIGHT -->
                    <tr>
                        <td align="center" style="background-color: #090d16; padding: 15px; border-top: 1px solid #1e293b; font-size: 10px; color: #64748b;">
                            © {{ date('Y') }} BigWorldFitness. Todos los derechos reservados.
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
