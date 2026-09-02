<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $project->title }} — Comprehensive Growth Plan & Strategic Blueprint</title>
    <style>
        @page {
            margin: 35px 40px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.5;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #059669;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .forge-badge {
            font-size: 10px;
            font-weight: bold;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        h1 {
            font-size: 22px;
            margin: 0 0 6px 0;
            color: #0f172a;
        }
        .meta {
            font-size: 11px;
            color: #64748b;
        }
        .watermark {
            position: fixed;
            top: 40%;
            left: 5%;
            width: 90%;
            text-align: center;
            opacity: 0.12;
            font-size: 38px;
            font-weight: 900;
            color: #ef4444;
            transform: rotate(-30deg);
            z-index: -1000;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #059669;
            border-radius: 6px;
            padding: 14px;
            margin-bottom: 20px;
        }
        .card-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .quadrant-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .quadrant-box {
            width: 48%;
            vertical-align: top;
            padding: 10px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .table th {
            background-color: #f1f5f9;
            color: #475569;
            text-align: left;
            padding: 8px 10px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #cbd5e1;
        }
        .table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-indigo { background: #e0e7ff; color: #3730a3; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

@if($watermark)
    <div class="watermark">FORGE FREE TIER — UPGRADE FOR CLEAN EXPORT</div>
@endif

<div class="header">
    <div class="forge-badge">FORGE &bull; Strategic Business Growth Blueprint</div>
    <h1>{{ $project->title }}</h1>
    <div class="meta">
        <strong>Classification:</strong> {{ $project->classification->label() }} &bull;
        <strong>Generated:</strong> {{ now()->format('F j, Y') }} &bull;
        <strong>Workflow Mode:</strong> {{ ucfirst(str_replace('_', ' ', $project->workflow_mode->value)) }}
    </div>
</div>

<!-- Executive Summary & Situation Diagnosis -->
<div class="section">
    <div class="section-title">1. Executive Summary & Objective</div>
    <div class="summary-card">
        <div class="card-title">Objective Diagnosis</div>
        <p style="margin: 0; color: #334155; font-size: 11.5px;">{{ $project->description }}</p>
    </div>
</div>

<!-- Growth Plan Details (from ProductDocument if generated) -->
@php
    $growthDoc = $project->documents()->where('type', 'growth_plan')->first()
        ?? $project->documents()->where('type', 'improvement_plan')->first();
@endphp

@if($growthDoc)
<div class="section">
    <div class="section-title">2. Growth Levers & Strategic Sprints</div>
    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; font-family: monospace; font-size: 10.5px; white-space: pre-wrap; line-height: 1.6;">{{ $growthDoc->content }}</div>
</div>
@endif

<!-- Opportunities Prioritization Matrix -->
@if($project->opportunities->isNotEmpty())
<div class="section">
    <div class="section-title">3. Prioritized Opportunity Matrix</div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 35%;">Opportunity / Growth Lever</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 15%;">Impact</th>
                <th style="width: 15%;">Difficulty</th>
                <th style="width: 20%;">Quadrant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($project->opportunities as $opp)
            <tr>
                <td>
                    <strong>{{ $opp->title }}</strong>
                    <div style="font-size: 10px; color: #64748b; margin-top: 2px;">{{ Str::limit($opp->description, 90) }}</div>
                </td>
                <td><span class="badge badge-gray">{{ $opp->category }}</span></td>
                <td>
                    @if(in_array($opp->impact, ['high', 'critical']))
                        <span class="badge badge-green">{{ $opp->impact }}</span>
                    @else
                        <span class="badge badge-gray">{{ $opp->impact }}</span>
                    @endif
                </td>
                <td>
                    @if(in_array($opp->difficulty, ['low', 'medium']))
                        <span class="badge badge-indigo">{{ $opp->difficulty }}</span>
                    @else
                        <span class="badge badge-amber">{{ $opp->difficulty }}</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $opp->quadrant === 'quick_wins' ? 'badge-green' : ($opp->quadrant === 'major_projects' ? 'badge-indigo' : 'badge-gray') }}">
                        {{ str_replace('_', ' ', $opp->quadrant) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- Verified Research Sources & Citation Appendix -->
@php
    $sources = $project->researchSessions()->with('sources')->get()->pluck('sources')->flatten()->unique('url');
@endphp

@if($sources->isNotEmpty())
<div class="section">
    <div class="section-title">4. Research Citations & Sources Appendix</div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 45%;">Source Title & URL</th>
                <th style="width: 20%;">Domain / Type</th>
                <th style="width: 15%;">Date</th>
                <th style="width: 20%;">Reliability</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sources as $src)
            <tr>
                <td>
                    <strong>{{ $src->title }}</strong>
                    <div style="font-size: 9.5px; color: #2563eb; word-break: break-all; margin-top: 2px;">{{ $src->url }}</div>
                </td>
                <td><span class="badge badge-gray">{{ $src->source_type }}</span></td>
                <td style="color: #64748b;">{{ $src->publication_date ?? 'Recent' }}</td>
                <td>
                    <span class="badge {{ $src->reliability_score >= 0.8 ? 'badge-green' : 'badge-amber' }}">
                        {{ round($src->reliability_score * 100) }}% Score
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="footer">
    Synthesized by FORGE &bull; Grounded Real-World Market Intelligence & Execution Blueprint &bull; Page 1 of 1
</div>

</body>
</html>
