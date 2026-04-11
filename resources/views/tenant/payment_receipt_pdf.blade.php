<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Recibo - OS-{{ $serviceOrder->order_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 16mm 16mm 20mm 16mm;
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
            color: #ffffff;
            padding: 14px 16px;
            margin-bottom: 18px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo-cell {
            width: 52px;
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
            width: 42px;
            height: 42px;
        }

        .doc-title {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .company-sub {
            font-size: 8pt;
            opacity: 0.85;
            margin-top: 2px;
        }

        .os-number {
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: 0.03em;
        }

        .header-date {
            font-size: 8pt;
            opacity: 0.85;
            margin-top: 3px;
        }

        /* ─── SECTION TITLE ───────────────────────────────────────── */
        .section-title {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            border-left: 3px solid #e5e7eb;
            padding-left: 7px;
            margin: 14px 0 6px 0;
        }

        /* ─── OS REFERENCE BAR ────────────────────────────────────── */
        .os-ref-bar {
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            margin-bottom: 14px;
        }

        .os-ref-table {
            width: 100%;
            border-collapse: collapse;
        }

        .os-ref-label {
            font-size: 7pt;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .os-ref-value {
            font-size: 9pt;
            font-weight: bold;
            color: #111827;
        }

        .os-ref-cell {
            vertical-align: top;
            padding: 0 10px 0 0;
        }

        /* ─── CLIENT / VEHICLE CARDS ──────────────────────────────── */
        .info-outer {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .info-card {
            vertical-align: top;
            width: 50%;
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
        }

        .info-card-right {
            border-left: none;
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

        /* ─── PAYMENT HIGHLIGHT ───────────────────────────────────── */
        .payment-highlight {
            text-align: center;
            padding: 24px 16px;
            margin-bottom: 14px;
            border-width: 2px;
            border-style: solid;
        }

        .payment-amount-label {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 6px;
        }

        .payment-amount-value {
            font-size: 28pt;
            font-weight: bold;
            letter-spacing: 0.02em;
        }

        .payment-meta-table {
            width: auto;
            border-collapse: collapse;
            margin: 10px auto 0 auto;
        }

        .payment-meta-cell {
            padding: 0 16px;
            text-align: center;
            vertical-align: top;
        }

        .payment-meta-separator {
            width: 1px;
            background-color: #e5e7eb;
            padding: 0;
        }

        .payment-meta-label {
            font-size: 7pt;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 2px;
        }

        .payment-meta-value {
            font-size: 9.5pt;
            font-weight: bold;
            color: #374151;
        }

        /* ─── SIGNATURE AREA ──────────────────────────────────────── */
        .signature-section {
            margin-top: 32px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-cell {
            width: 47%;
            vertical-align: top;
            text-align: center;
            padding: 0 10px;
        }

        .signature-spacer {
            width: 6%;
        }

        .signature-line {
            border-top: 1px solid #374151;
            margin-bottom: 5px;
            margin-top: 40px;
        }

        .signature-name {
            font-size: 8.5pt;
            font-weight: bold;
            color: #111827;
        }

        .signature-sub {
            font-size: 7.5pt;
            color: #9ca3af;
            margin-top: 2px;
        }

        .signature-sub-input {
            font-size: 7.5pt;
            color: #9ca3af;
            margin-top: 6px;
            text-align: left;
        }

        /* ─── FOOTER ──────────────────────────────────────────────── */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 7pt;
            color: #9ca3af;
            text-align: center;
            padding: 6px 14mm;
            border-top: 1px solid #f3f4f6;
        }

        /* ─── WATERMARK ───────────────────────────────────────────── */
        .refund-watermark {
            font-size: 48pt;
            font-weight: bold;
            color: #fca5a5;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            text-align: center;
            opacity: 0.15;
            position: fixed;
            top: 40%;
            left: 0;
            right: 0;
            transform: rotate(-30deg);
        }
    </style>
</head>
<body>

@php
    $isRefund    = $receiptType === 'refund';
    $accentColor = $isRefund ? '#dc2626' : '#f97316';
    $accentLight = $isRefund ? '#fef2f2' : '#fff7ed';
    $docTitle    = $isRefund ? 'RECIBO DE ESTORNO' : 'RECIBO DE PAGAMENTO';

    $methodLabels = [
        'cash'          => 'Dinheiro',
        'credit_card'   => 'Cartão de Crédito',
        'debit_card'    => 'Cartão de Débito',
        'pix'           => 'PIX',
        'bank_transfer' => 'Transferência Bancária',
        'check'         => 'Cheque',
    ];
    $methodLabel = $methodLabels[$receiptMethod] ?? $receiptMethod;

    $statusLabels = [
        'draft'            => 'Rascunho',
        'waiting_approval' => 'Aguardando Aprovação',
        'approved'         => 'Aprovado',
        'in_progress'      => 'Em Andamento',
        'waiting_payment'  => 'Aguardando Pagamento',
        'completed'        => 'Concluída',
        'cancelled'        => 'Cancelada',
    ];
    $statusValue = $serviceOrder->status instanceof \BackedEnum
        ? $serviceOrder->status->value
        : (string) $serviceOrder->status;
    $statusLabel = $statusLabels[$statusValue] ?? $statusValue;

    $vehicleLabel = implode(' ', array_filter([
        $serviceOrder->vehicle?->brand,
        $serviceOrder->vehicle?->model,
        $serviceOrder->vehicle?->year,
    ]));

    $generatedAt   = now()->format('d/m/Y \à\s H:i');
    $receiptNumber = strtoupper(substr(uniqid(), -8));
@endphp

{{-- Watermark for refund --}}
@if($isRefund)
<div class="refund-watermark">ESTORNO</div>
@endif

{{-- Fixed footer --}}
<div class="page-footer">
    Gerado em {{ $generatedAt }} &nbsp;·&nbsp; OS-{{ $serviceOrder->order_number }} &nbsp;·&nbsp; Recibo #{{ $receiptNumber }}
</div>

{{-- ═══════════════════════════════════════════════════════════
     HEADER
══════════════════════════════════════════════════════════════ --}}
<div class="page-header" style="background-color: {{ $accentColor }}">
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                <svg class="logo-icon" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="44" height="44" rx="10" fill="rgba(255,255,255,0.2)"/>
                    <path d="M30.5 10C27.46 10 25 12.46 25 15.5c0 .55.08 1.09.22 1.6L14.6 27.72A5.48 5.48 0 0 0 13 27.5C9.96 27.5 7.5 29.96 7.5 33S9.96 38.5 13 38.5s5.5-2.46 5.5-5.5c0-.55-.08-1.09-.22-1.6l10.62-10.62c.51.14 1.05.22 1.6.22C33.54 21 36 18.54 36 15.5S33.54 10 30.5 10zm-17.5 26a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z" fill="white"/>
                    <path d="M23 15l6-5 2 2-5 6-3-3z" fill="rgba(255,255,255,0.7)"/>
                </svg>
            </td>
            <td class="header-info-cell">
                <div class="doc-title">{{ $docTitle }}</div>
                <div class="company-sub">Serviços Automotivos</div>
            </td>
            <td class="header-right-cell">
                <div class="os-number">OS-{{ $serviceOrder->order_number }}</div>
                <div class="header-date">{{ $generatedAt }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ═══════════════════════════════════════════════════════════
     REFERENTE À OS
══════════════════════════════════════════════════════════════ --}}
<div class="section-title">Referente à Ordem de Serviço</div>
<div class="os-ref-bar">
    <table class="os-ref-table">
        <tr>
            <td class="os-ref-cell" style="width: 18%">
                <div class="os-ref-label">Número</div>
                <div class="os-ref-value">OS-{{ $serviceOrder->order_number }}</div>
            </td>
            <td class="os-ref-cell" style="width: 22%">
                <div class="os-ref-label">Status</div>
                <div class="os-ref-value">{{ $statusLabel }}</div>
            </td>
            <td class="os-ref-cell" style="width: 40%">
                <div class="os-ref-label">Veículo</div>
                <div class="os-ref-value">{{ $vehicleLabel ?: '—' }}</div>
            </td>
            <td class="os-ref-cell" style="width: 20%; padding-right: 0">
                <div class="os-ref-label">Placa</div>
                <div class="os-ref-value">{{ $serviceOrder->vehicle?->license_plate ?: '—' }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ═══════════════════════════════════════════════════════════
     DADOS DO CLIENTE
══════════════════════════════════════════════════════════════ --}}
<div class="section-title">Dados do Cliente</div>
<table class="info-outer">
    <tr>
        <td class="info-card">
            <div class="info-card-title">Cliente</div>
            <div class="info-row">
                <div class="info-field-value">{{ $serviceOrder->client?->name ?? '—' }}</div>
            </div>
            @if($serviceOrder->client?->document_number)
            <div class="info-row">
                <span class="info-field-label">CPF/CNPJ: </span>
                <span class="info-field-sub">{{ $serviceOrder->client->document_number }}</span>
            </div>
            @endif
        </td>
        <td class="info-card info-card-right">
            <div class="info-card-title">Contato</div>
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
            @if(!$serviceOrder->client?->phone && !$serviceOrder->client?->email)
            <div class="info-field-sub">Não informado</div>
            @endif
        </td>
    </tr>
</table>

{{-- ═══════════════════════════════════════════════════════════
     DETALHES DO PAGAMENTO
══════════════════════════════════════════════════════════════ --}}
<div class="section-title">{{ $isRefund ? 'Detalhes do Estorno' : 'Detalhes do Pagamento' }}</div>
<div class="payment-highlight" style="border-color: {{ $accentColor }}; background-color: {{ $accentLight }}">
    <div class="payment-amount-label" style="color: {{ $accentColor }}">
        {{ $isRefund ? 'Valor Estornado' : 'Valor Pago' }}
    </div>
    <div class="payment-amount-value" style="color: {{ $accentColor }}">
        R$ {{ number_format(floatval($receiptAmount), 2, ',', '.') }}
    </div>

    <table class="payment-meta-table">
        <tr>
            <td class="payment-meta-cell">
                <div class="payment-meta-label">{{ $isRefund ? 'Forma de Devolução' : 'Forma de Pagamento' }}</div>
                <div class="payment-meta-value">{{ $methodLabel }}</div>
            </td>
            <td class="payment-meta-separator">&nbsp;</td>
            <td class="payment-meta-cell">
                <div class="payment-meta-label">Data / Hora</div>
                <div class="payment-meta-value">{{ $generatedAt }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ═══════════════════════════════════════════════════════════
     ÁREA DE ASSINATURA
══════════════════════════════════════════════════════════════ --}}
<div class="signature-section">
    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-line"></div>
                <div class="signature-name">Estabelecimento</div>
                <div class="signature-sub">Assinatura e Carimbo</div>
                <div class="signature-sub-input">Data: ______ / ______ / __________</div>
            </td>
            <td class="signature-spacer"></td>
            <td class="signature-cell">
                <div class="signature-line"></div>
                <div class="signature-name">{{ $serviceOrder->client?->name ?? 'Cliente / Responsável' }}</div>
                <div class="signature-sub">Assinatura</div>
                <div class="signature-sub-input">
                    CPF: _________________________________
                </div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
