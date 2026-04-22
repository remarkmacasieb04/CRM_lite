<div style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.6;">
    <h1 style="margin-bottom: 8px;">Your follow-up digest</h1>
    <p style="margin-top: 0;">
        Hello {{ $user->name }}, here is your NextClient follow-up summary for {{ $digestDateLabel }}.
    </p>

    <p>
        Total reminders: <strong>{{ $digest['total'] }}</strong>
    </p>

    @foreach ([
        'Overdue follow-ups' => $digest['overdue'],
        'Due today' => $digest['due_today'],
        'Upcoming this week' => $digest['upcoming'],
    ] as $section => $items)
        @if (count($items) > 0)
            <h2 style="margin-top: 24px; margin-bottom: 8px;">{{ $section }}</h2>
            <ul style="padding-left: 18px;">
                @foreach ($items as $item)
                    <li style="margin-bottom: 10px;">
                        <strong>{{ $item['name'] }}</strong>
                        @if ($item['company'])
                            <span>({{ $item['company'] }})</span>
                        @endif
                        <br>
                        Follow-up: {{ $item['follow_up_at'] ?? 'Not scheduled' }}
                        @if ($item['status_label'])
                            <span> | Status: {{ $item['status_label'] }}</span>
                        @endif
                        <br>
                        <a href="{{ $item['view_url'] }}">Open client record</a>
                    </li>
                @endforeach
            </ul>
        @endif
    @endforeach
</div>
