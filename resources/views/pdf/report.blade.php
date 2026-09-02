<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $project->title }} — FORGE Intelligence Blueprint</title>
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
            border-bottom: 2px solid #6366f1;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .forge-badge {
            font-size: 10px;
            font-weight: bold;
            color: #6366f1;
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
        .verdict-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #6366f1;
            border-radius: 6px;
            padding: 14px;
            margin-bottom: 15px;
        }
        .verdict-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            background-color: #e0e7ff;
            color: #4338ca;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .verdict-summary {
            font-size: 12px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 6px;
        }
        .verdict-rationale {
            font-size: 11px;
            color: #475569;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 11px;
        }
        th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .competitor-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
        }
        .competitor-name {
            font-weight: bold;
            color: #0f172a;
            font-size: 12px;
        }
        .tag {
            display: inline-block;
            font-size: 9px;
            padding: 2px 5px;
            border-radius: 3px;
            background-color: #f1f5f9;
            color: #475569;
            margin-right: 4px;
        }
        .tag-confidence {
            background-color: #ecfdf5;
            color: #059669;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @if(!$cleanPdf)
        <div class="watermark">
            FORGE FREE TIER &bull; UPGRADE FOR UNWATERMARKED REPORT
        </div>
    @endif

    <div class="header">
        <div class="forge-badge">FORGE &bull; Product & Market Intelligence Blueprint</div>
        <h1>{{ $project->title }}</h1>
        <div class="meta">
            Classification: <strong>{{ $project->classification->label() }}</strong> &bull;
            Status: <strong>{{ ucfirst($project->status->value) }}</strong> &bull;
            Generated: {{ now()->format('M d, Y H:i T') }}
        </div>
    </div>

    <!-- Executive Overview & Problem -->
    <div class="section">
        <div class="section-title">1. Problem Statement & Target Opportunity</div>
        <p>{{ $project->description ?: 'No project description provided.' }}</p>
    </div>

    <!-- Existence & Strategic Verdict -->
    @if($project->discovery)
        <div class="section">
            <div class="section-title">2. Strategic Existence Discovery Verdict</div>
            <div class="verdict-box">
                <span class="verdict-badge">{{ $project->discovery->verdict->label() }}</span>
                <div class="verdict-summary">{{ $project->discovery->summary }}</div>
                @if($project->discovery->rationale)
                    <div class="verdict-rationale">
                        <strong>Strategic Rationale:</strong> {{ $project->discovery->rationale }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Real-World Grounded Evidence -->
    @if($project->evidence && $project->evidence->count() > 0)
        <div class="section">
            <div class="section-title">3. Traceable Evidence & Citations ({{ $project->evidence->count() }} Claims)</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 55%;">Verified Claim / Insight</th>
                        <th style="width: 25%;">Category & Confidence</th>
                        <th style="width: 20%;">Primary Sources</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->evidence as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->claim }}</strong>
                            </td>
                            <td>
                                <span class="tag tag-confidence">{{ strtoupper($item->confidence->value ?? 'VERIFIED') }}</span>
                                <span class="tag">{{ ucfirst($item->category) }}</span>
                            </td>
                            <td>
                                @if($item->sources && $item->sources->count() > 0)
                                    @foreach($item->sources as $src)
                                        <div><a href="{{ $src->url }}" style="color: #6366f1; text-decoration: none;">{{ Str::limit($src->title, 25) }}</a></div>
                                    @endforeach
                                @else
                                    <span style="color: #94a3b8;">Direct Synthesis</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="page-break"></div>

    <!-- Competitor Intelligence Matrix -->
    @if($project->competitors && $project->competitors->count() > 0)
        <div class="section">
            <div class="section-title">4. Competitive Intelligence Matrix</div>
            @foreach($project->competitors as $comp)
                <div class="competitor-card">
                    <div class="competitor-name">{{ $comp->name }} ({{ ucfirst($comp->category) }} Competitor)</div>
                    <div style="font-size: 11px; color: #475569; margin: 3px 0;">{{ $comp->description }}</div>
                    <div style="margin-top: 5px;">
                        <strong>Key Differentiation:</strong> {{ $comp->differentiation ?: 'Standard alternative' }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Opportunities & Recommendations -->
    @if($project->opportunities && $project->opportunities->count() > 0)
        <div class="section">
            <div class="section-title">5. Strategic Opportunities & Execution Guidance</div>
            <table>
                <thead>
                    <tr>
                        <th>Opportunity</th>
                        <th>Impact</th>
                        <th>Difficulty</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->opportunities as $opp)
                        <tr>
                            <td>
                                <strong>{{ $opp->title }}</strong>
                                <div style="color: #64748b; font-size: 10px;">{{ $opp->description }}</div>
                            </td>
                            <td><span class="tag">{{ strtoupper($opp->impact) }}</span></td>
                            <td><span class="tag">{{ strtoupper($opp->difficulty) }}</span></td>
                            <td><span class="tag tag-confidence">{{ strtoupper($opp->status) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        Generated by FORGE &bull; Framework for Opportunity, Research, Growth & Execution &bull; Confidential
    </div>
</body>
</html>
