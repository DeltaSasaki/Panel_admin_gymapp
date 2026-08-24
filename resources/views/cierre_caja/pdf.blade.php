<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cierre de Caja - {{ $periodLabel }}</title>
    <style>
        @page {
            margin: 12mm 12mm 15mm 12mm;
            @bottom-right {
                content: "Página {PAGENO} de {nbpg}";
                font-family: 'Helvetica', 'Arial', sans-serif;
                font-size: 8pt;
                color: #64748b;
            }
            @bottom-left {
                content: "Documento Oficial de Auditoría Contable - Confidencial";
                font-family: 'Helvetica', 'Arial', sans-serif;
                font-size: 8pt;
                color: #94a3b8;
            }
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Header Table Layout */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-title {
            font-size: 16pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .doc-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2563eb;
            margin-top: 4px;
            margin-bottom: 0;
            text-transform: uppercase;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 8.5pt;
        }
        .meta-box td {
            padding: 2px 4px;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
        }

        /* Status Badge */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 8pt;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        /* Section Headings */
        .section-header {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            background-color: #f1f5f9;
            padding: 6px 10px;
            border-left: 4px solid #2563eb;
            margin-top: 15px;
            margin-bottom: 10px;
        }

        /* Summary Metric Cards Table */
        .metrics-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .metrics-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px;
            text-align: center;
        }
        .metrics-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }
        .metrics-value {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 4px;
        }

        /* Data Tables Styling */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8.5pt;
        }
        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #0f172a;
        }
        .data-table th.text-center { text-align: center; }
        .data-table th.text-right { text-align: right; }

        .data-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            border-left: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .data-table td.text-center { text-align: center; }
        .data-table td.text-right { text-align: right; }
        
        .total-row td {
            font-weight: bold;
            background-color: #e2e8f0 !important;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            font-size: 9pt;
        }

        /* Notes Box */
        .notes-box {
            background-color: #fffbebf;
            border: 1px dashed #f59e0b;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 15px;
            font-size: 8.5pt;
        }

        /* Signatures Layout */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 35px;
            page-break-inside: avoid;
        }
        .signature-cell {
            width: 45%;
            text-align: center;
            vertical-align: bottom;
        }
        .signature-line {
            border-top: 1.5px solid #334155;
            margin-top: 45px;
            margin-left: 15%;
            margin-right: 15%;
        }
        .signature-name {
            font-weight: bold;
            font-size: 9pt;
            color: #0f172a;
            margin-top: 5px;
        }
        .signature-role {
            font-size: 8pt;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- Header Block -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <h1 class="company-title">{{ $gym ? $gym->name : 'VISTA GLOBAL - TODAS LAS SUCURSALES' }}</h1>
                <h2 class="doc-title">Cierre de Caja y Auditoría Financiera</h2>
                <div style="font-size: 8.5pt; color: #64748b; margin-top: 4px;">
                    Reporte Contable Oficial de Movimientos e Ingresos
                </div>
            </td>
            <td style="width: 40%;">
                <table class="meta-box" style="width: 100%;">
                    <tr>
                        <td class="meta-label">Período:</td>
                        <td style="text-align: right; font-weight: bold; color: #2563eb;">{{ $periodLabel }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Estado de Caja:</td>
                        <td style="text-align: right;">
                            @if($isClosed)
                                <span class="badge badge-success">CERRADA</span>
                            @else
                                <span class="badge badge-warning">ABIERTA</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="meta-label">Emisión:</td>
                        <td style="text-align: right;">{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Factor Cambiario:</td>
                        <td style="text-align: right; font-weight: bold; color: #15803d;">1 USD = Bs. {{ number_format($dollarRate ?? 1, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Generado por:</td>
                        <td style="text-align: right;">{{ auth()->user()->name ?? 'Administrador' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Financial Summary Cards (mPDF Clean Layout without line artifacts) -->
    <table style="width: 100%; border-collapse: separate; border-spacing: 5px; margin-bottom: 15px;">
        <tr>
            <td style="width: 25%; background-color: #f8fafc; border: 1px solid #cbd5e1; border-top: 3px solid #2563eb; border-radius: 6px; padding: 8px 4px; text-align: center; vertical-align: middle;">
                <div style="font-size: 7.5pt; font-weight: bold; color: #475569; text-transform: uppercase;">TOTAL INGRESOS</div>
                <div style="font-size: 13pt; font-weight: bold; color: #2563eb; margin-top: 2px;">${{ number_format($grandTotal, 2) }}</div>
                <div style="font-size: 8pt; font-weight: bold; color: #16a34a; margin-top: 2px;">Bs. {{ number_format($grandTotalVes ?? ($grandTotal * ($dollarRate ?? 1)), 2, ',', '.') }}</div>
            </td>
            <td style="width: 25%; background-color: #f8fafc; border: 1px solid #cbd5e1; border-top: 3px solid #16a34a; border-radius: 6px; padding: 8px 4px; text-align: center; vertical-align: middle;">
                <div style="font-size: 7.5pt; font-weight: bold; color: #475569; text-transform: uppercase;">INGRESOS MEMBRESÍAS</div>
                <div style="font-size: 13pt; font-weight: bold; color: #16a34a; margin-top: 2px;">${{ number_format($membershipTotal, 2) }}</div>
                <div style="font-size: 8pt; font-weight: bold; color: #16a34a; margin-top: 2px;">Bs. {{ number_format($membershipTotalVes ?? ($membershipTotal * ($dollarRate ?? 1)), 2, ',', '.') }}</div>
            </td>
            <td style="width: 25%; background-color: #f8fafc; border: 1px solid #cbd5e1; border-top: 3px solid #0284c7; border-radius: 6px; padding: 8px 4px; text-align: center; vertical-align: middle;">
                <div style="font-size: 7.5pt; font-weight: bold; color: #475569; text-transform: uppercase;">VENTAS TIENDA / POS</div>
                <div style="font-size: 13pt; font-weight: bold; color: #0284c7; margin-top: 2px;">${{ number_format($productSalesTotal, 2) }}</div>
                <div style="font-size: 8pt; font-weight: bold; color: #16a34a; margin-top: 2px;">Bs. {{ number_format($productSalesTotalVes ?? ($productSalesTotal * ($dollarRate ?? 1)), 2, ',', '.') }}</div>
            </td>
            <td style="width: 25%; background-color: #f8fafc; border: 1px solid #cbd5e1; border-top: 3px solid #8b5cf6; border-radius: 6px; padding: 8px 4px; text-align: center; vertical-align: middle;">
                <div style="font-size: 7.5pt; font-weight: bold; color: #475569; text-transform: uppercase;">SOCIOS / ASISTENCIAS</div>
                <div style="font-size: 13pt; font-weight: bold; color: #8b5cf6; margin-top: 2px;">{{ $newMemberships->count() }} / {{ $attendances->count() }}</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 2px;">Operaciones</div>
            </td>
        </tr>
    </table>

    <!-- Breakdown by Payment Methods Table -->
    <div class="section-header">Desglose por Métodos de Pago y Monedas</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Método de Pago</th>
                <th class="text-right">Membresías ($)</th>
                <th class="text-right">Ventas Tienda ($)</th>
                <th class="text-right">Total ($)</th>
                <th class="text-right">Equivalente (Bs.)</th>
                <th class="text-right">Proporción (%)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Efectivo (Cash)</strong></td>
                <td class="text-right">${{ number_format($membershipPayments->where('payment_method', 'cash')->sum('amount'), 2) }}</td>
                <td class="text-right">${{ number_format($productSales->where('payment_method', 'cash')->sum('total_amount'), 2) }}</td>
                <td class="text-right"><strong>${{ number_format($cashTotal, 2) }}</strong></td>
                <td class="text-right" style="color: #15803d; font-weight: bold;">Bs. {{ number_format($cashTotalVes ?? ($cashTotal * ($dollarRate ?? 1)), 2, ',', '.') }}</td>
                <td class="text-right">{{ $grandTotal > 0 ? number_format(($cashTotal / $grandTotal) * 100, 1) : '0.0' }}%</td>
            </tr>
            <tr>
                <td><strong>Tarjeta (Débito / Crédito)</strong></td>
                <td class="text-right">${{ number_format($membershipPayments->where('payment_method', 'card')->sum('amount'), 2) }}</td>
                <td class="text-right">${{ number_format($productSales->where('payment_method', 'card')->sum('total_amount'), 2) }}</td>
                <td class="text-right"><strong>${{ number_format($cardTotal, 2) }}</strong></td>
                <td class="text-right" style="color: #15803d; font-weight: bold;">Bs. {{ number_format($cardTotalVes ?? ($cardTotal * ($dollarRate ?? 1)), 2, ',', '.') }}</td>
                <td class="text-right">{{ $grandTotal > 0 ? number_format(($cardTotal / $grandTotal) * 100, 1) : '0.0' }}%</td>
            </tr>
            <tr>
                <td><strong>Transferencia / Pago Móvil</strong></td>
                <td class="text-right">${{ number_format($membershipPayments->where('payment_method', 'transfer')->sum('amount'), 2) }}</td>
                <td class="text-right">${{ number_format($productSales->where('payment_method', 'transfer')->sum('total_amount'), 2) }}</td>
                <td class="text-right"><strong>${{ number_format($transferTotal, 2) }}</strong></td>
                <td class="text-right" style="color: #15803d; font-weight: bold;">Bs. {{ number_format($transferTotalVes ?? ($transferTotal * ($dollarRate ?? 1)), 2, ',', '.') }}</td>
                <td class="text-right">{{ $grandTotal > 0 ? number_format(($transferTotal / $grandTotal) * 100, 1) : '0.0' }}%</td>
            </tr>
            @if($otherTotal > 0)
            <tr>
                <td><strong>Otros / Mixto</strong></td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right"><strong>${{ number_format($otherTotal, 2) }}</strong></td>
                <td class="text-right" style="color: #15803d; font-weight: bold;">Bs. {{ number_format($otherTotalVes ?? ($otherTotal * ($dollarRate ?? 1)), 2, ',', '.') }}</td>
                <td class="text-right">{{ $grandTotal > 0 ? number_format(($otherTotal / $grandTotal) * 100, 1) : '0.0' }}%</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL GENERAL DE CAJA</td>
                <td class="text-right">${{ number_format($membershipTotal, 2) }}</td>
                <td class="text-right">${{ number_format($productSalesTotal, 2) }}</td>
                <td class="text-right" style="color: #0f172a; font-size: 10pt;">${{ number_format($grandTotal, 2) }}</td>
                <td class="text-right" style="color: #15803d; font-size: 10pt;">Bs. {{ number_format($grandTotalVes ?? ($grandTotal * ($dollarRate ?? 1)), 2, ',', '.') }}</td>
                <td class="text-right">100.0%</td>
            </tr>
        </tbody>
    </table>

    <!-- Table 1: Membership Payments Detail -->
    <div class="section-header">Detalle de Cobros de Membresías ({{ $membershipPayments->count() }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 14%;">Fecha y Hora</th>
                <th style="width: 23%;">Socio</th>
                <th style="width: 18%;">Plan Contratado</th>
                <th style="width: 12%;" class="text-center">Método</th>
                <th style="width: 12%;" class="text-center">Operó</th>
                <th style="width: 16%;" class="text-right">Monto ($ / Bs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($membershipPayments as $idx => $mp)
                @php
                    $user = $mp->membership->user ?? null;
                    $userName = ($user && $user->profile) ? trim($user->profile->first_name . ' ' . $user->profile->last_name) : ($user->name ?? 'Socio ID #' . ($mp->membership->user_id ?? ''));
                    $performer = $mp->receivedBy ? trim(($mp->receivedBy->profile->first_name ?? '') . ' ' . ($mp->receivedBy->profile->last_name ?? '')) : 'Sistema';
                    if (empty($performer)) $performer = $mp->receivedBy->name ?? 'Admin';
                    $methodLabel = match($mp->payment_method) {
                        'cash' => 'Efectivo',
                        'card' => 'Tarjeta',
                        'transfer' => 'Transferencia',
                        default => ucfirst($mp->payment_method ?? 'Otro')
                    };
                    $mpRate = ($mp->exchange_rate && (float)$mp->exchange_rate > 1.0001)
                        ? (float)$mp->exchange_rate
                        : (float)($dollarRate ?? 1);

                    $mpVes = ($mp->amount_ves && (float)$mp->amount_ves > ((float)$mp->amount * 1.0001))
                        ? (float)$mp->amount_ves
                        : ((float)$mp->amount * $mpRate);
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($mp->payment_date)->format('d/m/Y H:i') }}</td>
                    <td>
                        <strong>{{ $userName }}</strong>
                        @if($user && $user->email)
                            <br><span style="font-size: 7.5pt; color: #64748b;">{{ $user->email }}</span>
                        @endif
                    </td>
                    <td>{{ $mp->membership->plan->name ?? 'Plan Membresía' }}</td>
                    <td class="text-center"><strong>{{ $methodLabel }}</strong></td>
                    <td class="text-center">{{ $performer }}</td>
                    <td class="text-right font-bold">
                        ${{ number_format($mp->amount, 2) }}
                        <br><span style="font-size: 7.5pt; color: #15803d; font-weight: normal;">Bs. {{ number_format($mpVes, 2, ',', '.') }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #64748b; font-style: italic; padding: 12px;">
                        No se registraron cobros de membresías en el período seleccionado.
                    </td>
                </tr>
            @endforelse
            @if($membershipPayments->count() > 0)
                <tr class="total-row">
                    <td colspan="6" class="text-right">Subtotal Cobros de Membresías:</td>
                    <td class="text-right">
                        ${{ number_format($membershipTotal, 2) }}
                        <br><span style="font-size: 8pt; color: #15803d;">Bs. {{ number_format($membershipTotalVes ?? ($membershipTotal * ($dollarRate ?? 1)), 2, ',', '.') }}</span>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Table 2: Product Sales (Tienda / POS) Detail -->
    <div class="section-header">Detalle de Ventas en Tienda POS ({{ $productSales->count() }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 7%;">Folio</th>
                <th style="width: 14%;">Fecha y Hora</th>
                <th style="width: 20%;">Cliente</th>
                <th style="width: 25%;">Productos Comprados</th>
                <th style="width: 10%;" class="text-center">Método</th>
                <th style="width: 10%;" class="text-center">Cajero</th>
                <th style="width: 14%;" class="text-right">Total ($ / Bs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productSales as $sale)
                @php
                    $clientName = ($sale->user && $sale->user->profile) ? trim($sale->user->profile->first_name . ' ' . $sale->user->profile->last_name) : ($sale->user->name ?? 'Cliente Mostrador');
                    $sellerName = $sale->soldBy ? trim(($sale->soldBy->profile->first_name ?? '') . ' ' . ($sale->soldBy->profile->last_name ?? '')) : 'Sistema';
                    if (empty($sellerName)) $sellerName = $sale->soldBy->name ?? 'Cajero';

                    $itemsSummary = [];
                    foreach ($sale->items as $it) {
                        $pName = $it->product->name ?? 'Producto';
                        $itemsSummary[] = "{$it->quantity}x {$pName}";
                    }
                    $itemsStr = !empty($itemsSummary) ? implode(', ', $itemsSummary) : 'Venta POS';
                    $methodLabel = match($sale->payment_method) {
                        'cash' => 'Efectivo',
                        'card' => 'Tarjeta',
                        'transfer' => 'Transferencia',
                        default => ucfirst($sale->payment_method ?? 'Otro')
                    };
                    $saleRate = ($sale->exchange_rate && (float)$sale->exchange_rate > 1.0001)
                        ? (float)$sale->exchange_rate
                        : (float)($dollarRate ?? 1);

                    $saleVes = ($sale->total_amount_ves && (float)$sale->total_amount_ves > ((float)$sale->total_amount * 1.0001))
                        ? (float)$sale->total_amount_ves
                        : ((float)$sale->total_amount * $saleRate);
                @endphp
                <tr>
                    <td><strong>#POS-{{ $sale->id }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}</td>
                    <td><strong>{{ $clientName }}</strong></td>
                    <td><span style="font-size: 8pt;">{{ $itemsStr }}</span></td>
                    <td class="text-center"><strong>{{ $methodLabel }}</strong></td>
                    <td class="text-center">{{ $sellerName }}</td>
                    <td class="text-right font-bold">
                        ${{ number_format($sale->total_amount, 2) }}
                        <br><span style="font-size: 7.5pt; color: #15803d; font-weight: normal;">Bs. {{ number_format($saleVes, 2, ',', '.') }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #64748b; font-style: italic; padding: 12px;">
                        No se registraron ventas de productos en tienda durante este período.
                    </td>
                </tr>
            @endforelse
            @if($productSales->count() > 0)
                <tr class="total-row">
                    <td colspan="6" class="text-right">Subtotal Ventas Tienda POS:</td>
                    <td class="text-right">
                        ${{ number_format($productSalesTotal, 2) }}
                        <br><span style="font-size: 8pt; color: #15803d;">Bs. {{ number_format($productSalesTotalVes ?? ($productSalesTotal * ($dollarRate ?? 1)), 2, ',', '.') }}</span>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Notes & Closing Audit Information -->
    @if($isClosed && $closingLog)
        @php
            $logOld = $closingLog->old_data ? (is_array($closingLog->old_data) ? $closingLog->old_data : json_decode($closingLog->old_data, true)) : [];
            $logNew = $closingLog->new_data ? (is_array($closingLog->new_data) ? $closingLog->new_data : json_decode($closingLog->new_data, true)) : [];
            $notes = $logNew['notes'] ?? $logOld['notes'] ?? null;
            $closedBy = $logNew['closed_by'] ?? $closingLog->admin->name ?? 'Administrador';
            $closedAt = $logNew['closed_at'] ?? $closingLog->createdAt;
        @endphp
        <div class="notes-box">
            <strong>REGISTRO OFICIAL DE CIERRE REGISTRADO EN SISTEMA:</strong><br>
            Cierre efectuado por: <strong>{{ $closedBy }}</strong> el <strong>{{ \Carbon\Carbon::parse($closedAt)->format('d/m/Y a las H:i:s') }}</strong>.
            @if(!empty($notes))
                <br><em>Observaciones de cierre:</em> "{{ $notes }}"
            @endif
        </div>
    @endif

    <!-- Official Signatures Block -->
    <table class="signatures-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-line"></div>
                <div class="signature-name">{{ auth()->user()->name ?? 'Administrador / Cajero' }}</div>
                <div class="signature-role">Responsable de Caja / Operador</div>
            </td>
            <td style="width: 10%;"></td>
            <td class="signature-cell">
                <div class="signature-line"></div>
                <div class="signature-name">Revisado y Conforme</div>
                <div class="signature-role">Auditoría / Contabilidad General</div>
            </td>
        </tr>
    </table>

</body>
</html>
