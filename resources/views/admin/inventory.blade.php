@extends('layouts.admin')

@section('title', 'Materials & Inventory - D&G Construction Monitor')
@section('page_title', 'Materials & Inventory')

@section('content')
<div class="page active" id="pg-inventory">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Logistics Control & Stock Levels</div>
            <select class="form-select" id="inventory-location" style="width:auto; max-width:200px;">
                <option value="Overall">Overall Stock</option>
                <option value="Rizal Site">Rizal Site Depot</option>
                <option value="Lipa Hub">Lipa Storage Hub</option>
            </select>
        </div>
        <p class="text-muted p-3">Monitor concrete, rebar reinforcement metrics, lumber volumetric data, and low-stock threshold triggers.</p>
    </div>
</div>
@endsection