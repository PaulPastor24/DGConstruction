@extends('layouts.admin')

@section('title', 'Materials & Inventory')
@section('page_title', 'Materials & Inventory')

@push('styles')

<style>
    :root {
        --mi-dark: #10271a;
        --mi-green: #087d3d;
        --mi-green-soft: #e7f6ed;
        --mi-blue: #1d70e8;
        --mi-blue-soft: #eaf3ff;
        --mi-orange: #ff6b25;
        --mi-orange-soft: #fff0e8;
        --mi-muted: #7b8880;
        --mi-border: #e3e9e5;
        --mi-background: #f6f8f6;
        --mi-white: #ffffff;
        --mi-shadow: 0 14px 35px rgba(16, 39, 26, 0.07);
    }

    .content {
        background: var(--mi-background);
    }

    .mi-page {
        width: 100%;
        padding: 4px 0 28px;
    }

    .mi-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 28px;
    }

    .mi-heading-copy {
        min-width: 0;
    }

    .mi-heading h1 {
        margin: 0;
        color: var(--mi-dark);
        font-family: 'Syne', sans-serif;
        font-size: 34px;
        font-weight: 700;
        letter-spacing: -0.8px;
    }

    .mi-heading p {
        margin: 8px 0 0;
        color: var(--mi-muted);
        font-size: 14px;
        line-height: 1.6;
    }

    .mi-heading-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .mi-date {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 172px;
        padding: 13px 16px;
        border: 1px solid var(--mi-border);
        border-radius: 14px;
        background: var(--mi-white);
        box-shadow: 0 8px 24px rgba(16, 39, 26, 0.05);
        color: #38483f;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .mi-date i {
        color: var(--mi-dark);
        font-size: 17px;
    }

    .mi-user {
        display: flex;
        align-items: center;
        gap: 11px;
        padding-left: 16px;
        border-left: 1px solid var(--mi-border);
    }

    .mi-user-avatar {
        display: grid;
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        place-items: center;
        border-radius: 50%;
        background: linear-gradient(145deg, #184e2c, #092d19);
        color: #ffffff;
        font-size: 17px;
    }

    .mi-user-name {
        color: #17271e;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }

    .mi-user-role {
        margin-top: 2px;
        color: #8a968f;
        font-size: 11px;
    }

    .mi-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 26px;
    }

    .mi-summary-card {
        display: flex;
        align-items: center;
        gap: 20px;
        min-height: 132px;
        padding: 24px;
        border: 1px solid var(--mi-border);
        border-radius: 18px;
        background: var(--mi-white);
        box-shadow: var(--mi-shadow);
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .mi-summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 42px rgba(16, 39, 26, 0.1);
    }

    .mi-summary-card.blue {
        border-left: 3px solid var(--mi-blue);
    }

    .mi-summary-card.orange {
        border-left: 3px solid var(--mi-orange);
    }

    .mi-summary-card.green {
        border-left: 3px solid var(--mi-green);
    }

    .mi-summary-icon {
        display: grid;
        width: 64px;
        height: 64px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 50%;
        font-size: 27px;
    }

    .mi-summary-card.blue .mi-summary-icon {
        background: var(--mi-blue-soft);
        color: var(--mi-blue);
    }

    .mi-summary-card.orange .mi-summary-icon {
        background: var(--mi-orange-soft);
        color: var(--mi-orange);
    }

    .mi-summary-card.green .mi-summary-icon {
        background: var(--mi-green-soft);
        color: var(--mi-green);
    }

    .mi-summary-label {
        color: #46554c;
        font-size: 13px;
        font-weight: 600;
    }

    .mi-summary-value {
        margin-top: 2px;
        color: #101b15;
        font-family: 'Syne', sans-serif;
        font-size: 38px;
        font-weight: 700;
        line-height: 1.1;
        letter-spacing: -1px;
    }

    .mi-summary-note {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        color: #748178;
        font-size: 11px;
    }

    .mi-summary-dot {
        width: 8px;
        height: 8px;
        flex: 0 0 auto;
        border-radius: 50%;
    }

    .mi-summary-card.blue .mi-summary-dot {
        background: var(--mi-blue);
    }

    .mi-summary-card.orange .mi-summary-dot {
        background: var(--mi-orange);
    }

    .mi-summary-card.green .mi-summary-dot {
        background: var(--mi-green);
    }

    /*
    |--------------------------------------------------------------------------
    | Main workspace width
    |--------------------------------------------------------------------------
    |
    | Inventory Status is wider.
    | Delivery and Logistics panels are narrower.
    |
    */

    .mi-workspace {
        display: grid;
        grid-template-columns:
            minmax(0, 1.18fr)
            minmax(0, 0.82fr);
        gap: 20px;
        align-items: start;
    }

    .mi-panel {
        min-width: 0;
        overflow: hidden;
        border: 1px solid var(--mi-border);
        border-radius: 18px;
        background: var(--mi-white);
        box-shadow: var(--mi-shadow);
    }

    .mi-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 20px 22px;
        border-bottom: 1px solid var(--mi-border);
    }

    .mi-panel-title-wrap {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
    }

    .mi-panel-title-icon {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 11px;
        background: var(--mi-green-soft);
        color: var(--mi-green);
        font-size: 17px;
    }

    .mi-panel-title {
        margin: 0;
        color: #15261c;
        font-family: 'Syne', sans-serif;
        font-size: 18px;
        font-weight: 700;
    }

    .mi-panel-description {
        margin: 4px 0 0;
        color: #929e96;
        font-size: 11px;
    }

    .mi-project-filter {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .mi-project-filter label {
        margin: 0;
        color: #4c5b52;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .mi-project-filter select {
        min-width: 150px;
        height: 42px;
        padding: 0 38px 0 13px;
        border: 1px solid #dce4de;
        border-radius: 12px;
        outline: none;
        background-color: #ffffff;
        color: #2f3f36;
        font-size: 13px;
    }

    /*
    |--------------------------------------------------------------------------
    | Desktop table wrapper
    |--------------------------------------------------------------------------
    |
    | No horizontal scrollbar at normal desktop size.
    |
    */

    .mi-table-wrapper {
        width: 100%;
        overflow-x: hidden;
    }

    .mi-inventory-table {
        width: 100%;
        min-width: 0;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0;
    }

    .mi-inventory-table th {
        padding: 15px 16px;
        border-bottom: 1px solid var(--mi-border);
        background: #fafbfa;
        color: #657269;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .mi-inventory-table td {
        padding: 18px 16px;
        border-bottom: 1px solid #edf1ee;
        color: #445249;
        font-size: 12px;
        vertical-align: middle;
    }

    .mi-inventory-table th:nth-child(1),
    .mi-inventory-table td:nth-child(1) {
        width: 24%;
    }

    .mi-inventory-table th:nth-child(2),
    .mi-inventory-table td:nth-child(2) {
        width: 30%;
    }

    .mi-inventory-table th:nth-child(3),
    .mi-inventory-table td:nth-child(3) {
        width: 12%;
    }

    .mi-inventory-table th:nth-child(4),
    .mi-inventory-table td:nth-child(4) {
        width: 20%;
    }

    .mi-inventory-table th:nth-child(5),
    .mi-inventory-table td:nth-child(5) {
        width: 14%;
    }

    .mi-inventory-table tbody tr {
        transition: background 0.18s ease;
    }

    .mi-inventory-table tbody tr:hover {
        background: #fafcfb;
    }

    .mi-inventory-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .mi-material-name {
        overflow: hidden;
        color: #17261d;
        font-size: 13px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mi-stock {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .mi-stock-track {
        width: 76px;
        height: 8px;
        flex: 0 1 76px;
        overflow: hidden;
        border-radius: 999px;
        background: #e6ebe8;
    }

    .mi-stock-fill {
        height: 100%;
        border-radius: inherit;
    }

    .mi-stock-number {
        color: #28372f;
        font-weight: 600;
        white-space: nowrap;
    }

    .mi-site {
        display: block;
        overflow: hidden;
        color: #3f4d45;
        font-weight: 500;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mi-stock-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        max-width: 100%;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .mi-stock-status::before {
        width: 7px;
        height: 7px;
        flex: 0 0 auto;
        border-radius: 50%;
        content: '';
    }

    .mi-stock-status.available {
        background: var(--mi-green-soft);
        color: var(--mi-green);
    }

    .mi-stock-status.available::before {
        background: var(--mi-green);
    }

    .mi-stock-status.low {
        background: var(--mi-orange-soft);
        color: var(--mi-orange);
    }

    .mi-stock-status.low::before {
        background: var(--mi-orange);
    }

    .mi-panel-footer {
        padding: 15px 20px;
        border-top: 1px solid var(--mi-border);
        text-align: center;
    }

    .mi-panel-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: var(--mi-green);
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .mi-panel-link:hover {
        color: #056733;
    }

    .mi-right-column {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 20px;
    }

    .mi-form-body {
        padding: 18px 20px;
    }

    /*
    |--------------------------------------------------------------------------
    | Narrower right-side delivery form
    |--------------------------------------------------------------------------
    */

    .mi-form-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 1.05fr)
            minmax(0, 0.7fr)
            minmax(0, 0.75fr)
            minmax(0, 1.2fr);
        gap: 12px;
    }

    .mi-form-group {
        min-width: 0;
    }

    .mi-form-group label {
        display: block;
        margin-bottom: 8px;
        color: #435148;
        font-size: 12px;
        font-weight: 600;
    }

    .mi-form-group input,
    .mi-form-group select {
        width: 100%;
        height: 44px;
        min-width: 0;
        padding: 0 12px;
        border: 1px solid #dce4de;
        border-radius: 12px;
        outline: none;
        background: #ffffff;
        color: #25342c;
        font-size: 12px;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .mi-form-group input:focus,
    .mi-form-group select:focus {
        border-color: #83a18c;
        box-shadow: 0 0 0 4px rgba(8, 125, 61, 0.1);
    }

    .mi-delivery-bottom {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            175px;
        gap: 14px;
        margin-top: 16px;
        align-items: end;
    }

    .mi-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        height: 44px;
        border: 0;
        border-radius: 12px;
        background: linear-gradient(135deg, #0b9149, #08763d);
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .mi-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 22px rgba(8, 125, 61, 0.22);
    }

    /*
    |--------------------------------------------------------------------------
    | Logistics table
    |--------------------------------------------------------------------------
    */

    .mi-logistics-table {
        width: 100%;
        min-width: 0;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0;
    }

    .mi-logistics-table th {
        padding: 14px 14px;
        border-bottom: 1px solid var(--mi-border);
        background: #fafbfa;
        color: #657269;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.7px;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .mi-logistics-table td {
        padding: 15px 14px;
        border-bottom: 1px solid #edf1ee;
        color: #46544b;
        font-size: 11px;
        vertical-align: middle;
    }

    .mi-logistics-table th:nth-child(1),
    .mi-logistics-table td:nth-child(1) {
        width: 15%;
    }

    .mi-logistics-table th:nth-child(2),
    .mi-logistics-table td:nth-child(2) {
        width: 20%;
    }

    .mi-logistics-table th:nth-child(3),
    .mi-logistics-table td:nth-child(3) {
        width: 17%;
    }

    .mi-logistics-table th:nth-child(4),
    .mi-logistics-table td:nth-child(4) {
        width: 26%;
    }

    .mi-logistics-table th:nth-child(5),
    .mi-logistics-table td:nth-child(5) {
        width: 22%;
    }

    .mi-logistics-table tbody tr:hover {
        background: #fafcfb;
    }

    .mi-logistics-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .mi-trip-code {
        color: #22342a;
        font-weight: 700;
        white-space: nowrap;
    }

    .mi-trip-value {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mi-trip-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        max-width: 100%;
        padding: 7px 9px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 700;
        white-space: nowrap;
    }

    .mi-trip-status::before {
        width: 7px;
        height: 7px;
        flex: 0 0 auto;
        border-radius: 50%;
        content: '';
    }

    .mi-trip-status.route {
        background: var(--mi-blue-soft);
        color: var(--mi-blue);
    }

    .mi-trip-status.route::before {
        background: var(--mi-blue);
    }

    .mi-trip-status.delivered {
        background: var(--mi-green-soft);
        color: var(--mi-green);
    }

    .mi-trip-status.delivered::before {
        background: var(--mi-green);
    }

    .mi-trip-status.scheduled {
        background: var(--mi-orange-soft);
        color: var(--mi-orange);
    }

    .mi-trip-status.scheduled::before {
        background: var(--mi-orange);
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive layout
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1150px) {
        .mi-workspace {
            grid-template-columns: 1fr;
        }

        .mi-form-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .mi-table-wrapper {
            overflow-x: auto;
        }

        .mi-inventory-table {
            min-width: 710px;
            table-layout: auto;
        }

        .mi-logistics-table {
            min-width: 650px;
            table-layout: auto;
        }
    }

    @media (max-width: 1000px) {
        .mi-summary-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .mi-heading {
            flex-direction: column;
        }

        .mi-heading-right {
            width: 100%;
            align-items: stretch;
            flex-direction: column;
        }

        .mi-user {
            padding: 0;
            border: 0;
        }

        .mi-date {
            width: 100%;
        }

        .mi-panel-header {
            align-items: stretch;
            flex-direction: column;
        }

        .mi-project-filter {
            align-items: stretch;
            flex-direction: column;
        }

        .mi-project-filter select {
            width: 100%;
        }

        .mi-form-grid,
        .mi-delivery-bottom {
            grid-template-columns: 1fr;
        }
    }
</style>

@endpush

@section('content')

@php
$inventoryRows = [
[
'material' => 'Cement',
'stock' => 420,
'percentage' => 62,
'unit' => 'bags',
'site' => 'Kulas and Rene',
'status' => 'In Stock',
],
[
'material' => 'Steel Rebar 16mm',
'stock' => 180,
'percentage' => 41,
'unit' => 'pcs',
'site' => 'Kulas and Rene',
'status' => 'Low Stock',
],
[
'material' => 'Sand',
'stock' => 35,
'percentage' => 68,
'unit' => 'm³',
'site' => 'Main Warehouse',
'status' => 'In Stock',
],
[
'material' => 'Gravel 3/4',
'stock' => 22,
'percentage' => 44,
'unit' => 'm³',
'site' => 'Main Warehouse',
'status' => 'Low Stock',
],
[
'material' => 'Form Plywood',
'stock' => 95,
'percentage' => 49,
'unit' => 'sheets',
'site' => 'Site B',
'status' => 'In Stock',
],
];

$haulingRows = [
    [
        'trip' => 'TR-104',
        'material' => 'Cement',
        'truck' => 'Truck 02',
        'destination' => 'Kulas and Rene',
        'status' => 'En Route',
    ],
    [
        'trip' => 'TR-105',
        'material' => 'Gravel 3/4',
        'truck' => 'Truck 05',
        'destination' => 'Main Warehouse',
        'status' => 'Delivered',
    ],
    [
        'trip' => 'TR-106',
        'material' => 'Steel Rebar',
        'truck' => 'Truck 03',
        'destination' => 'Site B',
        'status' => 'Scheduled',
    ],
];

@endphp

<div class="mi-page">

<div class="mi-heading">

    <div class="mi-heading-copy">

        <h1>
            Materials & Inventory
        </h1>

        <p>
            Track stock movement, deliveries, inventory value
            and hauling operations across active projects.
        </p>

    </div>

    <div class="mi-heading-right">

        <div class="mi-date">

            <i class="bi bi-calendar3"></i>

            <span>
                June 29, 2026
            </span>

        </div>

        <div class="mi-user">

            <div class="mi-user-avatar">
                <i class="bi bi-person-fill"></i>
            </div>

            <div>

                <div class="mi-user-name">
                    John Dela Cruz
                </div>

                <div class="mi-user-role">
                    Project Manager
                </div>

            </div>

            <i class="bi bi-chevron-down"></i>

        </div>

    </div>

</div>

<div class="mi-summary-grid">

    <article class="mi-summary-card blue">

        <div class="mi-summary-icon">
            <i class="bi bi-truck"></i>
        </div>

        <div>

            <div class="mi-summary-label">
                Active Deliveries
            </div>

            <div class="mi-summary-value">
                18
            </div>

            <div class="mi-summary-note">

                <span class="mi-summary-dot"></span>

                6 incoming this week

            </div>

        </div>

    </article>

    <article class="mi-summary-card orange">

        <div class="mi-summary-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>

        <div>

            <div class="mi-summary-label">
                Low Stock Alerts
            </div>

            <div class="mi-summary-value">
                4
            </div>

            <div class="mi-summary-note">

                <span class="mi-summary-dot"></span>

                Cement, Rebar, Gravel, Form Ply

            </div>

        </div>

    </article>

    <article class="mi-summary-card green">

        <div class="mi-summary-icon">
            ₱
        </div>

        <div>

            <div class="mi-summary-label">
                Total Inventory Value
            </div>

            <div class="mi-summary-value">
                ₱2.48M
            </div>

            <div class="mi-summary-note">

                <span class="mi-summary-dot"></span>

                Across 5 active project sites

            </div>

        </div>

    </article>

</div>

<div class="mi-workspace">

    <section class="mi-panel">

        <div class="mi-panel-header">

            <div>

                <h2 class="mi-panel-title">
                    Inventory Status
                </h2>

                <p class="mi-panel-description">
                    Review available stock levels by material and site.
                </p>

            </div>

            <div class="mi-project-filter">

                <label for="inventoryProject">
                    Project:
                </label>

                <select id="inventoryProject">

                    <option selected>
                        Overall
                    </option>

                    <option>
                        Kulas and Rene
                    </option>

                    <option>
                        Main Warehouse
                    </option>

                    <option>
                        Site B
                    </option>

                </select>

            </div>

        </div>

        <div class="mi-table-wrapper">

            <table class="mi-inventory-table">

                <thead>

                    <tr>
                        <th>Material</th>
                        <th>Stock Level</th>
                        <th>Unit</th>
                        <th>Site</th>
                        <th>Status</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($inventoryRows as $item)

                        @php
                            $isLowStock =
                                $item['status'] === 'Low Stock';

                            $stockClass =
                                $isLowStock
                                    ? 'low'
                                    : 'available';

                            $barColor =
                                $isLowStock
                                    ? '#ff6b25'
                                    : '#087d3d';
                        @endphp

                        <tr>

                            <td>

                                <div class="mi-material-name">
                                    {{ $item['material'] }}
                                </div>

                            </td>

                            <td>

                                <div class="mi-stock">

                                    <div class="mi-stock-track">

                                        <div
                                            class="mi-stock-fill"
                                            style="
                                                width: {{ $item['percentage'] }}%;
                                                background: {{ $barColor }};
                                            "
                                        ></div>

                                    </div>

                                    <span class="mi-stock-number">
                                        {{ $item['stock'] }}
                                    </span>

                                </div>

                            </td>

                            <td>
                                {{ $item['unit'] }}
                            </td>

                            <td>

                                <span
                                    class="mi-site"
                                    title="{{ $item['site'] }}"
                                >
                                    {{ $item['site'] }}
                                </span>

                            </td>

                            <td>

                                <span
                                    class="mi-stock-status {{ $stockClass }}"
                                >
                                    {{ $item['status'] }}
                                </span>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        <div class="mi-panel-footer">

            <a href="#" class="mi-panel-link">

                View full inventory report

                <i class="bi bi-chevron-right"></i>

            </a>

        </div>

    </section>

    <div class="mi-right-column">

        <section class="mi-panel">

            <div class="mi-panel-header">

                <div class="mi-panel-title-wrap">

                    <span class="mi-panel-title-icon">
                        <i class="bi bi-truck"></i>
                    </span>

                    <div>

                        <h2 class="mi-panel-title">
                            Log Material Delivery
                        </h2>

                        <p class="mi-panel-description">
                            Record incoming construction materials.
                        </p>

                    </div>

                </div>

            </div>

            <div class="mi-form-body">

                <form
                    action="{{ route('admin.inventory.store-delivery') }}"
                    method="POST"
                >

                    @csrf

                    <div class="mi-form-grid">

                        <div class="mi-form-group">

                            <label for="material_id">
                                Material
                            </label>

                            <select
                                id="material_id"
                                name="material_id"
                                required
                            >

                                <option value="1" selected>
                                    Cement
                                </option>

                                <option value="2">
                                    Steel Rebar 16mm
                                </option>

                                <option value="3">
                                    Sand
                                </option>

                                <option value="4">
                                    Gravel 3/4
                                </option>

                                <option value="5">
                                    Form Plywood
                                </option>

                            </select>

                        </div>

                        <div class="mi-form-group">

                            <label for="quantity">
                                Quantity
                            </label>

                            <input
                                id="quantity"
                                type="number"
                                name="quantity"
                                value="120"
                                min="1"
                                required
                            >

                        </div>

                        <div class="mi-form-group">

                            <label for="unit">
                                Unit
                            </label>

                            <select
                                id="unit"
                                name="unit"
                                required
                            >

                                <option value="bags" selected>
                                    bags
                                </option>

                                <option value="pcs">
                                    pcs
                                </option>

                                <option value="tons">
                                    tons
                                </option>

                                <option value="m³">
                                    m³
                                </option>

                                <option value="sheets">
                                    sheets
                                </option>

                            </select>

                        </div>

                        <div class="mi-form-group">

                            <label for="supplier_name">
                                Supplier
                            </label>

                            <input
                                id="supplier_name"
                                type="text"
                                name="supplier_name"
                                value="ABC Builders Supply"
                                required
                            >

                        </div>

                    </div>

                    <div class="mi-delivery-bottom">

                        <div class="mi-form-group">

                            <label for="delivery_date">
                                Delivery Date
                            </label>

                            <input
                                id="delivery_date"
                                type="text"
                                value="Jun 29, 2026"
                                readonly
                            >

                        </div>

                        <button
                            type="submit"
                            class="mi-submit"
                        >

                            Log Delivery

                            <i class="bi bi-send"></i>

                        </button>

                    </div>

                </form>

            </div>

        </section>

        <section class="mi-panel">

            <div class="mi-panel-header">

                <div class="mi-panel-title-wrap">

                    <span class="mi-panel-title-icon">
                        <i class="bi bi-truck-front"></i>
                    </span>

                    <div>

                        <h2 class="mi-panel-title">
                            Hauling & Logistics
                        </h2>

                        <p class="mi-panel-description">
                            Monitor delivery trucks and hauling progress.
                        </p>

                    </div>

                </div>

            </div>

            <div class="mi-table-wrapper">

                <table class="mi-logistics-table">

                    <thead>

                        <tr>
                            <th>Trip</th>
                            <th>Material</th>
                            <th>Truck</th>
                            <th>Destination</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach($haulingRows as $trip)

                            @php
                                if ($trip['status'] === 'En Route') {
                                    $statusClass = 'route';
                                } elseif ($trip['status'] === 'Delivered') {
                                    $statusClass = 'delivered';
                                } else {
                                    $statusClass = 'scheduled';
                                }
                            @endphp

                            <tr>

                                <td>

                                    <span class="mi-trip-code">
                                        {{ $trip['trip'] }}
                                    </span>

                                </td>

                                <td>

                                    <span
                                        class="mi-trip-value"
                                        title="{{ $trip['material'] }}"
                                    >
                                        {{ $trip['material'] }}
                                    </span>

                                </td>

                                <td>

                                    <span
                                        class="mi-trip-value"
                                        title="{{ $trip['truck'] }}"
                                    >
                                        {{ $trip['truck'] }}
                                    </span>

                                </td>

                                <td>

                                    <span
                                        class="mi-trip-value"
                                        title="{{ $trip['destination'] }}"
                                    >
                                        {{ $trip['destination'] }}
                                    </span>

                                </td>

                                <td>

                                    <span
                                        class="mi-trip-status {{ $statusClass }}"
                                    >
                                        {{ $trip['status'] }}
                                    </span>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="mi-panel-footer">

                <a href="#" class="mi-panel-link">

                    View all hauling trips

                    <i class="bi bi-chevron-right"></i>

                </a>

            </div>

        </section>

    </div>

</div>

</div>
@endsection
