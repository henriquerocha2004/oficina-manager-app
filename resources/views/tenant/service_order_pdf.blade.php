<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>OS-{{ $serviceOrder->order_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm 14mm 20mm 14mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            color: #1f2937;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        /* ─── HEADER ──────────────────────────────────────────────── */
        .page-header {
            background-color: #f97316;
            color: #ffffff;
            padding: 14px 16px;
            margin-bottom: 16px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo-cell {
            width: 56px;
            vertical-align: middle;
        }

        .header-info-cell {
            vertical-align: middle;
            padding-left: 10px;
        }

        .header-right-cell {
            vertical-align: middle;
            text-align: right;
        }

        .logo-icon {
            width: 44px;
            height: 44px;
        }

        .company-name {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.02em;
        }

        .company-sub {
            font-size: 8pt;
            opacity: 0.85;
            margin-top: 1px;
        }

        .os-number {
            font-size: 20pt;
            font-weight: bold;
            letter-spacing: 0.03em;
        }

        .os-date {
            font-size: 8pt;
            opacity: 0.85;
            margin-top: 2px;
        }

        .status-badge {
            display: inline-block;
            background-color: rgba(255,255,255,0.25);
            color: #ffffff;
            padding: 2px 10px;
            font-size: 8pt;
            font-weight: bold;
            margin-top: 4px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* ─── SECTION TITLE ───────────────────────────────────────── */
        .section-title {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #f97316;
            border-left: 3px solid #f97316;
            padding-left: 7px;
            margin: 14px 0 6px 0;
        }

        .section-title-gray {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
            border-left: 3px solid #d1d5db;
            padding-left: 7px;
            margin: 14px 0 6px 0;
        }

        /* ─── INFO BLOCKS ─────────────────────────────────────────── */
        .info-outer {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .info-card {
            vertical-align: top;
            width: 50%;
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
        }

        .info-card-title {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9ca3af;
            margin-bottom: 5px;
        }

        .info-field-label {
            font-size: 7.5pt;
            color: #9ca3af;
        }

        .info-field-value {
            font-size: 9.5pt;
            color: #111827;
            font-weight: bold;
        }

        .info-field-sub {
            font-size: 8.5pt;
            color: #6b7280;
        }

        .info-row {
            margin-bottom: 4px;
        }

        /* ─── DATES ROW ───────────────────────────────────────────── */
        .dates-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .date-cell {
            width: 25%;
            border: 1px solid #e5e7eb;
            padding: 7px 10px;
            text-align: center;
        }

        .date-cell-label {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9ca3af;
            margin-bottom: 2px;
        }

        .date-cell-value {
            font-size: 9pt;
            font-weight: bold;
            color: #111827;
        }

        /* ─── TECHNICIAN ──────────────────────────────────────────── */
        .technician-bar {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 7px 12px;
            margin-bottom: 4px;
            font-size: 9pt;
        }

        /* ─── TEXT BLOCKS ─────────────────────────────────────────── */
        .text-block {
            border: 1px solid #e5e7eb;
            border-left: 3px solid #f97316;
            padding: 8px 12px;
            font-size: 9.5pt;
            color: #374151;
            background-color: #fffbf7;
            word-wrap: break-word;
            white-space: pre-wrap;
            margin-bottom: 4px;
        }

        .text-block-gray {
            border: 1px solid #e5e7eb;
            border-left: 3px solid #d1d5db;
            padding: 8px 12px;
            font-size: 9.5pt;
            color: #374151;
            background-color: #f9fafb;
            word-wrap: break-word;
            white-space: pre-wrap;
            margin-bottom: 4px;
        }

        /* ─── ITEMS TABLE ─────────────────────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .items-table th {
            background-color: #1f2937;
            color: #f9fafb;
            font-size: 8pt;
            font-weight: bold;
            padding: 6px 8px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            font-size: 9pt;
            padding: 5px 8px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            vertical-align: middle;
        }

        .items-table td.text-right {
            text-align: right;
        }

        .items-table tr.even td {
            background-color: #f9fafb;
        }

        .items-table tfoot td {
            font-weight: bold;
            font-size: 9.5pt;
            color: #111827;
            border-top: 2px solid #d1d5db;
            border-bottom: none;
            padding: 6px 8px;
        }

        .items-table tfoot td.text-right {
            text-align: right;
        }

        .items-section-header td {
            background-color: #f3f4f6;
            color: #6b7280;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 4px 8px;
        }

        .badge {
            display: inline-block;
            padding: 1px 6px;
            font-size: 7.5pt;
            font-weight: bold;
        }

        .badge-service {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .badge-part {
            background-color: #dcfce7;
            color: #15803d;
        }

        /* ─── FINANCIAL SUMMARY ───────────────────────────────────── */
        .financial-wrap {
            text-align: right;
            margin-top: 10px;
            margin-bottom: 4px;
        }

        .financial-table {
            width: 260px;
            margin-left: auto;
            border-collapse: collapse;
        }

        .financial-table td {
            padding: 3px 8px;
            font-size: 9.5pt;
        }

        .financial-table td.label {
            color: #6b7280;
            text-align: left;
        }

        .financial-table td.value {
            text-align: right;
            color: #374151;
        }

        .financial-table tr.divider td {
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
        }

        .financial-table tr.total-row td {
            font-size: 11.5pt;
            font-weight: bold;
            color: #f97316;
            border-top: 2px solid #e5e7eb;
            padding-top: 6px;
        }

        .financial-table tr.paid-row td {
            color: #16a34a;
            font-weight: bold;
        }

        .financial-table tr.balance-row-zero td {
            color: #16a34a;
        }

        .financial-table tr.balance-row-positive td {
            color: #dc2626;
            font-weight: bold;
        }

        /* ─── PAYMENTS TABLE ──────────────────────────────────────── */
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .payments-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 5px 8px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }

        .payments-table th.text-right {
            text-align: right;
        }

        .payments-table td {
            font-size: 9pt;
            padding: 5px 8px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
        }

        .payments-table td.text-right {
            text-align: right;
        }

        .badge-payment {
            background-color: #dcfce7;
            color: #15803d;
        }

        .badge-refund {
            background-color: #fee2e2;
            color: #dc2626;
        }

        /* ─── FOOTER ──────────────────────────────────────────────── */
        .page-footer {
            position: fixed;
            bottom: -14mm;
            left: 0;
            right: 0;
            height: 14mm;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
            font-size: 7.5pt;
            color: #9ca3af;
            text-align: center;
        }

        .footer-inner {
            display: inline-block;
        }

        /* ─── DIVIDER ─────────────────────────────────────────────── */
        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 10px 0;
        }

        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

@php
    $statusLabels = [
        'draft'            => 'Rascunho',
        'waiting_approval' => 'Aguardando Aprovação',
        'approved'         => 'Aprovado',
        'in_progress'      => 'Em Andamento',
        'waiting_payment'  => 'Aguardando Pagamento',
        'completed'        => 'Concluída',
        'cancelled'        => 'Cancelada',
    ];

    $methodLabels = [
        'cash'          => 'Dinheiro',
        'credit_card'   => 'Cartão de Crédito',
        'debit_card'    => 'Cartão de Débito',
        'pix'           => 'PIX',
        'bank_transfer' => 'Transferência Bancária',
        'check'         => 'Cheque',
    ];

    $statusValue   = $serviceOrder->status instanceof \BackedEnum
        ? $serviceOrder->status->value
        : (string) $serviceOrder->status;

    $statusLabel   = $statusLabels[$statusValue] ?? $statusValue;
    $services      = $serviceOrder->items->filter(fn($i) => ($i->type instanceof \BackedEnum ? $i->type->value : (string)$i->type) === 'service');
    $parts         = $serviceOrder->items->filter(fn($i) => ($i->type instanceof \BackedEnum ? $i->type->value : (string)$i->type) === 'part');
    $photoCount    = $serviceOrder->photos->count();
    $balance       = (float) $serviceOrder->outstanding_balance;
@endphp

{{-- FOOTER (must be before page content) --}}
<div class="page-footer">
    <span class="footer-inner">
        Gerado em {{ now()->format('d/m/Y \à\s H:i') }} &nbsp;·&nbsp; OS-{{ $serviceOrder->order_number }}
        @if($photoCount > 0)
            &nbsp;·&nbsp; {{ $photoCount }} foto{{ $photoCount > 1 ? 's' : '' }} anexada{{ $photoCount > 1 ? 's' : '' }}
        @endif
    </span>
</div>

{{-- ──────────── HEADER ──────────────────────────────── --}}
<div class="page-header">
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                {{-- Generic wrench/tool SVG logo --}}
                <svg class="logo-icon" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="22" cy="22" r="22" fill="rgba(255,255,255,0.15)"/>
                    <path d="M28.5 8.5C26.2 8.5 24.2 9.6 23 11.3L25.5 13.8C26 13.3 26.7 13 27.5 13C29.2 13 30.5 14.3 30.5 16C30.5 17.7 29.2 19 27.5 19C26.7 19 26 18.7 25.5 18.2L14.2 29.5C13.7 29 13 28.7 12.5 28.7C10.8 28.7 9.5 30 9.5 31.7C9.5 33.4 10.8 34.7 12.5 34.7C14.2 34.7 15.5 33.4 15.5 31.7C15.5 31.2 15.2 30.5 14.7 30L26 18.7C26.5 19.2 27.2 19.5 28 19.5C30.5 19.5 32.5 17.5 32.5 15C32.5 12.5 31.2 9.8 28.5 8.5Z"
                          fill="rgba(255,255,255,0.9)" />
                    <circle cx="12.5" cy="31.7" r="1.5" fill="rgba(255,255,255,0.9)"/>
                </svg>
            </td>
            <td class="header-info-cell">
                <div class="company-name">AutoOficina</div>
                <div class="company-sub">Ordem de Serviço</div>
            </td>
            <td class="header-right-cell">
                <div class="os-number">OS-{{ $serviceOrder->order_number }}</div>
                <div class="os-date">{{ $serviceOrder->created_at?->format('d/m/Y') }}</div>
                <div><span class="status-badge">{{ $statusLabel }}</span></div>
            </td>
        </tr>
    </table>
</div>

{{-- ──────────── CLIENTE + VEÍCULO ──────────────────────── --}}
<table class="info-outer">
    <tr>
        {{-- Cliente --}}
        <td class="info-card" style="padding-right: 6px; border-right: none;">
            <div class="info-card-title">Cliente</div>
            <div class="info-row">
                <div class="info-field-value">{{ $serviceOrder->client?->name ?? '—' }}</div>
            </div>
            @if($serviceOrder->client?->phone)
            <div class="info-row">
                <span class="info-field-label">Telefone: </span>
                <span class="info-field-sub">{{ $serviceOrder->client->phone }}</span>
            </div>
            @endif
            @if($serviceOrder->client?->email)
            <div class="info-row">
                <span class="info-field-label">E-mail: </span>
                <span class="info-field-sub">{{ $serviceOrder->client->email }}</span>
            </div>
            @endif
        </td>

        {{-- Veículo --}}
        <td class="info-card" style="padding-left: 6px; border-left: none;">
            <div class="info-card-title">Veículo</div>
            <div class="info-row">
                <div class="info-field-value">
                    {{ $serviceOrder->vehicle?->brand }} {{ $serviceOrder->vehicle?->model }}
                    @if($serviceOrder->vehicle?->year) ({{ $serviceOrder->vehicle->year }}) @endif
                </div>
            </div>
            @if($serviceOrder->vehicle?->license_plate)
            <div class="info-row">
                <span class="info-field-label">Placa: </span>
                <span class="info-field-sub">{{ $serviceOrder->vehicle->license_plate }}</span>
                @if($serviceOrder->vehicle?->color)
                    <span class="info-field-label"> &nbsp;·&nbsp; Cor: </span>
                    <span class="info-field-sub">{{ $serviceOrder->vehicle->color }}</span>
                @endif
            </div>
            @endif
            @if($serviceOrder->vehicle?->mileage)
            <div class="info-row">
                <span class="info-field-label">KM: </span>
                <span class="info-field-sub">{{ number_format($serviceOrder->vehicle->mileage, 0, ',', '.') }} km</span>
            </div>
            @endif
        </td>
    </tr>
</table>

{{-- ──────────── DATAS ───────────────────────────────────── --}}
<table class="dates-table">
    <tr>
        <td class="date-cell">
            <div class="date-cell-label">Abertura</div>
            <div class="date-cell-value">{{ $serviceOrder->created_at?->format('d/m/Y') ?? '—' }}</div>
        </td>
        <td class="date-cell">
            <div class="date-cell-label">Aprovação</div>
            <div class="date-cell-value">{{ $serviceOrder->approved_at?->format('d/m/Y') ?? '—' }}</div>
        </td>
        <td class="date-cell">
            <div class="date-cell-label">Início</div>
            <div class="date-cell-value">{{ $serviceOrder->started_at?->format('d/m/Y') ?? '—' }}</div>
        </td>
        <td class="date-cell">
            <div class="date-cell-label">Conclusão</div>
            <div class="date-cell-value">{{ $serviceOrder->completed_at?->format('d/m/Y') ?? '—' }}</div>
        </td>
    </tr>
</table>

{{-- ──────────── TÉCNICO ─────────────────────────────────── --}}
<div class="technician-bar">
    <span class="info-field-label">Técnico Responsável: </span>
    <strong>{{ $serviceOrder->technician?->name ?? 'Não atribuído' }}</strong>
</div>

{{-- ──────────── PROBLEMA RELATADO ──────────────────────── --}}
@if($serviceOrder->reported_problem)
<div class="no-break">
    <div class="section-title">Problema Relatado pelo Cliente</div>
    <div class="text-block">{{ $serviceOrder->reported_problem }}</div>
</div>
@endif

{{-- ──────────── DIAGNÓSTICO TÉCNICO ────────────────────── --}}
@if($serviceOrder->technical_diagnosis)
<div class="no-break">
    <div class="section-title">Diagnóstico Técnico</div>
    <div class="text-block">{{ $serviceOrder->technical_diagnosis }}</div>
</div>
@endif

{{-- ──────────── OBSERVAÇÕES ────────────────────────────── --}}
@if($serviceOrder->observations)
<div class="no-break">
    <div class="section-title-gray">Observações</div>
    <div class="text-block-gray">{{ $serviceOrder->observations }}</div>
</div>
@endif

{{-- ──────────── ITENS ───────────────────────────────────── --}}
<div class="section-title">Itens da Ordem de Serviço</div>

@if($serviceOrder->items->count() > 0)
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 70px;">Tipo</th>
            <th>Descrição</th>
            <th class="text-right" style="width: 40px;">Qtd</th>
            <th class="text-right" style="width: 90px;">Preço Unit.</th>
            <th class="text-right" style="width: 90px;">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @if($services->count() > 0)
        <tr class="items-section-header">
            <td colspan="5">Serviços</td>
        </tr>
        @foreach($services as $index => $item)
        <tr class="{{ $index % 2 === 0 ? '' : 'even' }}">
            <td><span class="badge badge-service">Serviço</span></td>
            <td>{{ $item->description }}</td>
            <td class="text-right">{{ $item->quantity }}</td>
            <td class="text-right">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
            <td class="text-right">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
        </tr>
        @endforeach
        @endif

        @if($parts->count() > 0)
        <tr class="items-section-header">
            <td colspan="5">Peças</td>
        </tr>
        @foreach($parts as $index => $item)
        <tr class="{{ $index % 2 === 0 ? '' : 'even' }}">
            <td><span class="badge badge-part">Peça</span></td>
            <td>{{ $item->description }}</td>
            <td class="text-right">{{ $item->quantity }}</td>
            <td class="text-right">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
            <td class="text-right">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
        </tr>
        @endforeach
        @endif
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-right">Total dos Itens</td>
            <td class="text-right">
                R$ {{ number_format($serviceOrder->total_services + $serviceOrder->total_parts, 2, ',', '.') }}
            </td>
        </tr>
    </tfoot>
</table>
@else
<p style="font-size: 9pt; color: #9ca3af; font-style: italic;">Nenhum item adicionado.</p>
@endif

{{-- ──────────── RESUMO FINANCEIRO ───────────────────────── --}}
<div class="financial-wrap no-break">
    <table class="financial-table">
        <tr>
            <td class="label">Serviços</td>
            <td class="value">R$ {{ number_format($serviceOrder->total_services, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Peças</td>
            <td class="value">R$ {{ number_format($serviceOrder->total_parts, 2, ',', '.') }}</td>
        </tr>
        @if((float)$serviceOrder->discount > 0)
        <tr class="divider">
            <td class="label">Desconto</td>
            <td class="value" style="color: #16a34a;">- R$ {{ number_format($serviceOrder->discount, 2, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td class="label">TOTAL</td>
            <td class="value">R$ {{ number_format($serviceOrder->total, 2, ',', '.') }}</td>
        </tr>
        <tr class="paid-row">
            <td class="label">Pago</td>
            <td class="value">R$ {{ number_format($serviceOrder->paid_amount, 2, ',', '.') }}</td>
        </tr>
        <tr class="{{ $balance > 0 ? 'balance-row-positive' : 'balance-row-zero' }}">
            <td class="label">Saldo Devedor</td>
            <td class="value">R$ {{ number_format($balance, 2, ',', '.') }}</td>
        </tr>
    </table>
</div>

{{-- ──────────── PAGAMENTOS ──────────────────────────────── --}}
@if($serviceOrder->payments->count() > 0)
<div class="no-break">
    <div class="section-title">Pagamentos Registrados</div>
    <table class="payments-table">
        <thead>
            <tr>
                <th style="width: 70px;">Tipo</th>
                <th>Método</th>
                <th class="text-right" style="width: 100px;">Valor</th>
                <th class="text-right" style="width: 70px;">Parcelas</th>
                <th class="text-right" style="width: 90px;">Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($serviceOrder->payments as $payment)
            @php
                $paymentType = $payment->type instanceof \BackedEnum ? $payment->type->value : (string)$payment->type;
                $paymentMethod = $payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : (string)$payment->payment_method;
            @endphp
            <tr>
                <td>
                    <span class="badge {{ $paymentType === 'refund' ? 'badge-refund' : 'badge-payment' }}">
                        {{ $paymentType === 'refund' ? 'Estorno' : 'Pagamento' }}
                    </span>
                </td>
                <td>{{ $methodLabels[$paymentMethod] ?? $paymentMethod }}</td>
                <td class="text-right">R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                <td class="text-right">{{ $payment->installments ?? 1 }}x</td>
                <td class="text-right">{{ $payment->paid_at?->format('d/m/Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

</body>
</html>
